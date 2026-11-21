import assert from "node:assert/strict";
import { mkdtemp, readFile, rm } from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import test from "node:test";
import { loadRuntimeSettings, runtimeSettingsView, saveRuntimeSettings } from "../app/Services/RuntimeSettings.js";

test("render settings save, override .env, validate input and reset", async () => {
  const root = await mkdtemp(path.join(os.tmpdir(), "settings-"));
  const original = { COMFYUI_URL: process.env.COMFYUI_URL, WAN_LOCAL_URL: process.env.WAN_LOCAL_URL };
  process.env.COMFYUI_URL = "http://from-env:8188";
  try {
    await loadRuntimeSettings(root);
    await saveRuntimeSettings({ COMFYUI_URL: "https://render.example.com:8188/", COMFYUI_VIDEO_WORKFLOW: "database/workflows/wan21-local-api.json" }, {}, root);
    assert.equal(process.env.COMFYUI_URL, "https://render.example.com:8188");
    assert.equal(runtimeSettingsView().find((s) => s.name === "COMFYUI_URL").source, "Saved in settings");
    const stored = JSON.parse(await readFile(path.join(root, "storage", "settings", "runtime.json"), "utf8"));
    assert.equal(stored.COMFYUI_VIDEO_WORKFLOW, "database/workflows/wan21-local-api.json");

    await assert.rejects(() => saveRuntimeSettings({ COMFYUI_URL: "file:///etc/passwd" }, {}, root), /http or https/);
    await assert.rejects(() => saveRuntimeSettings({ COMFYUI_VIDEO_WORKFLOW: "database/workflows/../../.env.json" }, {}, root), /workflow/);
    await assert.rejects(() => saveRuntimeSettings({ WAN_LOCAL_URL: "http://example.com:8188" }, {}, root), /this machine/);
    assert.equal(process.env.COMFYUI_URL, "https://render.example.com:8188", "rejected saves change nothing");

    await saveRuntimeSettings({}, { reset: ["COMFYUI_URL"] }, root);
    assert.equal(process.env.COMFYUI_URL, "http://from-env:8188");
  } finally {
    await saveRuntimeSettings({}, { reset: ["COMFYUI_URL", "COMFYUI_VIDEO_WORKFLOW", "WAN_LOCAL_URL", "WAN_LOCAL_WORKFLOW"] }, root);
    for (const [name, value] of Object.entries(original)) value === undefined ? delete process.env[name] : process.env[name] = value;
    await rm(root, { recursive: true, force: true });
  }
});
