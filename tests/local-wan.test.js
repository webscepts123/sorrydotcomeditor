import assert from "node:assert/strict";
import test from "node:test";
import { readFile } from "node:fs/promises";
import { LocalWanService } from "../app/Services/LocalWanService.js";
import { ComfyVideoService } from "../app/Services/ComfyVideoService.js";
import { ProductionController } from "../app/Controllers/ProductionController.js";

test("local Wan reports missing weights before allowing generation", async () => {
  const originalFetch = globalThis.fetch;
  try {
    globalThis.fetch = async () => new Response(JSON.stringify({}));
    const service = new LocalWanService();
    service.status = async () => ({ online: true, gpu_name: "cpu" });
    const status = await service.readiness();
    assert.equal(status.ready, false);
    assert.equal(status.mode, "cpu");
    assert.ok(status.missing.includes("wan2.1_t2v_1.3B_fp16.safetensors"));
    await assert.rejects(() => service.submitScene({}), /Missing:/);
    const nodes = {
      UNETLoader: { input: { required: { unet_name: [["wan2.1_t2v_1.3B_fp16.safetensors"]] } } },
      CLIPLoader: { input: { required: { clip_name: [["umt5_xxl_fp8_e4m3fn_scaled.safetensors"]] } } },
      VAELoader: { input: { required: { vae_name: [["wan_2.1_vae.safetensors"]] } } }
    };
    for (const name of ["KSampler", "CLIPTextEncode", "EmptyHunyuanLatentVideo", "ModelSamplingSD3", "VAEDecode", "SaveWEBM"]) nodes[name] = {};
    globalThis.fetch = async () => new Response(JSON.stringify(nodes));
    assert.equal((await service.readiness()).ready, true);
  } finally { globalThis.fetch = originalFetch; }
});

test("local jobs retain their worker when importing completed video", async () => {
  const scene = { id: 9, project_id: 2, status: "Draft", script_segment: "A quiet station." };
  const writes = [];
  let imported;
  const local = {
    submitScene: async () => "job-123",
    downloadCompletedVideo: async id => { imported = id; return "uploads/scenes/comfy-job-123.webm"; }
  };
  const headers = {};
  const context = {
    request: { headers: { accept: "application/json" } }, params: { id: "9" },
    session: {},
    response: { setHeader: (name, value) => { headers[name] = value; } },
    text: (_body, status) => ({ status }),
    service: name => {
      if (name === "db") return { query: async (sql, values) => {
        if (!sql.startsWith("UPDATE scenes")) return [];
        assert.equal(values[2], scene.generation_job_id);
        const update = { video_path: values[0], status: "Ready", generation_error: null };
        writes.push(update);
        Object.assign(scene, update);
        return { affectedRows: 1 };
      } };
      if (name === "comfyLocal") return () => local;
      throw new Error(`Unexpected service: ${name}`);
    },
    json: (body, status) => ({ body, status }),
    abort: (status, message) => { throw new Error(`${status} ${message}`); }
  };
  const controller = new ProductionController(context);
  controller.findOwned = async () => scene;
  Object.defineProperty(controller, "models", { value: { scenes: { update: async (_id, values) => { writes.push(values); Object.assign(scene, values); } } } });
  const result = await controller.renderLocalScene();
  assert.equal(result.status, 202);
  assert.equal(writes[0].generation_job_id, "local:job-123");
  const conflict = await controller.renderLocalScene();
  assert.equal(conflict.status, 409);
  assert.match(conflict.body.message, /already/);
  context.request.headers.accept = "text/html";
  assert.equal((await controller.renderScene()).status, 303);
  assert.equal(headers.Location, "/scenes/9");
  assert.match(context.session.flash, /already/);
  assert.equal(writes.length, 1);
  context.request.headers.accept = "application/json";
  await controller.syncSceneRender();
  assert.equal(imported, "job-123");
  assert.equal(scene.status, "Ready");
  assert.equal(scene.video_path, "uploads/scenes/comfy-job-123.webm");
  scene.generation_job_id = "kaggle:owner/notebook";
  context.request.headers.accept = "text/html";
  assert.equal((await controller.syncSceneRender()).status, 303);
  assert.equal(headers.Location, "/projects/2/videoeditor?scene_id=9");
  assert.equal(writes.length, 2);
  scene.generation_job_id = "configured-job";
  scene.status = "Rendering";
  context.service = () => { throw new Error("COMFYUI_URL is missing"); };
  assert.equal((await controller.syncSceneRender()).status, 303);
  assert.equal(headers.Location, "/scenes/9");
  assert.match(context.session.flash, /configured and online/);
  assert.equal(scene.status, "Rendering");
  assert.equal(writes.length, 2);
  context.request.headers.accept = "application/json";
  assert.equal((await controller.syncSceneRender()).status, 502);
});

test("ComfyUI image previews are not mistaken for completed video", async () => {
  const originalFetch = globalThis.fetch;
  try {
    globalThis.fetch = async () => new Response(JSON.stringify({ job: { outputs: { "1": { images: [{ filename: "preview.png" }] } }, status: { status_str: "success" } } }));
    assert.equal(await new ComfyVideoService({ baseUrl: "http://localhost:8188" }).downloadCompletedVideo("job"), null);
    globalThis.fetch = async () => new Response(JSON.stringify({ job: { status: { status_str: "error" } } }));
    await assert.rejects(() => new ComfyVideoService({ baseUrl: "http://localhost:8188" }).downloadCompletedVideo("job"), /failed generation/);
  } finally { globalThis.fetch = originalFetch; }
});

test("local workflow uses native nodes and a small test clip", async () => {
  const workflow = JSON.parse(await readFile(new URL("../database/workflows/wan21-local-api.json", import.meta.url)));
  assert.equal(workflow["2"].inputs.device, "cpu");
  assert.equal(workflow["6"].inputs.length, 17);
  assert.equal(workflow["10"].class_type, "SaveWEBM");
  assert.equal(workflow["4"].inputs.text, "{{PROMPT}}");
});
