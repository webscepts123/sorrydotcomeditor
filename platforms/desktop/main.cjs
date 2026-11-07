"use strict";
const { app, BrowserWindow, Menu, net, protocol, screen, session, shell } = require("electron");
const { existsSync, readFileSync, statSync, writeFileSync } = require("node:fs");
const path = require("node:path");
const { pathToFileURL } = require("node:url");

const CONFIG = {
  "appId": "com.sorrydotcom.editor",
  "appName": "Void Shadow Studio",
  "scheme": "voidshadow",
  "window": {
    "width": 1200,
    "height": 800,
    "minWidth": 720,
    "minHeight": 480,
    "resizable": true,
    "rememberBounds": true
  },
  "backgroundColor": "#090B14",
  "singleInstance": true,
  "devTools": false,
  "permissions": [
    "notifications",
    "clipboard-sanitized-write",
    "fullscreen"
  ],
  "liveReloadUrl": "https://studio.sorrydotcom.lk"
};
const ORIGIN = `${CONFIG.scheme}://app`;
const WWW = path.join(__dirname, "www");
const IS_MAC = process.platform === "darwin";

// A standard, secure scheme gives the bundle a real origin, so fetch, storage,
// and the service worker behave as they do on the web.
protocol.registerSchemesAsPrivileged([{
  scheme: CONFIG.scheme,
  privileges: { standard: true, secure: true, supportFetchAPI: true, allowServiceWorkers: true, corsEnabled: true, stream: true }
}]);

if (process.platform === "win32") app.setAppUserModelId(CONFIG.appId);
if (CONFIG.singleInstance && !app.requestSingleInstanceLock()) app.quit();

function bundleFile(requestUrl) {
  const relativePath = decodeURIComponent(new URL(requestUrl).pathname).replace(/^\/+/, "");
  // Files ship inside the app, so a service worker would only serve stale
  // copies after an update. Refusing it makes the registration a no-op.
  if (relativePath === "sw.js") return null;
  const file = path.resolve(WWW, relativePath);
  if (file !== WWW && !file.startsWith(WWW + path.sep)) return null;
  if (relativePath && existsSync(file) && statSync(file).isFile()) return file;
  // Clean routes such as /about are drawn by the router from index.html.
  return path.extname(relativePath) ? null : path.join(WWW, "index.html");
}

function isAppUrl(url) {
  if (url.startsWith(`${ORIGIN}/`)) return true;
  return Boolean(CONFIG.liveReloadUrl) && new URL(url).origin === new URL(CONFIG.liveReloadUrl).origin;
}

function openExternal(url) {
  if (/^(https?|mailto):/i.test(url)) shell.openExternal(url);
}

const stateFile = () => path.join(app.getPath("userData"), "window-state.json");

function savedBounds() {
  if (!CONFIG.window.rememberBounds) return {};
  try {
    const state = JSON.parse(readFileSync(stateFile(), "utf8"));
    // Drop a position that is no longer on any connected display.
    const visible = screen.getAllDisplays().some(({ workArea: area }) => state.x < area.x + area.width
      && state.x + state.width > area.x && state.y < area.y + area.height && state.y + state.height > area.y);
    return visible ? state : { width: state.width, height: state.height, maximized: state.maximized };
  } catch {
    return {};
  }
}

function rememberBounds(window) {
  if (!CONFIG.window.rememberBounds) return;
  try {
    writeFileSync(stateFile(), JSON.stringify({ ...window.getNormalBounds(), maximized: window.isMaximized() }));
  } catch {}
}

function createWindow() {
  const { maximized, ...bounds } = savedBounds();
  const { rememberBounds: _remember, ...size } = CONFIG.window;
  const window = new BrowserWindow({
    ...size,
    ...bounds,
    title: CONFIG.appName,
    backgroundColor: CONFIG.backgroundColor,
    show: false,
    autoHideMenuBar: !IS_MAC,
    icon: path.join(__dirname, "build", "icon.png"),
    webPreferences: {
      preload: path.join(__dirname, "preload.cjs"),
      contextIsolation: true,
      sandbox: true,
      nodeIntegration: false,
      webSecurity: true,
      devTools: CONFIG.devTools ?? !app.isPackaged
    }
  });

  window.once("ready-to-show", () => {
    if (maximized) window.maximize();
    window.show();
  });
  window.on("close", () => rememberBounds(window));
  window.webContents.setWindowOpenHandler(({ url }) => {
    openExternal(url);
    return { action: "deny" };
  });
  window.webContents.on("will-navigate", (event, url) => {
    if (isAppUrl(url)) return;
    event.preventDefault();
    openExternal(url);
  });
  window.loadURL(CONFIG.liveReloadUrl ?? `${ORIGIN}/`);
  return window;
}

function applicationMenu() {
  const devTools = CONFIG.devTools ?? !app.isPackaged;
  return Menu.buildFromTemplate([
    ...(IS_MAC ? [{ role: "appMenu" }] : []),
    { role: "fileMenu" },
    { role: "editMenu" },
    {
      label: "View",
      submenu: [
        { role: "reload" },
        ...(devTools ? [{ role: "toggleDevTools" }] : []),
        { type: "separator" },
        { role: "resetZoom" },
        { role: "zoomIn" },
        { role: "zoomOut" },
        { type: "separator" },
        { role: "togglefullscreen" }
      ]
    },
    { role: "windowMenu" }
  ]);
}

app.on("second-instance", () => {
  const [window] = BrowserWindow.getAllWindows();
  if (!window) return;
  if (window.isMinimized()) window.restore();
  window.focus();
});

app.whenReady().then(() => {
  protocol.handle(CONFIG.scheme, (request) => {
    const file = bundleFile(request.url);
    return file ? net.fetch(pathToFileURL(file).toString()) : new Response("Not found", { status: 404 });
  });
  session.defaultSession.setPermissionRequestHandler((_contents, permission, allow) => {
    allow(CONFIG.permissions.includes(permission));
  });
  Menu.setApplicationMenu(applicationMenu());
  createWindow();
  require("./updater.cjs").startUpdates();
  app.on("activate", () => {
    if (!BrowserWindow.getAllWindows().length) createWindow();
  });
});

app.on("window-all-closed", () => {
  if (!IS_MAC) app.quit();
});
