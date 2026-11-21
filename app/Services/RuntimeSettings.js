import { randomUUID } from "node:crypto";
import { mkdir, readdir, readFile, rename, writeFile } from "node:fs/promises";
import path from "node:path";

// Render settings that can be edited from /settings. Saved values are stored in
// storage/settings/runtime.json (kept across deploys) and override .env. The
// ComfyUI and Local Wan services read process.env on every request, so changes
// take effect without a restart. Settings that can run code (KAGGLE_PYTHON) or
// hold database/API secrets stay in the server .env only.
export const WORKFLOW_DIRECTORIES = ["storage/workflows", "database/workflows"];
const LOOPBACK = ["127.0.0.1", "localhost", "[::1]"];

function httpUrl(value, { loopback = false } = {}) {
  let url;
  try { url = new URL(value); } catch { throw new Error("Enter a full URL such as http://127.0.0.1:8188."); }
  if (!["http:", "https:"].includes(url.protocol) || url.username || url.password) throw new Error("Use an http or https URL without a username or password.");
  if (loopback && !LOOPBACK.includes(url.hostname)) throw new Error("The Local Wan URL must point to this machine (127.0.0.1 or localhost).");
  return url.href.replace(/\/+$/, "");
}

function workflowPath(value) {
  const normalized = value.replaceAll("\\", "/").replace(/^\.\//, "");
  if (!/^[a-zA-Z0-9._\/-]+\.json$/.test(normalized) || normalized.split("/").includes("..")
      || !WORKFLOW_DIRECTORIES.some((directory) => normalized.startsWith(`${directory}/`))) {
    throw new Error(`Choose a workflow .json file inside ${WORKFLOW_DIRECTORIES.join(" or ")}.`);
  }
  return normalized;
}

export const RUNTIME_SETTINGS = [
  { name: "COMFYUI_URL", label: "ComfyUI server URL", group: "comfy", placeholder: "https://your-comfyui-host:8188", help: "Remote ComfyUI used by \"Queue ComfyUI render\". Leave empty to disable.", validate: (value) => httpUrl(value) },
  { name: "COMFYUI_VIDEO_WORKFLOW", label: "ComfyUI workflow", group: "comfy", workflow: true, fallback: "storage/workflows/wan_video_api.json", help: "API-format workflow JSON sent to ComfyUI.", validate: workflowPath },
  { name: "WAN_LOCAL_URL", label: "Local Wan 2.1 URL", group: "local", fallback: "http://127.0.0.1:8188", placeholder: "http://127.0.0.1:8188", help: "ComfyUI running on this machine (npm run wan:start).", validate: (value) => httpUrl(value, { loopback: true }) },
  { name: "WAN_LOCAL_WORKFLOW", label: "Local Wan workflow", group: "local", workflow: true, fallback: "database/workflows/wan21-local-api.json", help: "API-format workflow JSON for the local worker.", validate: workflowPath }
];

const byName = new Map(RUNTIME_SETTINGS.map((setting) => [setting.name, setting]));
const fromEnvFile = {};
let overrides = {};

function storageFile(root = process.cwd()) {
  return path.resolve(root, "storage", "settings", "runtime.json");
}

function apply() {
  for (const { name } of RUNTIME_SETTINGS) {
    if (!(name in fromEnvFile)) fromEnvFile[name] = process.env[name];
    const value = name in overrides ? overrides[name] : fromEnvFile[name];
    if (value === undefined) delete process.env[name];
    else process.env[name] = value;
  }
}

export async function loadRuntimeSettings(root) {
  try {
    const stored = JSON.parse(await readFile(storageFile(root), "utf8"));
    overrides = Object.fromEntries(Object.entries(stored ?? {}).filter(([name, value]) => byName.has(name) && typeof value === "string"));
  } catch (error) {
    if (error.code !== "ENOENT") console.warn("Saved settings could not be read; using .env values.", error.message);
    overrides = {};
  }
  apply();
}

// values: { NAME: string }. An empty value with reset=true falls back to .env;
// an empty value otherwise clears the setting.
export async function saveRuntimeSettings(values, { reset = [] } = {}, root) {
  const next = { ...overrides };
  const errors = [];
  for (const setting of RUNTIME_SETTINGS) {
    if (reset.includes(setting.name)) { delete next[setting.name]; continue; }
    if (!(setting.name in values)) continue;
    const raw = String(values[setting.name] ?? "").trim();
    if (raw.length > 500) { errors.push(`${setting.label}: value is too long.`); continue; }
    if (!raw) { next[setting.name] = ""; continue; }
    try { next[setting.name] = setting.validate(raw); }
    catch (error) { errors.push(`${setting.label}: ${error.message}`); }
  }
  if (errors.length) throw new Error(errors.join(" "));
  const file = storageFile(root);
  await mkdir(path.dirname(file), { recursive: true, mode: 0o700 });
  const temporary = `${file}.${randomUUID()}.tmp`;
  await writeFile(temporary, JSON.stringify(next, null, 2), { mode: 0o600 });
  await rename(temporary, file);
  overrides = next;
  apply();
}

export function runtimeSettingsView() {
  return RUNTIME_SETTINGS.map((setting) => ({
    name: setting.name,
    label: setting.label,
    group: setting.group,
    help: setting.help,
    placeholder: setting.placeholder ?? setting.fallback ?? "",
    workflow: Boolean(setting.workflow),
    value: process.env[setting.name] ?? "",
    source: setting.name in overrides ? "Saved in settings" : fromEnvFile[setting.name] !== undefined ? "From server .env" : setting.fallback ? `Default: ${setting.fallback}` : "Not set",
    overridden: setting.name in overrides
  }));
}

export async function listWorkflows(root = process.cwd()) {
  const files = [];
  for (const directory of WORKFLOW_DIRECTORIES) {
    const entries = await readdir(path.resolve(root, directory)).catch(() => []);
    for (const entry of entries) if (/^[a-zA-Z0-9._-]+\.json$/.test(entry)) files.push(`${directory}/${entry}`);
  }
  return files.sort();
}
