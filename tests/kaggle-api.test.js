import assert from "node:assert/strict";
import test from "node:test";
import { mkdtemp, readFile, writeFile, rm, readdir } from "node:fs/promises";
import { tmpdir } from "node:os";
import path from "node:path";
import { KaggleApiService } from "../app/Services/KaggleApiService.js";
import { ProductionController } from "../app/Controllers/ProductionController.js";
import { renderStudio } from "../app/Views/Studio.js";

test("scene studio loads the Kaggle client and retains its document head", () => {
  const html = renderStudio({ project: { id: 2, title: "Film" }, scenes: [], csrfToken: "csrf" });
  assert.match(html, /src="\/public\/studio\/kaggle.js" defer><\/script>/);
  assert.doesNotMatch(html, /\/public\/studio\/local-wan.js/);
  assert.equal((html.match(/<\/head>/g) || []).length, 1);
});

async function fixture(t, run, database = {}) {
  const root = await mkdtemp(path.join(tmpdir(), "studio-kaggle-api-"));
  t.after(() => rm(root, { recursive: true, force: true }));
  return new KaggleApiService({ root, publicRoot: path.join(root, "public"), appKey: "test-only-private-application-key", database, run });
}

test("Kaggle connection encrypts credentials and isolates accounts", async t => {
  const service = await fixture(t, async () => ({}));
  const secret = "private-test-kaggle-token";
  await service.connect(2, { username: "Filmmaker", secret });
  const stored = await readFile(service.credentialPath(2), "utf8");
  assert.ok(!stored.includes(secret));
  assert.ok(!stored.includes("filmmaker"));
  assert.deepEqual(await service.credentials(2), { username: "filmmaker", kind: "token", secret });
  assert.equal(await service.credentials(3), null);
  assert.deepEqual(await service.connection(2), { connected: true, available: true, username: "filmmaker", message: "Connected as filmmaker" });
  await assert.rejects(() => service.connect(2, { username: "../user", secret }), /username/);
  service.appKey = "a-different-application-key";
  await assert.rejects(() => service.credentials(2), /Reconnect/);
  await service.disconnect(2);
  assert.equal(await service.credentials(2), null);
});

test("Kaggle submission reserves the scene and runs a private notebook without including credentials", async t => {
  const actions = [];
  const database = { query: async (sql, values) => { actions.push({ sql, values }); return { affectedRows: 1 }; } };
  let metadata;
  const service = await fixture(t, async data => {
    if (data.action === "submit") {
      assert.equal(actions.length, 1);
      metadata = JSON.parse(await readFile(path.join(data.directory, "kernel-metadata.json"), "utf8"));
      assert.equal(await readFile(path.join(data.directory, "scene.ipynb"), "utf8"), '{"cells":[]}');
      assert.equal(metadata.is_private, true); assert.equal(metadata.enable_gpu, true); assert.equal(metadata.enable_internet, true);
      assert.ok(!JSON.stringify(metadata).includes(data.credentials.secret));
    }
    return {};
  }, database);
  await service.connect(2, { username: "filmmaker", secret: "private-token" });
  const job = await service.submit(2, { id: 9 }, '{"cells":[]}');
  assert.match(job.job_id, /^kaggle:filmmaker\/void-scene-9-[a-f0-9]{12}$/);
  assert.equal(metadata.id, job.job_id.slice(7));
  assert.deepEqual(await readdir(path.join(service.root, "jobs")), []);
  database.query = async () => ({ affectedRows: 0 });
  await assert.rejects(() => service.submit(2, { id: 9 }, '{}'), /already has/);
  assert.deepEqual(await readdir(path.join(service.root, "jobs")), []);
});

test("Kaggle settings recognize legacy keys and kaggle.json without exposing mismatched credentials", async t => {
  const checked = [];
  const service = await fixture(t, async ({ credentials }) => {
    checked.push(credentials);
    return { connected: true };
  });
  const key = "b".repeat(32);
  await service.connect(2, { username: "Filmmaker", secret: key });
  await service.connect(2, { secret: JSON.stringify({ username: "filmmaker", key }) });
  await assert.rejects(() => service.connect(2, { username: "someone-else", secret: JSON.stringify({ username: "filmmaker", key }) }), /username differs/);
  assert.deepEqual(checked, [
    { username: "filmmaker", secret: key, kind: "legacy" },
    { username: "filmmaker", secret: key, kind: "legacy" }
  ]);
});

test("a saved notebook can replace a stale render on the same scene", async t => {
  let state = "draft";
  const writes = [];
  const database = { query: async (_sql, values) => {
    writes.push(values);
    return { affectedRows: 1 };
  } };
  const service = await fixture(t, async data => {
    if (data.action !== "status") return {};
    assert.equal(data.kernel, "filmmaker/notebook123");
    return { status: state };
  }, database);
  await service.connect(2, { username: "filmmaker", secret: "private-token" });
  const scene = { id: 5, status: "Processing", generation_job_id: "old-render" };
  const url = "https://www.kaggle.com/code/filmmaker/notebook123/edit";
  await assert.rejects(() => service.linkNotebook(2, scene, url), /Save a Kaggle version/);
  await assert.rejects(() => service.linkNotebook(2, scene, url.replace("filmmaker", "different")), /owns this notebook/);
  assert.equal(writes.length, 0);
  state = "queued";
  await service.linkNotebook(2, scene, url);
  assert.deepEqual(writes, [["kaggle:filmmaker/notebook123", 5, "Processing", "old-render", null]]);
});

test("Kaggle completed output attaches only to its matching scene job", async t => {
  const jobId = "kaggle:filmmaker/void-scene-9-abcdef123456";
  const writes = [];
  let currentJob = jobId;
  const database = { transaction: async callback => callback({ execute: async (sql, values) => {
    if (sql.startsWith("SELECT")) return [[{ id: 9, project_id: 2, status: "Rendering", generation_job_id: currentJob }]];
    writes.push({ sql, values }); return [{ affectedRows: 1 }];
  } }) };
  let state = "running";
  const service = await fixture(t, async data => {
    if (data.action === "status") return { status: state };
    if (data.action === "output") {
      const video = path.join(data.directory, "scene-9-1080p.mp4");
      await writeFile(video, Buffer.from("000000186674797069736f6d", "hex"));
      return { video };
    }
    return {};
  }, database);
  await service.connect(2, { username: "filmmaker", secret: "private-token" });
  const scene = { id: 9, generation_job_id: jobId };
  assert.equal((await service.sync(2, scene)).pending, true);
  assert.equal(writes.length, 0);
  state = "complete";
  const result = await service.sync(2, scene);
  assert.equal(result.status, "Ready");
  assert.equal(result.editor_url, "/projects/2/videoeditor?scene_id=9");
  assert.equal((await readFile(path.join(service.publicRoot, result.video_path))).length, 12);
  assert.match(writes[0].sql, /generation_job_id = NULL/);
  currentJob = "different-job";
  await assert.rejects(() => service.sync(2, scene), /running generation job/);
  assert.equal((await readdir(path.join(service.publicRoot, "uploads/scenes"))).length, 1);
  assert.deepEqual(await readdir(path.join(service.root, "outputs")), []);
  await assert.rejects(() => service.sync(3, scene), /Reconnect/);
});

test("Kaggle progress and submission refuse scenes outside the signed-in account", async () => {
  const controller = new ProductionController({ params: { id: "9" }, session: { userId: 2 } });
  controller.findOwned = async () => { throw new Error("404 Scene not found"); };
  await assert.rejects(() => controller.renderKaggleScene(), /404/);
  await assert.rejects(() => controller.kaggleSceneProgress(), /404/);
});

test("recreating a clip uses the saved scene and queues a new seed without clearing the clip", async () => {
  const scene = { id: 5, status: "Ready", prompt: "Police arrive at a house in Colombo.", video_path: "uploads/scenes/old.mp4" };
  const headers = {};
  const session = { userId: 2 };
  const controller = new ProductionController({
    params: { id: "5" }, session, request: { headers: { accept: "text/html" } },
    response: { setHeader: (name, value) => { headers[name] = value; } },
    text: (_body, status) => ({ status }),
    service: name => {
      assert.equal(name, "kaggle");
      return { submit: async (owner, selected, notebook) => {
        assert.equal(owner, 2);
        assert.equal(selected, scene);
        assert.equal(notebook, "saved scene notebook");
      } };
    }
  });
  controller.findOwned = async () => scene;
  controller.kaggleNotebookDocument = async ({ seed }) => {
    assert.ok(Number.isInteger(seed) && seed >= 0 && seed < 2 ** 32);
    return { body: "saved scene notebook" };
  };
  assert.equal((await controller.renderKaggleScene()).status, 303);
  assert.equal(headers.Location, "/scenes/5");
  assert.match(session.flash, /Video recreation queued on Kaggle/);
  assert.equal(scene.video_path, "uploads/scenes/old.mp4");
});
