import assert from "node:assert/strict";
import test from "node:test";
import { mkdtemp, writeFile, readFile, readdir, rm } from "node:fs/promises";
import { tmpdir } from "node:os";
import path from "node:path";
import { importKaggleVideo } from "../app/Services/KaggleVideoImport.js";
import { ProductionController } from "../app/Controllers/ProductionController.js";
import { kaggleControls } from "../app/Views/studio-helpers.js";
import { createNotebookTransfer, readNotebookTransfer } from "../app/Services/KaggleNotebookTransfer.js";

test("private Kaggle import snapshots expire and reject guessed or traversal paths", async t => {
  const root = await mkdtemp(path.join(tmpdir(), "kaggle-transfer-"));
  t.after(() => rm(root, { recursive: true, force: true }));
  const now = 1800000000000;
  const notebook = JSON.stringify({ nbformat: 4, cells: [] });
  const token = await createNotebookTransfer(notebook, root, now);
  assert.equal(await readNotebookTransfer(token, root, now), notebook);
  assert.equal(await readNotebookTransfer(token, root, now + 15 * 60 * 1000), null);
  assert.equal(await readNotebookTransfer("../../.env", root, now), null);
  assert.equal(await readNotebookTransfer(`${now + 1000}-${"0".repeat(64)}`, root, now), null);
  await createNotebookTransfer(notebook, root, now + 15 * 60 * 1000);
  assert.ok(!(await readdir(root)).includes(`${token}.ipynb`));
});

test("scene notebook includes the saved prompt safely and selects 1080p", async () => {
  const prompt = 'Close-up of a dancer. "Golden light"; </textarea><script>alert(1)</script>\nSlow dolly.';
  const headers = {};
  const controller = new ProductionController({
    params: { id: "9" }, response: { setHeader: (key, value) => { headers[key] = value; } },
    service: () => ({ query: async () => [] }), text: body => body,
  });
  controller.findOwned = async table => table === "scenes"
    ? { id: 9, project_id: 2, prompt, script_segment: "Old script" }
    : { id: 2, style_preset: "Film" };
  const notebook = JSON.parse(await controller.kaggleSceneNotebook());
  const source = notebook.cells.filter(cell => cell.cell_type === "code").map(cell => cell.source.join("")).join("\n");
  const encoded = source.match(/bytes.fromhex\("([a-f0-9]+)"\)/)[1];
  const job = JSON.parse(Buffer.from(encoded, "hex").toString("utf8"));
  assert.ok(job.prompt.includes(prompt));
  assert.ok(!job.prompt.includes("Old script"));
  assert.equal(job.preset, "1080p");
  assert.equal(job.scene_id, 9);
  assert.match(source, /PRESET = "1080p"/);
  assert.match(source, /Export did not produce 1920x1080 video/);
  assert.match(headers["Content-Disposition"], /scene-9-1080p.ipynb/);
  assert.equal(headers["Cache-Control"], "private, no-store");
  const html = kaggleControls({ id: 9, prompt }, "csrf-example");
  assert.ok(!html.includes("<script>alert"));
  assert.match(html, /name="_csrf" value="csrf-example"/);
  assert.match(html, /Save video prompt/);
  assert.match(html, /\/scenes\/9\/kaggle-notebook/);
  controller.findOwned = async () => { throw new Error("404 Scene not found"); };
  await assert.rejects(() => controller.kaggleSceneNotebook(), /404/);
});

test("saving a Kaggle prompt preserves the scene script and checks ownership", async () => {
  const writes = [];
  const headers = {};
  const controller = new ProductionController({
    params: { id: "9" }, body: { prompt: "  Slow camera move at sunrise.  " }, session: {},
    service: () => ({ query: async (sql, values) => { writes.push({ sql, values }); return {}; } }),
    response: { setHeader: (key, value) => { headers[key] = value; } }, text: (_body, status) => status,
  });
  controller.findOwned = async () => ({ id: 9, project_id: 2 });
  assert.equal(await controller.saveKagglePrompt(), 303);
  assert.match(writes[0].sql, /UPDATE scenes SET prompt/);
  assert.doesNotMatch(writes[0].sql, /script_segment/);
  assert.ok(writes[0].values.includes("Slow camera move at sunrise."));
  assert.equal(headers.Location, "/projects/2/videoeditor?scene_id=9");
  controller.findOwned = async () => { throw new Error("404 Scene not found"); };
  await assert.rejects(() => controller.saveKagglePrompt(), /404/);
  assert.equal(writes.filter(write => write.sql.startsWith("UPDATE")).length, 1);
});

test("Kaggle scene export uses owned scene data without exposing credentials", async () => {
  const calls = [];
  const headers = {};
  const controller = new ProductionController({
    params: { id: "9" }, response: { setHeader: (key, value) => { headers[key] = value; } },
    service: () => ({ query: async () => [{ name: "Maya", role: "engineer", description: "Thoughtful" }] }),
    text: (body, status, contentType) => ({ body, status, contentType }),
  });
  controller.findOwned = async (table, id) => {
    calls.push([table, id]);
    return table === "scenes" ? { id: 9, project_id: 2, script_segment: "A station at dawn." } : { id: 2, style_preset: "Cinematic noir" };
  };
  const result = await controller.kaggleScene();
  const job = JSON.parse(result.body);
  assert.deepEqual(calls, [["scenes", "9"], ["projects", 2]]);
  assert.equal(job.format, "sorrydotcomeditor.wan-kaggle.v1");
  assert.equal(job.scene_id, 9);
  assert.equal(job.preset, "1080p");
  assert.match(job.prompt, /A station at dawn/);
  assert.match(job.prompt, /Maya/);
  assert.match(headers["Content-Disposition"], /scene-9-wan-job.json/);
  assert.doesNotMatch(result.body, /DB_PASSWORD|APP_KEY|api_key|password/i);
  controller.findOwned = async () => { throw new Error("404 Scene not found"); };
  await assert.rejects(() => controller.kaggleScene(), /404/);
});

test("Kaggle notebook download contains an unexecuted GPU workflow", async () => {
  let filename;
  const controller = new ProductionController({ response: { setHeader: (_name, value) => { filename = value; } }, text: body => body });
  const notebook = JSON.parse(await controller.kaggleNotebook());
  assert.match(filename, /wan21-kaggle.ipynb/);
  assert.equal(notebook.nbformat, 4);
  for (const cell of notebook.cells.filter(cell => cell.cell_type === "code")) {
    assert.equal(cell.execution_count, null);
    assert.deepEqual(cell.outputs, []);
  }
  const code = notebook.cells.flatMap(cell => cell.source).join("\n");
  assert.match(code, /torch.cuda.is_available/);
  assert.match(code, /enable_model_cpu_offload/);
  assert.match(code, /is_bf16_supported/);
  assert.match(code, /export_to_video/);
  assert.doesNotMatch(code, /ngrok|cloudflared|DB_PASSWORD/);
});

async function temporaryFiles(t) {
  const root = await mkdtemp(path.join(tmpdir(), "kaggle-import-"));
  t.after(async () => {
    assert.equal(path.dirname(root), path.resolve(tmpdir()));
    assert.ok(path.basename(root).startsWith("kaggle-import-"));
    await rm(root, { recursive: true, force: true });
  });
  const source = path.join(root, "download.mp4");
  await writeFile(source, Buffer.from("000000186674797069736f6d", "hex"));
  return { root, source, publicRoot: path.join(root, "media") };
}

test("Kaggle MP4 import preserves source, links the scene, and clears previous jobs", async t => {
  const files = await temporaryFiles(t);
  const writes = [];
  const database = { transaction: async callback => callback({ execute: async (sql, values) => {
    if (sql.startsWith("SELECT")) return [[{ id: 9, project_id: 2, status: "Draft" }]];
    writes.push([sql, values]);
    return [{ affectedRows: 1 }];
  } }) };
  const result = await importKaggleVideo(database, { ...files, sceneId: 9 });
  assert.equal(result.editor_url, "/projects/2/videoeditor?scene_id=9");
  assert.deepEqual(await readFile(files.source), await readFile(path.join(files.publicRoot, result.video_path)));
  assert.match(writes[0][0], /generation_job_id = NULL/);
  assert.match(writes[1][0], /completed_clips/);
});

test("Kaggle import removes only its new copy when an active render blocks import", async t => {
  const files = await temporaryFiles(t);
  const database = { transaction: async callback => callback({ execute: async () => [[{ id: 9, project_id: 2, status: "Rendering" }]] }) };
  await assert.rejects(() => importKaggleVideo(database, { ...files, sceneId: 9 }), /running generation job/);
  assert.deepEqual(await readdir(path.join(files.publicRoot, "uploads/scenes")), []);
  assert.equal((await readFile(files.source)).length, 12);
  await writeFile(files.source, "this is not an mp4 file");
  await assert.rejects(() => importKaggleVideo(database, { ...files, sceneId: 9 }), /MP4 file header/);
});
