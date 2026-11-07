import { ai, bearerToken, connect, loadEnvironment, loadPackages, noderyx, registerMobileUi } from "noderyx-framework";
import { fileURLToPath } from "node:url";
import { registerProductionRoutes, requireAuthentication } from "./app/Controllers/ProductionController.js";
import { ComfyVideoService } from "./app/Services/ComfyVideoService.js";
import { LocalWanService } from "./app/Services/LocalWanService.js";

loadEnvironment();
const { default: config } = await import("./noderyx.config.js");
const database = await connect(config.database);

const app = noderyx({
  requireAppKey: true,
  name: config.app.name,
  environment: config.app.environment,
  debug: config.app.debug,
  cache: config.cache,
  views: fileURLToPath(new URL("./resources/views", import.meta.url)),
  public: fileURLToPath(new URL("./public", import.meta.url)),
  security: {
    ...config.security,
    cors: config.security.cors.length
      ? { origins: config.security.cors, credentials: true }
      : null
  }
});

app.provide("ai", ai(config.ai));
app.provide("db", database);
app.provide("comfy", () => new ComfyVideoService());
app.provide("comfyLocal", () => new LocalWanService());

app.use(requireAuthentication);
registerProductionRoutes(app);
app.get("/health", ({ json }) => json({ status: "ok", runtime: "Node.js" }));

// Mobile Studio (/mobile-studio) edits the theme, text, and flags of installed
// apps. Set MOBILE_STUDIO_TOKEN (16+ characters) to allow changes; without it
// the studio is read-only. Swap in your own check, e.g. an admin session.
registerMobileUi(app, config, {
  authorize: process.env.MOBILE_STUDIO_TOKEN ? bearerToken(process.env.MOBILE_STUDIO_TOKEN) : null
});
await loadPackages(app, config.packages, { config });

// Phusion Passenger (cPanel) may hand the application a unix socket path in
// PORT instead of a number, so only treat a numeric value as a TCP port.
const requestedPort = String(process.env.PORT ?? 3000);
const socketPath = /^\d+$/.test(requestedPort) ? null : requestedPort;
const host = process.env.HOST ?? "0.0.0.0";
const port = Number(requestedPort);
let closing = false;

if (socketPath) app.listen(socketPath, () => console.log(`Listening on ${socketPath}`));
else {
  let bindAttempts = 0;
  const server = app.listen(port, host, () => console.log(`Running at http://localhost:${port}`));
  server.on("error", (error) => {
    // A watch restart can fire before the old process frees the socket. Retry
    // the same port instead of moving the app to a different one, which would
    // leave the browser tab you already opened pointing at nothing.
    if (!closing && error.code === "EADDRINUSE" && bindAttempts++ < 20) {
      setTimeout(() => server.listen(port, host), 250).unref();
      return;
    }
    console.error(`Server error: ${error.message}`);
    process.exit(1);
  });
  const stop = () => {
    if (closing) return;
    closing = true;
    server.close(() => process.exit(0));
    // The dev live-reload stream never ends on its own and would block close.
    server.closeAllConnections?.();
    setTimeout(() => process.exit(0), 3000).unref();
  };
  process.on("SIGTERM", stop);
  process.on("SIGINT", stop);
}
