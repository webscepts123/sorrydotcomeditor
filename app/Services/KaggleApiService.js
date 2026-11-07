import { spawn } from "node:child_process";
import { createCipheriv, createDecipheriv, createHash, randomBytes, randomUUID } from "node:crypto";
import { existsSync } from "node:fs";
import { mkdir, readFile, writeFile, unlink, rm, rename } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { importKaggleVideo } from "./KaggleVideoImport.js";

const bridge = fileURLToPath(new URL("../../scripts/kaggle-api.py", import.meta.url));
const validKernel = /^[a-zA-Z0-9_-]+\/void-scene-\d+-[a-f0-9]{12}$/;

export function runKaggle(payload, timeout = 90000) {
  const hosted = "/home/studiosorrydotco/apps/sorrydotcomeditor/shared/kaggle-venv/bin/python";
  const python = process.env.KAGGLE_PYTHON || (existsSync(hosted) ? hosted : process.platform === "win32" ? "python" : "python3");
  return new Promise((resolve, reject) => {
    const child = spawn(python, [bridge], { windowsHide: true, stdio: ["pipe", "pipe", "pipe"], env: { ...process.env, PYTHONUTF8: "1" } });
    let output = "", settled = false;
    const finish = (error, result) => {
      if (settled) return;
      settled = true; clearTimeout(timer);
      error ? reject(error) : resolve(result);
    };
    const timer = setTimeout(() => { child.kill(); finish(new Error("Kaggle request timed out. Check the notebook before retrying.")); }, timeout);
    child.stdout.on("data", chunk => { output += chunk; if (output.length > 65536) { child.kill(); finish(new Error("Invalid Kaggle response.")); } });
    child.stderr.on("data", () => {});
    child.stdin.on("error", () => {});
    child.on("error", () => finish(new Error("The server Kaggle runtime is unavailable.")));
    child.on("close", code => {
      try {
        const result = JSON.parse(output);
        finish(code || result.error ? new Error(result.error || "Kaggle request failed.") : null, result);
      } catch { finish(new Error("The server Kaggle runtime could not start.")); }
    });
    child.stdin.end(JSON.stringify(payload));
  });
}

export class KaggleApiService {
  constructor({ database, root = path.resolve("storage/kaggle-api"), run = runKaggle, appKey = process.env.APP_KEY, publicRoot = path.resolve("public") } = {}) {
    this.database = database; this.root = root; this.run = run; this.appKey = appKey; this.publicRoot = publicRoot; this.busy = new Set();
  }
  credentialPath(userId) {
    if (!Number.isSafeInteger(Number(userId)) || Number(userId) <= 0) throw new Error("Sign in before connecting Kaggle.");
    return path.join(this.root, `user-${Number(userId)}.json`);
  }
  key() {
    if (!this.appKey || this.appKey.length < 16) throw new Error("Configure the server application key before connecting Kaggle.");
    return createHash("sha256").update(this.appKey).digest();
  }
  async credentials(userId) {
    try {
      const stored = JSON.parse(await readFile(this.credentialPath(userId), "utf8"));
      const decipher = createDecipheriv("aes-256-gcm", this.key(), Buffer.from(stored.iv, "hex"));
      decipher.setAuthTag(Buffer.from(stored.tag, "hex"));
      return JSON.parse(Buffer.concat([decipher.update(Buffer.from(stored.data, "hex")), decipher.final()]).toString("utf8"));
    } catch (error) {
      if (error.code === "ENOENT") return null;
      throw new Error("Reconnect Kaggle: the saved connection could not be read.");
    }
  }
  async connect(userId, { username, secret, kind = "token" }) {
    username = String(username || "").trim().toLowerCase(); secret = String(secret || "").trim();
    if (!/^[a-z0-9_-]{3,64}$/.test(username) || !secret || secret.length > 4096 || !["token", "legacy"].includes(kind)) throw new Error("Enter your Kaggle username and API token.");
    const credentials = { username, secret, kind };
    this.key();
    await this.run({ action: "verify", credentials });
    const iv = randomBytes(12), cipher = createCipheriv("aes-256-gcm", this.key(), iv);
    const data = Buffer.concat([cipher.update(JSON.stringify(credentials), "utf8"), cipher.final()]);
    await mkdir(this.root, { recursive: true, mode: 0o700 });
    const destination = this.credentialPath(userId), temporary = `${destination}.${randomUUID()}.tmp`;
    await writeFile(temporary, JSON.stringify({ iv: iv.toString("hex"), tag: cipher.getAuthTag().toString("hex"), data: data.toString("hex") }), { mode: 0o600, flag: "wx" });
    await rename(temporary, destination);
    return { connected: true, username };
  }
  async disconnect(userId) { await unlink(this.credentialPath(userId)).catch(error => { if (error.code !== "ENOENT") throw error; }); }
  async connection(userId) {
    const credentials = await this.credentials(userId);
    try { await this.run({ action: "probe" }); }
    catch (error) { return { connected: false, available: false, message: error.message }; }
    return { connected: Boolean(credentials), available: true, username: credentials?.username, message: credentials ? `Connected as ${credentials.username}` : "Connect your Kaggle account to generate a scene here." };
  }
  async submit(userId, scene, notebook) {
    const credentials = await this.credentials(userId);
    if (!credentials) throw new Error("Connect your Kaggle account first.");
    const slug = `void-scene-${scene.id}-${randomBytes(6).toString("hex")}`;
    const kernel = `${credentials.username}/${slug}`, jobId = `kaggle:${kernel}`;
    const directory = path.join(this.root, "jobs", slug);
    await mkdir(directory, { recursive: true, mode: 0o700 });
    await writeFile(path.join(directory, "scene.ipynb"), notebook, { mode: 0o600 });
    await writeFile(path.join(directory, "kernel-metadata.json"), JSON.stringify({ id: kernel, title: slug.replaceAll("-", " "), code_file: "scene.ipynb", language: "python", kernel_type: "notebook", is_private: true, enable_gpu: true, enable_internet: true, dataset_sources: [], competition_sources: [], kernel_sources: [], model_sources: [] }));
    // Reserve the scene before making the external request; concurrent clicks cannot queue twice.
    const reserved = await this.database.query("UPDATE scenes SET status = 'Rendering', generation_job_id = ?, generation_error = NULL WHERE id = ? AND status NOT IN ('Rendering', 'Processing')", [jobId, scene.id]);
    if (!reserved.affectedRows) { await rm(directory, { recursive: true, force: true }); throw new Error("This scene already has a running generation job."); }
    try {
      await this.run({ action: "submit", credentials, directory });
    } catch (error) {
      if (error.message.startsWith("Kaggle rejected the notebook")) {
        await this.database.query("UPDATE scenes SET status = 'failed', generation_error = ? WHERE id = ? AND generation_job_id = ?", [error.message, scene.id, jobId]);
        throw error;
      }
      // Keep the reference: an interrupted response can still have queued a notebook.
      await this.database.query("UPDATE scenes SET generation_error = ? WHERE id = ? AND generation_job_id = ?", [error.message, scene.id, jobId]);
      throw new Error(`${error.message} Use Check progress before starting another job.`);
    } finally { await rm(directory, { recursive: true, force: true }); }
    return { job_id: jobId, status: "Rendering", notebook_url: `https://www.kaggle.com/code/${kernel}` };
  }
  async sync(userId, scene) {
    const jobId = String(scene.generation_job_id || ""), kernel = jobId.slice(7);
    if (!jobId.startsWith("kaggle:") || !validKernel.test(kernel)) throw new Error("This scene has no Kaggle API job.");
    if (this.busy.has(jobId)) return { pending: true, status: "Downloading" };
    const credentials = await this.credentials(userId);
    if (!credentials || kernel.split("/")[0] !== credentials.username) throw new Error("Reconnect the Kaggle account used to generate this scene.");
    this.busy.add(jobId);
    try {
      const { status } = await this.run({ action: "status", credentials, kernel });
      if (["error", "failed", "cancelled", "canceled", "cancel_acknowledged"].includes(status)) {
        const message = "Kaggle could not finish this scene. Open the notebook for details, then try again.";
        await this.database.query("UPDATE scenes SET status = 'failed', generation_error = ? WHERE id = ? AND generation_job_id = ?", [message, scene.id, jobId]);
        return { status: "failed", message, notebook_url: `https://www.kaggle.com/code/${kernel}` };
      }
      if (status !== "complete" && status !== "completed") return { pending: true, status, notebook_url: `https://www.kaggle.com/code/${kernel}` };
      const directory = path.join(this.root, "outputs", randomUUID());
      await mkdir(directory, { recursive: true, mode: 0o700 });
      try {
        const { video } = await this.run({ action: "output", credentials, kernel, directory }, 10 * 60 * 1000);
        const source = path.resolve(video);
        if (!source.startsWith(path.resolve(directory) + path.sep)) throw new Error("Invalid Kaggle output path.");
        const imported = await importKaggleVideo(this.database, { sceneId: Number(scene.id), source, publicRoot: this.publicRoot, expectedJobId: jobId });
        return { ...imported, status: "Ready" };
      } finally { await rm(directory, { recursive: true, force: true }); }
    } finally { this.busy.delete(jobId); }
  }
  start() {
    let running = false;
    const tick = async () => {
      if (running) return;
      running = true;
      try {
        const scenes = await this.database.query("SELECT s.*, p.user_id FROM scenes s INNER JOIN projects p ON p.id = s.project_id WHERE s.status IN ('Rendering', 'Processing') AND s.generation_job_id LIKE 'kaggle:%' ORDER BY s.updated_at LIMIT 10");
        for (const scene of scenes) await this.sync(scene.user_id, scene).catch(() => {});
      } catch { /* Retry after a temporary database or network outage. */ }
      finally { running = false; }
    };
    const timer = setInterval(tick, 60000); timer.unref();
    tick();
    return () => clearInterval(timer);
  }
}
