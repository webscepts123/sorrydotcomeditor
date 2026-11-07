import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import path from "node:path";
import test from "node:test";
import vm from "node:vm";
import { compileFile, noderyx } from "noderyx-framework";
import { ProductionController, registerProductionRoutes, requireAuthentication } from "../app/Controllers/ProductionController.js";
import { Repository } from "../app/Models/index.js";
import { ComfyVideoService } from "../app/Services/ComfyVideoService.js";
import { down, up } from "../database/migrations/20261107000000_create_production_schema.js";
import { renderProductionEditor } from "../app/Views/ProductionEditor.js";
import { renderStudio } from "../app/Views/Studio.js";

test("original editors handle empty productions and escape cast and prompt text", () => {
  const project = { id: 8, title: "<Unsafe title>", style_preset: "Noir", aspect_ratio: "16:9" };
  const empty = renderProductionEditor({ project, scenes: [], characters: [], csrfToken: "csrf" });
  assert.match(empty, /TIMELINE IS EMPTY/);
  assert.match(empty, /No cast assigned/);
  assert.match(empty, /&lt;Unsafe title&gt;/);
  assert.match(empty, /disabled>BATCH RENDER/);
  const populated = renderProductionEditor({ project, scenes: [{ id: 9, order_index: 1, script_segment: "</textarea><script>unsafe</script>", status: "Draft" }], characters: [{ name: "<Maya>", ai_tag: "@maya" }], csrfToken: "csrf" });
  assert.match(populated, /&lt;Maya&gt;/);
  assert.match(populated, /&lt;\/textarea&gt;&lt;script&gt;/);
  assert.match(populated, /name="return_to_editor" value="1"/);
  assert.doesNotMatch(populated, /@csrf|@method|\{\{|\sonclick=|\sonsubmit=/);
  const studio = renderStudio({ project, scenes: [] });
  assert.match(studio, /Select a clip in the timeline/);
  assert.doesNotMatch(studio, /id="mainPlayer"/);
});

test("production routes include the application workflows", () => {
  const app = noderyx({ requireAppKey: false, security: { csrf: false, session: false, rateLimit: false } });
  registerProductionRoutes(app);
  const paths = new Set(app.router.routes.map((route) => `${route.method} ${route.regex.source}`));
  assert.ok([...paths].some((route) => route.startsWith("GET ") && route.includes("/projects")));
  assert.ok([...paths].some((route) => route.startsWith("POST ") && route.includes("/scenes")));
  assert.ok([...paths].some((route) => route.startsWith("POST ") && route.includes("posters") && route.includes("generate")));
});

test("unauthenticated requests are redirected except public entry points", async () => {
  const responseHeaders = {};
  const context = {
    request: { url: "/projects", method: "GET" },
    session: {},
    response: { setHeader: (name, value) => { responseHeaders[name] = value; } },
    text: (value, status) => ({ value, status })
  };
  const result = await requireAuthentication(context, () => "continued");
  assert.deepEqual(result, { value: "", status: 303 });
  assert.equal(responseHeaders.Location, "/login");
  context.request.url = "/health";
  assert.equal(await requireAuthentication(context, () => "continued"), "continued");
});

test("repository writes only model fields", async () => {
  const calls = [];
  const database = {
    kind: "mysql",
    query: async (sql, values) => {
      calls.push({ sql, values });
      if (sql.startsWith("INSERT")) return { insertId: 7 };
      return [{ id: 7, title: "Short film" }];
    }
  };
  const project = new Repository(database, "projects");
  const created = await project.create({ user_id: 3, title: "Short film", password: "discard me" });
  assert.equal(created.id, 7);
  assert.match(calls[0].sql, /INSERT INTO projects \(user_id, title\)/);
  assert.deepEqual(calls[0].values, [3, "Short film"]);
});

test("Noderframe resource view renders dynamic fields and forms", async () => {
  const view = path.resolve("resources/views/resource.noderframe");
  const html = await compileFile(view, {
    title: "Projects",
    resource: "projects",
    createHref: "/projects/create",
    cancelHref: "/projects",
    formAction: "/projects",
    showForm: true,
    showDelete: false,
    hasRows: false,
    emptyMessage: "",
    message: "",
    navigation: [],
    rows: [],
    fields: [{ name: "title", label: "Title", value: "A film", type: "text", multiline: false }],
    csrfToken: "test-token"
  });
  assert.match(html, /A film/);
  assert.match(html, /name="_csrf" value="test-token"/);
  assert.match(html, /action="\/projects"/);
});

test("production page templates render their page-specific controls", async () => {
  const cases = [
    ["projects.noderframe", { title: "Projects", pageType: "index", createHref: "/projects/create", projects: [], hasRows: false }, /Initialize first project/],
    ["scenes.noderframe", { title: "Scenes", pageType: "index", createHref: "/scenes/create", rows: [], hasRows: false }, /No scenes yet/],
    ["characters.noderframe", { title: "Characters", pageType: "index", createHref: "/characters/create", rows: [], hasRows: false }, /Export character data/],
    ["planning.noderframe", { pageType: "form", projectOptions: [], fields: [], selectedProjectId: "1", logline: "" }, /Generate story with AI/],
    ["posters.noderframe", { projects: [], hasRows: false, createHref: "/projects/create" }, /Movie Poster Studio/],
    ["tools.noderframe", { title: "Script breakdown", pageType: "script", rows: [], hasRows: false, scriptText: "" }, /Parse into scenes/],
    ["settings.noderframe", { fields: [] }, /Provider setup/],
    ["timeline.noderframe", { title: "Timeline", rows: [], hasRows: false }, /Timeline is empty/],
    ["videoeditor.noderframe", { title: "Video editor", rows: [], hasRows: false, selectedVideoUrl: "", selectedSceneTitle: "" }, /CINEMATIC LOOK/]
  ];

  for (const [file, data, expected] of cases) {
    const html = await compileFile(path.resolve("resources/views", file), {
      navigation: [],
      message: "",
      ...data
    });
    assert.match(html, expected, `${file} should render its converted screen`);
  }
});

test("studio sidebar renders original navigation and production tools on every app screen", async () => {
  let navigation;
  const controller = new ProductionController({
    session: { userId: 7 },
    service: () => ({ query: async () => [] }),
    render: (_view, data) => { navigation = data.navigation; }
  });
  await controller.dashboard();
  const pages = ["dashboard", "projects", "scenes", "characters", "planning", "posters", "resource", "settings", "timeline", "tools", "videoeditor"];
  for (const page of pages) {
    const html = await compileFile(path.resolve("resources/views", `${page}.noderframe`), {
      title: "Test", navigation, csrfToken: "test-token"
    });
    assert.match(html, /aria-controls="production-tools"/, page);
    assert.match(html, /aria-expanded="true"/, page);
    assert.match(html, /<div id="production-tools" class="nav-submenu">/, page);
    for (const id of ["projects-menu", "timeline-menu", "planning-menu", "scenes-menu", "characters-menu", "editors-menu", "trailers-menu", "posters-menu", "soundtracks-menu"]) {
      assert.ok(html.includes(`aria-controls="${id}"`), `${page} missing ${id} toggle`);
      assert.ok(html.includes(`id="${id}"`), `${page} missing ${id} submenu`);
    }
    assert.match(html, /<script src="\/public\/navigation\.js" defer="defer"><\/script>/, page);
    assert.match(html, /href="\/timeline"/, page);
    for (const route of ["/scenes/create", "/planning", "/tools/sync-face", "/tools/gen-score", "/tools/script"]) {
      assert.ok(html.includes(`href="${route}"`), `${page} missing ${route}`);
    }
    assert.match(html, /name="_csrf" value="test-token"/, page);
  }
});

test("dashboard renders populated and empty widgets with owner-scoped project and queue queries", async () => {
  for (const populated of [true, false]) {
    const calls = [];
    let data;
    const db = { query: async (sql, values) => {
      calls.push({ sql, values });
      if (sql.includes("AS scene_count")) return populated ? [{ id: 12, title: "My film", style_preset: "Noir", scene_count: 12 }] : [];
      if (sql.includes("AS project_title") && sql.includes("'Rendering'")) return populated ? [{ id: 5, order_index: 2, status: "Rendering", project_title: "My film" }] : [];
      if (sql.includes("AS project_title")) return populated ? [{ id: 6, order_index: 3, status: "Draft", project_title: "My film" }] : [];
      if (sql.startsWith("SELECT id, name, role FROM editors")) return populated ? [{ id: 4, name: "Editor One", role: "Director" }] : [];
      return [{ count: 0 }];
    } };
    const controller = new ProductionController({
      session: { userId: 7 }, service: () => db,
      render: (_view, values) => { data = values; }
    });
    await controller.dashboard();
    const ownerQueries = calls.filter(({ sql }) => sql.includes("p.user_id = ?"));
    assert.equal(ownerQueries.length, 3);
    for (const call of ownerQueries) assert.deepEqual(call.values, [7]);
    const html = await compileFile(path.resolve("resources/views/dashboard.noderframe"), data);
    for (const label of ["ACTIVE PRODUCTION", "AI RENDER QUEUE", "RENDER WORKER RESOURCES", "PRODUCTION TOOLS", "TEAM / CAST"]) assert.ok(html.includes(label));
    assert.match(html, /<script src="\/public\/dashboard\.js" defer="defer"><\/script>/);
    assert.match(html, /id="resource-widget"/);
    if (populated) {
      assert.match(html, /My film/);
      assert.match(html, /180 \/ 150 min/);
      assert.match(html, /100% of target/);
      assert.match(html, /Sequence #2/);
      assert.match(html, /Next: Sequence #3/);
      assert.match(html, /Editor One/);
      assert.match(html, /href="\/projects\/12\/videoeditor"/);
    } else {
      assert.match(html, /No active project/);
      assert.match(html, /No scenes are currently rendering/);
      assert.match(html, /No editors assigned/);
    }
  }
});

test("ComfyUI status reports actual and unavailable metrics", async () => {
  const originalFetch = globalThis.fetch;
  try {
    globalThis.fetch = async (url) => new Response(JSON.stringify(url.endsWith("/queue")
      ? { queue_running: [1], queue_pending: [2, 3] }
      : { system: { ram_total: 4 * 1024 ** 3, ram_free: 1024 ** 3, comfyui_version: "1.0" },
          devices: [{ name: "GPU", vram_total: 2 * 1024 ** 3, vram_free: 1024 ** 3 }] }));
    const service = new ComfyVideoService({ baseUrl: "http://localhost:8188" });
    const status = await service.status();
    assert.equal(status.ram_percent, 75);
    assert.equal(status.vram_used_gb, 1);
    assert.equal(status.running_jobs, 1);
    assert.equal(status.pending_jobs, 2);
    globalThis.fetch = async () => new Response("{}");
    const missing = await service.status();
    assert.equal(missing.ram_percent, null);
    assert.equal(missing.vram_used_gb, null);
    assert.equal(missing.running_jobs, null);
  } finally {
    globalThis.fetch = originalFetch;
  }
});

test("dashboard status endpoint disables caching and reports configuration and worker failures", async () => {
  const previousUrl = process.env.COMFYUI_URL;
  const originalError = console.error;
  const headers = {};
  const context = {
    response: { setHeader: (name, value) => { headers[name] = value; } },
    json: (body, status = 200) => ({ body, status }),
    service: () => () => ({ status: async () => { throw new Error("worker unreachable"); } })
  };
  try {
    delete process.env.COMFYUI_URL;
    const controller = new ProductionController(context);
    const unconfigured = await controller.runPodStatus();
    assert.equal(unconfigured.body.online, false);
    assert.match(unconfigured.body.error, /COMFYUI_URL/);
    assert.equal(headers["Cache-Control"], "no-store");
    process.env.COMFYUI_URL = "http://localhost:8188";
    let logged = false;
    console.error = () => { logged = true; };
    const failed = await controller.runPodStatus();
    assert.equal(failed.status, 503);
    assert.equal(failed.body.online, false);
    assert.equal(logged, true);
  } finally {
    console.error = originalError;
    if (previousUrl === undefined) delete process.env.COMFYUI_URL;
    else process.env.COMFYUI_URL = previousUrl;
  }
});

test("dashboard status polling updates and clears metrics on failure", async () => {
  const elements = Object.fromEntries(["status", "gpu", "version", "vram", "ram", "queue", "updated"].map((name) => [`worker-${name}`, { textContent: "" }]));
  const widget = { dataset: { statusUrl: "/dashboard/runpod-status" } };
  let next;
  let calls = 0;
  const document = { getElementById: (id) => id === "resource-widget" ? widget : elements[id] };
  const script = await readFile(path.resolve("public/dashboard.js"), "utf8");
  vm.runInNewContext(script, {
    document, console, Date,
    fetch: async () => {
      calls += 1;
      if (calls === 1) return { ok: true, json: async () => ({
        online: true, gpu_name: "Test GPU", ram_used_gb: 1, ram_total_gb: 2, ram_percent: 50,
        running_jobs: 0, pending_jobs: 3
      }) };
      return { ok: false, json: async () => ({ online: false, error: "Worker offline" }) };
    },
    setTimeout: (callback, delay) => { assert.equal(delay, 5000); next = callback; }
  });
  await new Promise(setImmediate);
  assert.equal(elements["worker-status"].textContent, "Worker online");
  assert.equal(elements["worker-ram"].textContent, "System RAM: 1 / 2 GB (50%)");
  assert.equal(elements["worker-vram"].textContent, "GPU VRAM: unavailable");
  assert.equal(elements["worker-queue"].textContent, "ComfyUI queue: 0 running, 3 queued");
  await next();
  assert.equal(elements["worker-status"].textContent, "Worker unavailable");
  assert.equal(elements["worker-updated"].textContent, "Worker offline");
  assert.equal(elements["worker-gpu"].textContent, "GPU: unavailable");
});

test("timeline navigation selects the latest project belonging to the current user", async () => {
  let queryValues;
  const headers = {};
  const context = {
    session: { userId: 7 },
    service: () => ({ query: async (_sql, values) => { queryValues = values; return [{ id: 12 }]; } }),
    response: { setHeader: (name, value) => { headers[name] = value; } },
    text: (_value, status) => status
  };
  assert.equal(await new ProductionController(context).recentTimeline(), 303);
  assert.deepEqual(queryValues, [7]);
  assert.equal(headers.Location, "/projects/12/timeline");
  context.service = () => ({ query: async () => [] });
  assert.equal(await new ProductionController(context).recentTimeline(), 303);
  assert.equal(headers.Location, "/projects");
});

test("video editor navigation selects the latest owned project or the project list", async () => {
  let queryValues;
  const headers = {};
  const context = {
    session: { userId: 7 },
    service: () => ({ query: async (_sql, values) => { queryValues = values; return [{ id: 12 }]; } }),
    response: { setHeader: (name, value) => { headers[name] = value; } },
    text: (_value, status) => status
  };
  assert.equal(await new ProductionController(context).recentVideoEditor(), 303);
  assert.deepEqual(queryValues, [7]);
  assert.equal(headers.Location, "/projects/12/videoeditor");
  context.service = () => ({ query: async () => [] });
  assert.equal(await new ProductionController(context).recentVideoEditor(), 303);
  assert.equal(headers.Location, "/projects");
});

test("original studio renders selected owned media and supports original scene links", async () => {
  let rendered;
  const scenes = [
    { id: 4, order_index: 1, status: "Draft", script_segment: '<script>alert("unsafe")</script>' },
    { id: 5, order_index: 2, status: "Ready", video_path: "uploads/scenes/clip.mp4" }
  ];
  const context = {
    session: { userId: 7 }, params: { id: "12" }, query: {}, csrfToken: "test-csrf",
    response: { baseHeaders: { "content-security-policy": "script-src 'self'; style-src 'self'; form-action 'self'" } },
    service: () => ({ query: async (_sql, values) => {
      assert.deepEqual(values, [12]);
      return scenes;
    } }),
    text: (html, status, type) => {
      assert.equal(status, 200);
      assert.equal(type, "text/html; charset=utf-8");
      rendered = html;
    },
    abort: (status, message) => { throw new Error(`${status} ${message}`); }
  };
  const controller = new ProductionController(context);
  controller.findOwned = async (resource, id) => {
    assert.equal(resource, "projects");
    assert.equal(id, "12");
    return { id: 12, title: "Test production" };
  };
  await controller.projectVideoEditor();
  assert.match(rendered, /id="mainPlayer" src="\/public\/uploads\/scenes\/clip.mp4"/);
  assert.match(rendered, /name="_csrf" value="test-csrf"/);
  assert.match(rendered, /action="\/scenes\/5"/);
  assert.match(rendered, /action="\/scenes\/5\/render"/);
  assert.match(rendered, /action="\/projects\/12\/render-batch"/);
  for (const workspace of ["edit", "color", "fairlight", "deliver"]) {
    assert.ok(rendered.includes(`id="workspace-${workspace}"`));
  }
  assert.doesNotMatch(rendered, /<script>alert/);
  assert.match(rendered, /&lt;script&gt;alert/);
  assert.doesNotMatch(rendered, /\sonclick=|\soninput=|@csrf|@if\(|\{\{/);
  assert.match(context.response.baseHeaders["content-security-policy"], /script-src 'self';/);
  context.query.active_scene = "4";
  await controller.projectVideoEditor();
  assert.match(rendered, /MEDIA OFFLINE/);
  assert.match(rendered, /action="\/scenes\/4"/);
  context.query.scene_id = "99";
  await assert.rejects(() => controller.projectVideoEditor(), /404 Scene not found/);
});

test("video editor creates a playable video and operates preview controls", async () => {
  const handlers = {};
  const style = { setProperty(name, value) { this[name] = value; } };
  const monitor = { dataset: { videoUrl: "/public/uploads/scenes/clip.mp4" }, style };
  const mount = { replaceChildren(child) { this.child = child; } };
  const message = { textContent: "" };
  const video = {
    style: {}, paused: true, currentTime: 0, duration: 10,
    addEventListener(name, callback) { handlers[name] = callback; },
    async play() { this.paused = false; },
    pause() { this.paused = true; }
  };
  const play = { dataset: { editorAction: "play" }, addEventListener(_name, callback) { this.click = callback; } };
  const forward = { dataset: { editorAction: "forward" }, addEventListener(_name, callback) { this.click = callback; } };
  const scale = { dataset: { editorControl: "scale" }, value: "150", defaultValue: "100", type: "range", addEventListener(_name, callback) { this.input = callback; } };
  const speed = { addEventListener(_name, callback) { this.change = callback; } };
  const volume = { addEventListener(_name, callback) { this.input = callback; } };
  const mute = { dataset: { editorAction: "mute" }, setAttribute(name, value) { this[name] = value; }, addEventListener(_name, callback) { this.click = callback; } };
  const workspaceButtons = ["edit", "color", "fairlight", "deliver"].map((name) => ({
    dataset: { workspaceButton: name },
    setAttribute(key, value) { this[key] = value; },
    addEventListener(_name, callback) { this.click = callback; }
  }));
  const workspacePanels = workspaceButtons.map((button) => ({ dataset: { workspacePanel: button.dataset.workspaceButton }, hidden: true }));
  const document = {
    getElementById: (id) => ({ "editor-preview": monitor, "video-mount": mount, "editor-message": message, "playback-speed": speed, "master-volume": volume })[id],
    createElement: (tag) => { assert.equal(tag, "video"); return video; },
    querySelector: (selector) => selector === '[data-editor-control="scale"]' ? scale : null,
    querySelectorAll: (selector) => ({
      "[data-editor-control]": [scale], "[data-editor-action]": [play, forward, mute],
      "[data-workspace-button]": workspaceButtons, "[data-workspace-panel]": workspacePanels
    })[selector] ?? []
  };
  vm.runInNewContext(await readFile(path.resolve("public/video-editor.js"), "utf8"), { document, console });
  assert.equal(mount.child, video);
  assert.equal(video.src, "/public/uploads/scenes/clip.mp4");
  assert.match(video.style.transform, /scale\(1\.5\)/);
  await play.click();
  assert.equal(video.paused, false);
  await forward.click();
  assert.equal(video.currentTime, 1 / 30);
  speed.change({ target: { value: "1.5" } });
  assert.equal(video.playbackRate, 1.5);
  volume.input({ target: { value: "35" } });
  assert.equal(video.volume, 0.35);
  await mute.click();
  assert.equal(video.muted, true);
  assert.equal(mute["aria-pressed"], "true");
  workspaceButtons[2].click();
  assert.equal(workspacePanels[2].hidden, false);
  assert.equal(workspacePanels[1].hidden, true);
  assert.equal(workspaceButtons[2]["aria-pressed"], "true");
  handlers.error();
  assert.match(message.textContent, /could not be loaded/);
});

test("sidebar submenus toggle independently and retain the production tools menu", async () => {
  const menus = ["projects-menu", "scenes-menu", "production-tools"].map((id) => {
    const menu = {
      id,
      hidden: false,
      closest: () => ({ querySelectorAll: () => [] })
    };
    const button = {
      attributes: { "aria-controls": id, "aria-expanded": "true" },
      getAttribute(name) { return this.attributes[name]; },
      setAttribute(name, value) { this.attributes[name] = value; },
      addEventListener(_type, callback) { this.click = callback; }
    };
    return { menu, button };
  });
  const document = {
    querySelectorAll(selector) {
      if (selector === ".nav-toggle") return menus.map(({ button }) => button);
      return [];
    },
    getElementById(id) { return menus.find(({ menu }) => menu.id === id)?.menu ?? null; }
  };
  const script = await readFile(path.resolve("public/navigation.js"), "utf8");
  vm.runInNewContext(script, { document, window: { location: { pathname: "/dashboard", origin: "http://localhost" } }, URL });
  assert.equal(menus[0].menu.hidden, true);
  assert.equal(menus[2].menu.hidden, false);
  menus[0].button.click();
  assert.equal(menus[0].menu.hidden, false);
  assert.equal(menus[0].button.getAttribute("aria-expanded"), "true");
  assert.equal(menus[1].menu.hidden, true);
  menus[0].button.click();
  assert.equal(menus[0].menu.hidden, true);
  assert.equal(menus[0].button.getAttribute("aria-expanded"), "false");
});

test("schema migration preserves existing tables and refuses destructive rollback", async () => {
  const statements = [];
  const database = {
    kind: "mysql",
    query: async (sql) => {
      statements.push(sql);
      return sql.startsWith("SHOW COLUMNS") ? [] : [];
    }
  };
  await up(database);
  assert.ok(statements.some((sql) => sql.includes("CREATE TABLE IF NOT EXISTS projects")));
  assert.ok(statements.some((sql) => sql.startsWith("ALTER TABLE")));
  assert.ok(statements.every((sql) => !sql.startsWith("DROP TABLE")));
  await assert.rejects(() => down(database), /cannot be rolled back/);
});
