import { Controller } from "noderyx-framework";
import { randomUUID } from "node:crypto";
import { mkdir, readFile, statfs, writeFile } from "node:fs/promises";
import path from "node:path";
import { repositories } from "../Models/index.js";
import { renderStudio } from "../Views/Studio.js";
import { renderProductionEditor } from "../Views/ProductionEditor.js";
import { mediaUrl } from "../Views/studio-helpers.js";
import { createNotebookTransfer, readNotebookTransfer } from "../Services/KaggleNotebookTransfer.js";

const resourceDefinitions = {
  projects: {
    table: "projects",
    label: "Projects",
    singular: "Project",
    fields: [
      { name: "title", label: "Title", required: true },
      { name: "description", label: "Production logline", multiline: true },
      { name: "aspect_ratio", label: "Aspect ratio" },
      { name: "resolution", label: "Resolution" },
      { name: "style_preset", label: "Style preset" },
      { name: "poster_image", label: "Poster image path" },
      { name: "teaser_video", label: "Teaser video path" },
      { name: "final_movie_path", label: "Final movie path" },
      { name: "status", label: "Status", type: "select", options: ["draft", "generating", "stitching", "completed", "failed"] }
    ]
  },
  scenes: {
    table: "scenes",
    label: "Scenes",
    singular: "Scene",
    fields: [
      { name: "project_id", label: "Project ID", required: true },
      { name: "order_index", label: "Sequence number", type: "number", required: true },
      { name: "script_segment", label: "Scene script", multiline: true },
      { name: "characters", label: "Character IDs (comma-separated)" },
      { name: "video_path", label: "Video path" },
      { name: "status", label: "Status", type: "select", options: ["Draft", "Ready", "Rendering", "Processing", "failed"] }
    ]
  },
  characters: {
    table: "characters",
    label: "Characters",
    singular: "Character",
    fields: [
      { name: "project_id", label: "Project ID" },
      { name: "name", label: "Name", required: true },
      { name: "ai_tag", label: "AI tag" },
      { name: "role", label: "Role" },
      { name: "description", label: "Description", multiline: true },
      { name: "personality", label: "Personality", multiline: true },
      { name: "dialogue_style", label: "Dialogue style", multiline: true },
      { name: "prompt", label: "Image prompt", multiline: true },
      { name: "video_prompt", label: "Video prompt", multiline: true },
      { name: "image_path", label: "Image path" }
    ]
  },
  editors: {
    table: "editors",
    label: "Editors",
    singular: "Editor",
    fields: [
      { name: "name", label: "Name", required: true },
      { name: "email", label: "Email", type: "email" },
      { name: "role", label: "Role" },
      { name: "avatar_path", label: "Avatar path" }
    ]
  },
  soundtracks: {
    table: "soundtracks",
    label: "Soundtracks",
    singular: "Soundtrack",
    fields: [
      { name: "project_id", label: "Project ID" },
      { name: "title", label: "Title", required: true },
      { name: "composer", label: "Composer" },
      { name: "type", label: "Type" },
      { name: "notes", label: "Notes", multiline: true },
      { name: "file_path", label: "Audio path" }
    ]
  },
  trailers: {
    table: "trailers",
    label: "Trailers",
    singular: "Trailer",
    fields: [
      { name: "project_id", label: "Project ID" },
      { name: "title", label: "Title", required: true },
      { name: "description", label: "Description", multiline: true },
      { name: "duration", label: "Duration" },
      { name: "video_path", label: "Video path" },
      { name: "status", label: "Status" }
    ]
  },
  movies: {
    table: "movies",
    label: "Movies",
    singular: "Movie",
    fields: [
      { name: "title", label: "Title", required: true },
      { name: "synopsis", label: "Synopsis", multiline: true },
      { name: "global_style", label: "Global style" },
      { name: "poster_path", label: "Poster path" }
    ]
  },
  production_plans: {
    table: "production_plans",
    label: "Production plans",
    singular: "Production plan",
    fields: [
      { name: "project_id", label: "Project ID", required: true },
      { name: "logline", label: "Logline", multiline: true },
      { name: "full_story", label: "Full story", multiline: true },
      { name: "script_outline", label: "Script outline", multiline: true },
      { name: "scene_breakdown", label: "Scene breakdown", multiline: true },
      { name: "production_notes", label: "Production notes", multiline: true }
    ]
  }
};

function navigationGroup(id, label, href, links) {
  return { id, label, href, submenu: true, links: links.map(([linkLabel, linkHref]) => ({ label: linkLabel, href: linkHref })) };
}

const navigation = [
  { label: "Dashboard", href: "/dashboard" },
  navigationGroup("projects-menu", "Projects", "/projects", [["All projects", "/projects"], ["New project", "/projects/create"]]),
  navigationGroup("timeline-menu", "Timeline", "/timeline", [["Open timeline", "/timeline"], ["New scene", "/scenes/create"]]),
  { label: "Video editor", href: "/videoeditor" },
  navigationGroup("planning-menu", "Story planner", "/planning", [["Story and schedule", "/planning"], ["Projects", "/projects"]]),
  navigationGroup("scenes-menu", "Scenes", "/scenes", [["All scenes", "/scenes"], ["New scene", "/scenes/create"]]),
  navigationGroup("characters-menu", "Characters", "/characters", [["All characters", "/characters"], ["New character", "/characters/create"], ["Export characters", "/characters/export"]]),
  navigationGroup("editors-menu", "Editors", "/editors", [["All editors", "/editors"], ["New editor", "/editors/create"]]),
  navigationGroup("trailers-menu", "Trailers", "/trailers", [["All trailers", "/trailers"], ["New trailer", "/trailers/create"]]),
  navigationGroup("posters-menu", "Movie posters", "/posters", [["Poster studio", "/posters"], ["Projects", "/projects"]]),
  navigationGroup("soundtracks-menu", "Soundtracks", "/soundtracks", [["All soundtracks", "/soundtracks"], ["New soundtrack", "/soundtracks/create"]]),
  navigationGroup("production-tools", "Production tools", null, [
    ["New shot", "/scenes/create"],
    ["Story planner", "/planning"],
    ["Sync face", "/tools/sync-face"],
    ["Gen score", "/tools/gen-score"],
    ["Script", "/tools/script"]
  ]),
  { label: "Other settings", href: "/settings" }
];

function escapedXml(value) {
  return String(value ?? "").replace(/[<>&'"]/g, (character) => ({
    "<": "&lt;", ">": "&gt;", "&": "&amp;", "'": "&apos;", "\"": "&quot;"
  })[character]);
}

function routeId(value) {
  if (!/^\d+$/.test(String(value ?? ""))) return null;
  const id = Number(value);
  return Number.isSafeInteger(id) && id > 0 ? id : null;
}

function redirect(context, location) {
  context.response.setHeader("Location", location);
  return context.text("", 303);
}

function flash(context, message) {
  context.session.flash = message;
}

function takeFlash(context) {
  const message = context.session.flash ?? "";
  delete context.session.flash;
  return message;
}

function stringArray(value) {
  if (Array.isArray(value)) return value.map(String);
  if (typeof value !== "string" || !value.trim()) return [];
  try {
    const parsed = JSON.parse(value);
    if (Array.isArray(parsed)) return parsed.map(String);
  } catch {
    return value.split(",").map((item) => item.trim()).filter(Boolean);
  }
  return [];
}

async function saveGeneratedImage(prompt, filename, size = "1024x1024") {
  const apiKey = process.env.OPENAI_API_KEY;
  if (!apiKey) throw new Error("Set OPENAI_API_KEY before generating images.");
  const response = await fetch("https://api.openai.com/v1/images/generations", {
    method: "POST",
    headers: { authorization: `Bearer ${apiKey}`, "content-type": "application/json" },
    body: JSON.stringify({ model: "gpt-image-1", prompt, size, quality: "high" }),
    signal: AbortSignal.timeout(300000)
  });
  const result = await response.json().catch(() => null);
  if (!response.ok) throw new Error(result?.error?.message ?? "Image generation request failed.");
  const encoded = result?.data?.[0]?.b64_json;
  const image = typeof encoded === "string" ? Buffer.from(encoded, "base64") : null;
  if (!image?.length || image.length > 30 * 1024 * 1024) throw new Error("Image provider returned an invalid image.");
  const directory = path.resolve(process.cwd(), "public", "uploads");
  await mkdir(directory, { recursive: true });
  await writeFile(path.join(directory, filename), image, { flag: "wx" });
  return `uploads/${filename}`;
}

function rowForView(row, path) {
  const label = row.title ?? row.name ?? `#${row.id}`;
  const description = row.description ?? row.script_segment ?? row.notes ?? "";
  return {
    title: String(label),
    status: String(row.status ?? ""),
    summary: String(description).slice(0, 240),
    href: `${path}/${row.id}`,
    id: String(row.id)
  };
}

export class ProductionController extends Controller {
  get db() { return this.service("db"); }
  get models() { return repositories(this.db); }
  get comfy() { return this.service("comfy")(); }
  get wantsHtml() {
    const accept = this.context.request.headers?.accept ?? "";
    return accept.includes("text/html") && !accept.includes("application/json");
  }

  async showLogin() {
    if (this.context.session.userId) return redirect(this.context, "/dashboard");
    const users = await this.db.query("SELECT COUNT(*) AS count FROM users");
    if (Number(users[0]?.count ?? 0) === 0) return redirect(this.context, "/setup");
    return this.render("auth/login", { title: "Production hub sign in", message: takeFlash(this.context) });
  }

  async setup() {
    const users = await this.db.query("SELECT COUNT(*) AS count FROM users");
    if (Number(users[0]?.count ?? 0) > 0) return redirect(this.context, "/login");
    return this.render("auth/setup", { title: "Create administrator", message: takeFlash(this.context) });
  }

  async createFirstUser() {
    const { name, email, password } = this.body;
    if (typeof name !== "string" || !name.trim() || typeof email !== "string" || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
      || typeof password !== "string" || password.length < 12) {
      return this.abort(422, "Enter a name, valid email address, and password with at least 12 characters.");
    }
    const { hashPassword } = await import("noderyx-framework");
    const hash = await hashPassword(password);
    const userId = await this.db.transaction(async (connection) => {
      const [lock] = await connection.execute("SELECT GET_LOCK(?, 5) AS acquired", ["sorrydotcomeditor:first-user"]);
      if (Number(lock[0]?.acquired) !== 1) throw new Error("Initial account setup is busy. Please try again.");
      try {
        const [rows] = await connection.execute("SELECT COUNT(*) AS count FROM users");
        if (Number(rows[0]?.count ?? 0) > 0) return null;
        const [result] = await connection.execute(
          "INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
          [name.trim(), email.trim().toLowerCase(), hash]
        );
        await connection.execute(
          "UPDATE projects SET user_id = ? WHERE user_id IS NULL",
          [result.insertId]
        );
        return result.insertId;
      } finally {
        await connection.execute("SELECT RELEASE_LOCK(?)", ["sorrydotcomeditor:first-user"]);
      }
    });
    if (!userId) return this.abort(409, "Initial setup has already been completed.");
    this.context.session.userId = userId;
    flash(this.context, "Administrator account created.");
    return redirect(this.context, "/dashboard");
  }

  async login() {
    const { email, password } = this.body;
    if (typeof email !== "string" || typeof password !== "string") {
      flash(this.context, "Enter your email and password.");
      return redirect(this.context, "/login");
    }
    const rows = await this.db.query("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1", [email.trim().toLowerCase()]);
    const user = rows[0];
    if (!user || !await this.verifyStoredPassword(password, user.password)) {
      flash(this.context, "Email or password is incorrect.");
      return redirect(this.context, "/login");
    }
    this.context.session.userId = user.id;
    return redirect(this.context, "/dashboard");
  }

  async verifyStoredPassword(password, stored) {
    if (String(stored).startsWith("$2")) {
      const bcrypt = await import("bcryptjs");
      return bcrypt.compare(password, stored);
    }
    const { verifyPassword } = await import("noderyx-framework");
    return verifyPassword(password, stored);
  }

  async logout() {
    delete this.context.session.userId;
    return redirect(this.context, "/login");
  }

  async dashboard() {
    const owner = this.context.session.userId;
    const [projects, projectCount, scenes, characters, editors, team, rendering, queued] = await Promise.all([
      this.db.query("SELECT p.*, (SELECT COUNT(*) FROM scenes s WHERE s.project_id = p.id) AS scene_count FROM projects p WHERE p.user_id = ? ORDER BY p.updated_at DESC LIMIT 8", [owner]),
      this.db.query("SELECT COUNT(*) AS count FROM projects WHERE user_id = ?", [owner]),
      this.db.query("SELECT COUNT(*) AS count FROM scenes WHERE project_id IN (SELECT id FROM projects WHERE user_id = ?)", [owner]),
      this.db.query("SELECT COUNT(*) AS count FROM characters WHERE project_id IN (SELECT id FROM projects WHERE user_id = ?) OR project_id IS NULL", [owner]),
      this.db.query("SELECT COUNT(*) AS count FROM editors"),
      this.db.query("SELECT id, name, role FROM editors ORDER BY created_at DESC LIMIT 5"),
      this.db.query("SELECT s.id, s.order_index, s.status, p.title AS project_title FROM scenes s INNER JOIN projects p ON p.id = s.project_id WHERE p.user_id = ? AND s.status IN ('Rendering', 'Processing') ORDER BY s.created_at DESC LIMIT 1", [owner]),
      this.db.query("SELECT s.id, s.order_index, s.status, p.title AS project_title FROM scenes s INNER JOIN projects p ON p.id = s.project_id WHERE p.user_id = ? AND s.status IN ('Draft', 'Queued') ORDER BY s.order_index, s.id LIMIT 1", [owner])
    ]);
    let storage;
    try {
      const disk = await statfs(process.cwd());
      const total = disk.blocks * disk.bsize;
      const available = disk.bavail * disk.bsize;
      if (!Number.isFinite(total) || total <= 0 || !Number.isFinite(available)) throw new Error("Invalid filesystem capacity");
      const used = Math.max(0, total - available);
      storage = `${(used / 1024 ** 3).toFixed(2)} GB / ${(total / 1024 ** 3).toFixed(2)} GB used (${Math.min(100, used / total * 100).toFixed(1)}%)`;
    } catch (error) {
      console.warn("Dashboard storage capacity unavailable:", error.message);
      storage = "Storage usage unavailable";
    }
    const project = projects[0];
    const sceneCount = Number(project?.scene_count ?? 0);
    const estimatedMinutes = sceneCount * 15;
    return this.render("dashboard", {
      title: "Production dashboard",
      message: takeFlash(this.context),
      navigation,
      createHref: "/projects/create",
      newSceneHref: "/scenes/create",
      syncFaceHref: "/tools/sync-face",
      scoreHref: "/tools/gen-score",
      scriptHref: "/tools/script",
      editorsHref: "/editors",
      stats: [
        { label: "Projects", value: String(projectCount[0]?.count ?? 0) },
        { label: "Scenes", value: String(scenes[0]?.count ?? 0) },
        { label: "Characters", value: String(characters[0]?.count ?? 0) },
        { label: "Editors", value: String(editors[0]?.count ?? 0) }
      ],
      activeProject: project ? {
        title: project.title,
        style: project.style_preset || "Unassigned",
        scenes: String(sceneCount),
        estimatedMinutes: String(estimatedMinutes),
        progress: String(Math.min(100, Math.round(estimatedMinutes / 150 * 100))),
        editorHref: `/projects/${project.id}/editor`,
        videoEditorHref: `/projects/${project.id}/videoeditor`
      } : null,
      renderingScene: rendering[0] ? {
        title: `Sequence #${rendering[0].order_index}`,
        project: rendering[0].project_title,
        status: rendering[0].status,
        href: `/scenes/${rendering[0].id}`
      } : null,
      nextScene: queued[0] ? {
        title: `Sequence #${queued[0].order_index}`,
        project: queued[0].project_title,
        status: queued[0].status,
        href: `/scenes/${queued[0].id}`
      } : null,
      storage,
      team: team.map((editor) => ({ name: editor.name, role: editor.role || "System editor", href: `/editors/${editor.id}` })),
      hasTeam: team.length > 0,
      rows: projects.map((row) => rowForView(row, "/projects")),
      hasRows: projects.length > 0
    });
  }

  async resourceIndex(path, query = this.query) {
    const definition = resourceDefinitions[path];
    if (!definition) return this.abort(404, "Resource not found.");
    const ownerId = this.context.session.userId;
    let rows;
    if (path === "projects") {
      rows = await this.db.query("SELECT * FROM projects WHERE user_id = ? ORDER BY updated_at DESC LIMIT 200", [ownerId]);
    } else if (["scenes", "characters", "production_plans", "trailers", "soundtracks"].includes(path)) {
      const join = path === "characters" ? "LEFT JOIN" : "INNER JOIN";
      rows = await this.db.query(
        `SELECT item.* FROM ${definition.table} item ${join} projects p ON p.id = item.project_id WHERE p.user_id = ?${path === "characters" ? " OR item.project_id IS NULL" : ""} ORDER BY item.id DESC LIMIT 200`,
        [ownerId]
      );
    } else {
      rows = await this.models[definition.table].all();
    }
    const id = routeId(query.id);
    if (id) rows = rows.filter((row) => Number(row.project_id) === id);
    if (path === "projects") {
      return this.render("projects", {
        title: definition.label,
        message: takeFlash(this.context),
        navigation,
        pageType: "index",
        createHref: "/projects/create",
        projects: rows.map((row) => ({
          ...rowForView(row, "/projects"),
          editorHref: `/projects/${row.id}/editor`,
          timelineHref: `/projects/${row.id}/timeline`,
          videoEditorHref: `/projects/${row.id}/videoeditor`,
          posterUrl: mediaUrl(row.poster_image)
        })),
        hasRows: rows.length > 0
      });
    }
    if (path === "scenes") {
      return this.render("scenes", {
        title: definition.label,
        message: takeFlash(this.context),
        navigation,
        pageType: "index",
        createHref: "/scenes/create",
        rows: rows.map((row) => rowForView(row, "/scenes")),
        hasRows: rows.length > 0
      });
    }
    if (path === "characters") {
      const projectOptions = await this.db.query(
        "SELECT id, title FROM projects WHERE user_id = ? ORDER BY title",
        [ownerId]
      );
      return this.render("characters", {
        title: definition.label,
        message: takeFlash(this.context),
        navigation,
        pageType: "index",
        createHref: "/characters/create",
        projectOptions: projectOptions.map((project) => ({ value: String(project.id), label: project.title })),
        hasProjects: projectOptions.length > 0,
        rows: rows.map((row) => ({
          ...rowForView(row, "/characters"),
          imageUrl: mediaUrl(row.image_path || row.reference_image)
        })),
        hasRows: rows.length > 0
      });
    }
    return this.render("resource", {
      title: definition.label,
      message: takeFlash(this.context),
      navigation,
      resource: path,
      createHref: `/${path}/create`,
      cancelHref: `/${path}`,
      rows: rows.map((row) => rowForView(row, `/${path}`)),
      hasRows: rows.length > 0,
      fields: [],
      formAction: `/${path}`,
      formMethod: "post",
      showForm: false,
      showDelete: false,
      emptyMessage: `No ${definition.label.toLowerCase()} yet.`,
      projectOptions: []
    });
  }

  async resourceForm(path, id = null) {
    const definition = resourceDefinitions[path];
    if (!definition) return this.abort(404, "Resource not found.");
    const row = id ? await this.findOwned(path, id) : null;
    const projectRows = definition.fields.some((field) => field.name === "project_id")
      ? await this.db.query("SELECT id, title FROM projects WHERE user_id = ? ORDER BY title", [this.context.session.userId])
      : [];
    const sceneCharacters = path === "scenes" && row
      ? await this.db.query("SELECT character_id FROM character_scene WHERE scene_id = ? ORDER BY character_id", [row.id])
      : [];
    const visibleFields = definition.fields.filter((field) => {
      if (path === "projects") return !["poster_image", "teaser_video", "final_movie_path"].includes(field.name) && (row || field.name !== "status");
      if (path === "scenes" && !row) return !["video_path", "status"].includes(field.name);
      return true;
    });
    const fieldRows = visibleFields.map((field) => ({
      ...field,
      type: field.name === "project_id" && projectRows.length
        ? "select"
        : field.type ?? "text",
      value: String(field.name === "characters" && sceneCharacters.length
        ? sceneCharacters.map(({ character_id }) => character_id).join(",")
        : row?.[field.name] ?? (field.name === "project_id" ? this.query.project_id ?? "" : "")),
      options: field.name === "project_id"
        ? projectRows.map((project) => ({ value: String(project.id), label: `${project.title} (#${project.id})`, selected: String(project.id) === String(row?.project_id ?? this.query.project_id ?? "") }))
        : (field.options ?? []).map((value) => ({ value, label: value, selected: value === row?.[field.name] }))
    }));
    if (path === "projects") {
      return this.render("projects", {
        title: `${row ? "Update" : "Initialize"} project`,
        message: takeFlash(this.context),
        navigation,
        pageType: "form",
        createHref: "/projects/create",
        fields: fieldRows,
        formAction: row ? `/projects/${row.id}` : "/projects",
        submitLabel: row ? "Save configuration" : "Create production"
      });
    }
    if (path === "scenes") {
      return this.render("scenes", {
        title: `${row ? "Edit" : "Create"} scene`,
        message: takeFlash(this.context),
        navigation,
        pageType: "form",
        createHref: "/scenes/create",
        fields: fieldRows,
        formAction: row ? `/scenes/${row.id}` : "/scenes",
        submitLabel: row ? "Save sequence" : "Lock sequence into timeline",
        cancelHref: row ? `/scenes/${row.id}` : "/scenes"
      });
    }
    if (path === "characters") {
      return this.render("characters", {
        title: `${row ? "Edit" : "Initialize"} character`,
        message: takeFlash(this.context),
        navigation,
        pageType: "form",
        createHref: "/characters/create",
        fields: fieldRows,
        character: { name: row?.name ?? "", role: row?.role ?? "" },
        formAction: row ? `/characters/${row.id}` : "/characters",
        submitLabel: row ? "Update identity" : "Save identity"
      });
    }
    return this.render("resource", {
      title: `${row ? "Edit" : "Create"} ${definition.singular}`,
      message: takeFlash(this.context),
      navigation,
      resource: path,
      createHref: `/${path}/create`,
      cancelHref: `/${path}`,
      rows: [],
      fields: fieldRows,
      formAction: row ? `/${path}/${row.id}` : `/${path}`,
      formMethod: row ? "post" : "post",
      showForm: true,
      showDelete: Boolean(row),
      deleteAction: row ? `/${path}/${row.id}/delete` : "",
      emptyMessage: "",
      projectOptions: projectRows
    });
  }

  async resourceShow(path, id) {
    const definition = resourceDefinitions[path];
    if (!definition) return this.abort(404, "Resource not found.");
    const row = await this.findOwned(path, id);
    const fields = Object.entries(row).filter(([key, value]) => !["password", "remember_token"].includes(key) && value !== null).map(([label, value]) => ({
      label: label.replaceAll("_", " "),
      value: String(value)
    }));
    if (path === "projects") {
      const scenes = await this.db.query("SELECT * FROM scenes WHERE project_id = ? ORDER BY order_index, id", [row.id]);
      const editors = await this.db.query("SELECT id, name FROM editors WHERE is_active = 1 ORDER BY name LIMIT 200");
      return this.render("projects", {
        title: row.title,
        message: takeFlash(this.context),
        navigation,
        pageType: "show",
        project: { ...row, posterUrl: mediaUrl(row.poster_image) },
        rows: scenes.map((scene) => rowForView(scene, "/scenes")),
        hasRows: scenes.length > 0,
        editHref: `/projects/${row.id}/edit`,
        timelineHref: `/projects/${row.id}/timeline`,
        videoEditorHref: `/projects/${row.id}/videoeditor`,
        editorHref: `/projects/${row.id}/editor`,
        xmlHref: `/projects/${row.id}/export-xml`,
        downloadHref: `/projects/${row.id}/download-video/1080p`,
        sceneCreateHref: `/scenes/create?project_id=${row.id}`,
        editorAssignAction: `/projects/${row.id}/assign-editor`,
        editors: editors.map((editor) => ({ value: String(editor.id), label: editor.name })),
        hasEditors: editors.length > 0,
        renderAction: `/projects/${row.id}/render-batch`,
        posterAction: `/projects/${row.id}/generate-poster`,
        deleteAction: `/projects/${row.id}/delete`
      });
    }
    if (path === "scenes") {
      const [projectRows, characterRows] = await Promise.all([
        this.db.query("SELECT title FROM projects WHERE id = ? LIMIT 1", [row.project_id]),
        this.db.query(
          "SELECT c.id, c.name, c.role FROM characters c INNER JOIN character_scene cs ON cs.character_id = c.id WHERE cs.scene_id = ? ORDER BY c.name",
          [row.id]
        )
      ]);
      return this.render("scenes", {
        title: `Sequence #${row.order_index}`,
        message: takeFlash(this.context),
        navigation,
        pageType: "show",
        scene: row,
        projectTitle: projectRows[0]?.title ?? "Unknown project",
        videoUrl: mediaUrl(row.video_path),
        characters: characterRows,
        hasCharacters: characterRows.length > 0,
        editHref: `/scenes/${row.id}/edit`,
        renderAction: `/scenes/${row.id}/render`,
        seedanceAction: `/scenes/${row.id}/generate-seedance`,
        syncAction: `/scenes/${row.id}/sync-render`,
        deleteAction: `/scenes/${row.id}/delete`
      });
    }
    if (path === "characters") {
      const projectRows = await this.db.query("SELECT id, title FROM projects WHERE user_id = ? ORDER BY title", [this.context.session.userId]);
      return this.render("characters", {
        title: row.name,
        message: takeFlash(this.context),
        navigation,
        pageType: "show",
        character: { ...row, imageUrl: mediaUrl(row.image_path || row.reference_image) },
        editHref: `/characters/${row.id}/edit`,
        exportHref: `/characters/${row.id}/export`,
        imageAction: `/characters/${row.id}/generate-image`,
        prepareAction: `/characters/${row.id}/sync-face`,
        deleteAction: `/characters/${row.id}/delete`,
        transferAction: `/characters/${row.id}/transfer`,
        projectOptions: projectRows.map((project) => ({ value: String(project.id), label: project.title }))
      });
    }
    return this.render("resource", {
      title: `${definition.singular} #${row.id}`,
      message: takeFlash(this.context),
      navigation,
      resource: path,
      createHref: `/${path}/create`,
      cancelHref: `/${path}`,
      rows: [],
      fields,
      formAction: `/${path}/${row.id}/edit`,
      formMethod: "get",
      showForm: false,
      showDelete: true,
      deleteAction: `/${path}/${row.id}/delete`,
      editHref: `/${path}/${row.id}/edit`,
      renderAction: path === "scenes" ? `/scenes/${row.id}/render` : "",
      syncAction: path === "scenes" ? `/scenes/${row.id}/sync-render` : "",
      seedanceAction: path === "scenes" ? `/scenes/${row.id}/generate-seedance` : "",
      prepareAction: path === "characters" ? `/characters/${row.id}/sync-face` : "",
      posterAction: path === "projects" ? `/projects/${row.id}/generate-poster` : "",
      imageAction: path === "characters" ? `/characters/${row.id}/generate-image` : "",
      exportHref: path === "characters" ? `/characters/${row.id}/export` : "",
      storyAction: path === "production_plans" ? "/planning/generate-story" : "",
      storyProjectId: path === "production_plans" ? String(row.project_id) : "",
      timelineHref: path === "projects" ? `/projects/${row.id}/timeline` : "",
      videoEditorHref: path === "projects" ? `/projects/${row.id}/videoeditor` : "",
      emptyMessage: "",
      projectOptions: []
    });
  }

  async findOwned(path, idValue) {
    const id = routeId(idValue);
    if (!id) return this.abort(404, "Record not found.");
    const definition = resourceDefinitions[path];
    const model = this.models[definition.table];
    const row = await model.find(id);
    if (!row) return this.abort(404, "Record not found.");
    const userId = Number(this.context.session.userId);
    if (path === "projects" && Number(row.user_id) !== userId) return this.abort(404, "Record not found.");
    if (path === "characters" && row.project_id === null) return row;
    if (["scenes", "characters", "production_plans", "trailers", "soundtracks"].includes(path)) {
      const project = await this.db.query("SELECT user_id FROM projects WHERE id = ? LIMIT 1", [row.project_id]);
      if (!project[0] || Number(project[0].user_id) !== userId) return this.abort(404, "Record not found.");
    }
    return row;
  }

  async resourceStore(path, id = null) {
    const definition = resourceDefinitions[path];
    if (!definition) return this.abort(404, "Resource not found.");
    const body = this.body && typeof this.body === "object" ? this.body : {};
    const values = Object.fromEntries(definition.fields
      .filter((field) => Object.hasOwn(body, field.name))
      .map((field) => [field.name, body[field.name]]));
    for (const field of definition.fields) {
      if (field.required && !String(values[field.name] ?? "").trim()) {
        return this.abort(422, `${field.label} is required.`);
      }
    }
    if (["scenes", "characters", "production_plans", "trailers", "soundtracks"].includes(path)) {
      const projectId = routeId(values.project_id);
      if (!projectId) return this.abort(422, "Choose a valid project.");
      await this.findOwned("projects", projectId);
      values.project_id = projectId;
    }
    if (path === "projects") {
      values.user_id = this.context.session.userId;
      if (values.title && !values.slug) {
        values.slug = `${values.title.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "")}-${randomUUID().slice(0, 8)}`;
      }
    }
    if (path === "production_plans") {
      const projectId = routeId(values.project_id);
      if (!projectId) return this.abort(422, "Choose a valid project.");
      await this.findOwned("projects", projectId);
      values.project_id = projectId;
      const [existing] = await this.db.query("SELECT id FROM production_plans WHERE project_id = ? LIMIT 1", [projectId]);
      if (existing && (!id || Number(existing.id) !== Number(id))) {
        id = existing.id;
      }
    }
    if (["soundtracks", "trailers"].includes(path) && values.project_id) {
      const projectId = routeId(values.project_id);
      if (!projectId) return this.abort(422, "Choose a valid project.");
      await this.findOwned("projects", projectId);
      values.project_id = projectId;
    }
    if (id) {
      const existing = await this.findOwned(path, id);
      if (path === "scenes" && this.body?.characters !== undefined) await this.syncSceneCharacters(existing.id, values.project_id ?? existing.project_id, this.body.characters);
      await this.models[definition.table].update(existing.id, values);
      flash(this.context, `${definition.singular} updated.`);
      if (path === "scenes" && this.body?.return_to_videoeditor === "1") {
        return redirect(this.context, `/projects/${existing.project_id}/videoeditor?scene_id=${existing.id}`);
      }
      if (path === "scenes" && this.body?.return_to_editor === "1") {
        return redirect(this.context, `/projects/${existing.project_id}/editor`);
      }
      return redirect(this.context, `/${path}/${existing.id}`);
    }
    const created = await this.models[definition.table].create(values);
    if (path === "scenes" && this.body?.characters !== undefined) await this.syncSceneCharacters(created.id, created.project_id, this.body.characters);
    if (path === "scenes") {
      await this.db.query("UPDATE projects SET total_scenes = (SELECT COUNT(*) FROM scenes WHERE project_id = ?), updated_at = CURRENT_TIMESTAMP WHERE id = ?", [created.project_id, created.project_id]);
    }
    flash(this.context, `${definition.singular} created.`);
    return redirect(this.context, `/${path}/${created.id}`);
  }

  async resourceDelete(path, id) {
    const definition = resourceDefinitions[path];
    if (!definition) return this.abort(404, "Resource not found.");
    const row = await this.findOwned(path, id);
    if (path === "projects") {
      for (const table of ["character_scene", "editor_project", "scenes", "characters", "production_plans", "soundtracks", "trailers"]) {
        const key = table === "character_scene" ? "scene_id" : table === "editor_project" ? "project_id" : "project_id";
        const predicate = table === "character_scene"
          ? "scene_id IN (SELECT id FROM scenes WHERE project_id = ?)"
          : `${key} = ?`;
        await this.db.query(`DELETE FROM ${table} WHERE ${predicate}`, [row.id]);
      }
    } else if (path === "scenes") {
      await this.db.query("DELETE FROM character_scene WHERE scene_id = ?", [row.id]);
    } else if (path === "characters") {
      await this.db.query("DELETE FROM character_scene WHERE character_id = ?", [row.id]);
    }
    await this.models[definition.table].delete(row.id);
    if (path === "scenes") {
      await this.db.query("UPDATE projects SET total_scenes = (SELECT COUNT(*) FROM scenes WHERE project_id = ?), updated_at = CURRENT_TIMESTAMP WHERE id = ?", [row.project_id, row.project_id]);
    }
    flash(this.context, `${definition.singular} deleted.`);
    return redirect(this.context, `/${path}`);
  }

  async syncSceneCharacters(sceneId, projectId, supplied) {
    const ids = [...new Set(stringArray(supplied).map((value) => routeId(value)).filter(Boolean))];
    if (ids.length > 100) return this.abort(422, "A scene cannot have more than 100 assigned characters.");
    if (!ids.length) {
      await this.db.query("DELETE FROM character_scene WHERE scene_id = ?", [sceneId]);
      return;
    }
    const valid = await this.db.query(
      `SELECT id FROM characters WHERE id IN (${ids.map(() => "?").join(",")}) AND (project_id = ? OR project_id IS NULL)`,
      [...ids, projectId]
    );
    if (valid.length !== ids.length) return this.abort(422, "Selected characters must belong to this project.");
    await this.db.query("DELETE FROM character_scene WHERE scene_id = ?", [sceneId]);
    for (const characterId of ids) {
      await this.db.query("INSERT INTO character_scene (character_id, scene_id) VALUES (?, ?)", [characterId, sceneId]);
    }
  }

  async projectView(view) {
    const project = await this.findOwned("projects", this.params.id);
    const scenes = await this.db.query("SELECT * FROM scenes WHERE project_id = ? ORDER BY order_index, id", [project.id]);
    if (view === "editor") {
      const characters = await this.db.query("SELECT * FROM characters WHERE project_id = ? ORDER BY id", [project.id]);
      return this.studioResponse(renderProductionEditor({ project, scenes, characters, csrfToken: this.context.csrfToken }));
    }
    return this.render("timeline", {
      title: `${project.title} — ${view}`,
      message: takeFlash(this.context),
      navigation,
      projectHref: `/projects/${project.id}`,
      sceneCreateHref: `/scenes/create?project_id=${project.id}`,
      editorHref: `/projects/${project.id}/editor`,
      videoEditorHref: `/projects/${project.id}/videoeditor`,
      xmlHref: `/projects/${project.id}/export-xml`,
      renderAction: `/projects/${project.id}/render-batch`,
      rows: scenes.map((scene) => ({
        ...rowForView(scene, "/scenes"),
        title: `Sequence #${scene.order_index}`,
        renderHref: `/scenes/${scene.id}/render`,
        syncHref: `/scenes/${scene.id}/sync-render`,
        deleteHref: `/scenes/${scene.id}/delete`
      })),
      hasRows: scenes.length > 0,
    });
  }

  async recentTimeline() {
    const projects = await this.db.query(
      "SELECT id FROM projects WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1",
      [this.context.session.userId]
    );
    return redirect(this.context, projects.length ? `/projects/${projects[0].id}/timeline` : "/projects");
  }

  async recentVideoEditor() {
    const projects = await this.db.query(
      "SELECT id FROM projects WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1",
      [this.context.session.userId]
    );
    return redirect(this.context, projects.length ? `/projects/${projects[0].id}/videoeditor` : "/projects");
  }

  async projectVideoEditor() {
    const project = await this.findOwned("projects", this.params.id);
    const scenes = await this.db.query("SELECT * FROM scenes WHERE project_id = ? ORDER BY order_index, id", [project.id]);
    const requestedSceneId = routeId(this.query.scene_id ?? this.query.active_scene);
    const scene = requestedSceneId
      ? scenes.find((candidate) => Number(candidate.id) === requestedSceneId)
      : scenes.find((candidate) => candidate.video_path) ?? scenes[0];
    if (requestedSceneId && !scene) return this.abort(404, "Scene not found in this production.");
    return this.studioResponse(renderStudio({ project, scenes, currentScene: scene, csrfToken: this.context.csrfToken }));
  }

  studioResponse(html) {
    // Original studio controls use inline layout styles. Scripts and event
    // listeners are served locally and retain the existing script policy.
    const headers = this.context.response?.baseHeaders;
    if (headers?.["content-security-policy"]) {
      headers["content-security-policy"] = headers["content-security-policy"].replace(/style-src [^;]+/, "style-src 'self' 'unsafe-inline'");
    }
    return this.text(html, 200, "text/html; charset=utf-8");
  }

  async exportXml() {
    const project = await this.findOwned("projects", this.params.id);
    const scenes = await this.db.query("SELECT * FROM scenes WHERE project_id = ? ORDER BY order_index, id", [project.id]);
    const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<project id="${project.id}" title="${escapedXml(project.title)}">\n${scenes.map((scene) =>
      `  <scene id="${scene.id}" order="${scene.order_index}">${escapedXml(scene.script_segment)}</scene>`
    ).join("\n")}\n</project>`;
    return this.text(xml, 200, "application/xml; charset=utf-8");
  }

  async runPodStatus() {
    this.context.response.setHeader("Cache-Control", "no-store");
    if (!process.env.COMFYUI_URL) return this.json({ online: false, error: "Set COMFYUI_URL to check the render worker." });
    try {
      return this.json(await this.comfy.status());
    } catch (error) {
      console.error("Render worker status unavailable:", error);
      return this.json({ online: false, error: "Unable to reach the render worker. Check the server logs." }, 503);
    }
  }

  async generateStory() {
    const projectId = routeId(this.body?.project_id ?? this.query.project_id);
    if (!projectId) return this.abort(422, "A valid project_id is required.");
    const project = await this.findOwned("projects", projectId);
    const result = await this.service("ai").generate(
      `Write a film story and concise scene outline for "${project.title}". Premise: ${this.body?.logline ?? project.description ?? "Develop an original cinematic story."}`,
      { instructions: "Return a useful production-ready story and scene outline. Do not invent production metadata." }
    );
    const existing = await this.db.query("SELECT id FROM production_plans WHERE project_id = ? LIMIT 1", [project.id]);
    let planId;
    if (existing[0]) {
      planId = existing[0].id;
      await this.db.query("UPDATE production_plans SET full_story = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [result.text, existing[0].id]);
    } else {
      const plan = await this.models.production_plans.create({ project_id: project.id, logline: this.body?.logline ?? project.description ?? "", full_story: result.text });
      planId = plan.id;
    }
    if (this.wantsHtml) {
      flash(this.context, "Production story generated.");
      return redirect(this.context, `/production_plans/${planId}`);
    }
    return this.json({ story: result.text });
  }

  async generateCharacterDetails() {
    const name = String(this.body?.name ?? "").trim();
    if (!name) return this.abort(422, "Character name is required.");
    const result = await this.service("ai").generate(
      `Create a concise fictional character profile for ${name}. Role: ${this.body?.role ?? "supporting"}.`,
      { instructions: "Return JSON with description, personality, dialogue_style, prompt, and video_prompt. Keep all values appropriate for a production workspace." }
    );
    let details;
    try { details = JSON.parse(result.text); } catch { return this.json({ message: "The AI provider returned an invalid character profile." }, 502); }
    return this.json(details);
  }

  async generateSceneVideo() {
    const projectId = routeId(this.body?.project_id);
    const segment = String(this.body?.script_segment ?? "").trim();
    const characterIds = stringArray(this.body?.characters);
    if (!projectId || !segment || characterIds.length === 0) return this.abort(422, "Choose a project, add a scene description, and select at least one character.");
    if (characterIds.length > 100) return this.abort(422, "A scene cannot have more than 100 assigned characters.");
    const project = await this.findOwned("projects", projectId);
    const characters = await this.db.query(
      `SELECT * FROM characters WHERE id IN (${characterIds.map(() => "?").join(",")}) AND (project_id = ? OR project_id IS NULL)`,
      [...characterIds, project.id]
    );
    if (characters.length !== new Set(characterIds).size) return this.abort(422, "Selected characters must belong to this project.");
    const prompt = this.seedancePrompt(segment, characters);
    const apiKey = process.env.SEEDANCE_API_KEY;
    const baseUrl = process.env.SEEDANCE_BASE_URL;
    if (!apiKey || !baseUrl) return this.abort(503, "Configure SEEDANCE_API_KEY and SEEDANCE_BASE_URL before generating scenes.");
    let data;
    try {
      data = await this.submitSeedance(prompt, apiKey, baseUrl);
    } catch (error) {
      return this.abort(error.message.startsWith("Configure SEEDANCE") ? 503 : 502, error.message);
    }
    const jobId = data?.id ?? data?.job_id ?? null;
    const created = await this.models.scenes.create({
      project_id: project.id,
      order_index: Number(this.body?.order_index) || 1,
      script_segment: segment,
      prompt,
      video_url: data?.output?.[0] ?? null,
      seedance_job_id: jobId,
      status: "Processing"
    });
    await this.syncSceneCharacters(created.id, project.id, characterIds);
    await this.db.query("UPDATE projects SET total_scenes = (SELECT COUNT(*) FROM scenes WHERE project_id = ?), updated_at = CURRENT_TIMESTAMP WHERE id = ?", [project.id, project.id]);
    return this.json({ id: created.id, job_id: jobId, video_url: created.video_url, status: created.status }, 202);
  }

  seedancePrompt(segment, characters) {
    const cast = characters.map((character) => [
      `CHARACTER NAME: ${character.name}`,
      `ROLE: ${character.role ?? ""}`,
      `AI TAG: ${character.ai_tag ?? ""}`,
      `CHARACTER PROMPT: ${character.prompt ?? ""}`
    ].join("\n")).join("\n\n");
    return [
      "SEEDANCE 2.0 CINEMATIC SCENE GENERATION",
      `SCENE ACTION:\n${segment}`,
      `CAST:\n${cast}`,
      "Ultra-realistic live-action cinematic Sri Lankan movie scene. Real human actors, natural motion, emotional acting, film-grade lighting and a 24fps movie look.",
      "No anime, cartoons, 3D renders, illustrations, game characters, logos or watermarks.",
      "Create a 6-second cinematic cutscene suitable for a movie trailer."
    ].join("\n\n");
  }

  async submitSeedance(prompt, apiKey = process.env.SEEDANCE_API_KEY, baseUrl = process.env.SEEDANCE_BASE_URL) {
    if (!apiKey || !baseUrl) throw new Error("Configure SEEDANCE_API_KEY and SEEDANCE_BASE_URL before generating scenes.");
    const response = await fetch(`${baseUrl.replace(/\/+$/, "")}/text2video`, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ key: apiKey, prompt, width: 1024, height: 576, duration: 6, guidance_scale: 7.5 }),
      signal: AbortSignal.timeout(300000)
    });
    const data = await response.json().catch(() => null);
    if (!response.ok) throw new Error(data?.message ?? "Seedance scene generation failed.");
    return data;
  }

  async generateSeedanceForScene() {
    const scene = await this.findOwned("scenes", this.params.id);
    const characters = await this.db.query(
      "SELECT c.* FROM characters c INNER JOIN character_scene cs ON cs.character_id = c.id WHERE cs.scene_id = ?",
      [scene.id]
    );
    if (!characters.length) return this.abort(422, "Assign at least one character before generating the scene.");
    let data;
    try {
      data = await this.submitSeedance(this.seedancePrompt(scene.script_segment, characters));
    } catch (error) {
      return this.abort(error.message.startsWith("Configure SEEDANCE") ? 503 : 502, error.message);
    }
    const jobId = data?.id ?? data?.job_id ?? null;
    await this.models.scenes.update(scene.id, {
      prompt: this.seedancePrompt(scene.script_segment, characters),
      video_url: data?.output?.[0] ?? null,
      seedance_job_id: jobId,
      status: "Processing"
    });
    flash(this.context, "Seedance scene generation submitted.");
    return redirect(this.context, `/scenes/${scene.id}`);
  }

  async localWanStatus() {
    this.context.response.setHeader("Cache-Control", "no-store");
    try {
      return this.json(await this.service("comfyLocal")().readiness());
    } catch (error) {
      return this.json({ online: false, ready: false, message: `Local Wan is unavailable. Start it with npm run wan:start. ${error.message}` });
    }
  }

  async kaggleNotebook() {
    const notebook = await readFile(new URL("../../notebooks/wan21-kaggle.ipynb", import.meta.url), "utf8");
    this.context.response.setHeader("Content-Disposition", 'attachment; filename="wan21-kaggle.ipynb"');
    return this.text(notebook, 200, "application/x-ipynb+json; charset=utf-8");
  }

  async kaggleConnection() {
    this.context.response.setHeader("Cache-Control", "private, no-store");
    try { return this.json(await this.service("kaggle").connection(this.context.session.userId)); }
    catch (error) { return this.json({ connected: false, available: false, message: error.message }, 503); }
  }

  async connectKaggle() {
    this.context.response.setHeader("Cache-Control", "private, no-store");
    try { return this.json(await this.service("kaggle").connect(this.context.session.userId, this.body)); }
    catch (error) { return this.json({ message: error.message }, 422); }
  }

  async disconnectKaggle() {
    await this.service("kaggle").disconnect(this.context.session.userId);
    return this.json({ connected: false });
  }

  async renderKaggleScene() {
    const scene = await this.findOwned("scenes", this.params.id);
    if (["Rendering", "Processing"].includes(scene.status)) return this.json({ message: "This scene already has a running generation job." }, 409);
    const prompt = typeof this.body?.prompt === "string" ? this.body.prompt.trim() : "";
    if (!prompt || prompt.length > 8000) return this.json({ message: "Enter a video prompt between 1 and 8000 characters." }, 422);
    await this.models.scenes.update(scene.id, { prompt });
    const notebook = await this.kaggleNotebookDocument();
    if (!notebook) return;
    try { return this.json(await this.service("kaggle").submit(this.context.session.userId, scene, notebook.body), 202); }
    catch (error) { return this.json({ message: error.message }, 503); }
  }

  async kaggleSceneProgress() {
    const scene = await this.findOwned("scenes", this.params.id);
    this.context.response.setHeader("Cache-Control", "private, no-store");
    if (!String(scene.generation_job_id || "").startsWith("kaggle:")) return this.json({ status: scene.status, video_path: mediaUrl(scene.video_path), ready: Boolean(scene.video_path) });
    if (scene.status === "failed") return this.json({ status: "failed", message: scene.generation_error, notebook_url: `https://www.kaggle.com/code/${scene.generation_job_id.slice(7)}` });
    try { return this.json(await this.service("kaggle").sync(this.context.session.userId, scene)); }
    catch (error) { return this.json({ message: error.message, pending: true }, 502); }
  }

  async kaggleScene() {
    const job = await this.kaggleJob();
    if (!job) return;
    this.context.response.setHeader("Cache-Control", "private, no-store");
    this.context.response.setHeader("Content-Disposition", `attachment; filename="scene-${job.scene_id}-wan-job.json"`);
    return this.text(JSON.stringify(job, null, 2), 200, "application/json; charset=utf-8");
  }

  async saveKagglePrompt() {
    const scene = await this.findOwned("scenes", this.params.id);
    const prompt = typeof this.body?.prompt === "string" ? this.body.prompt.trim() : "";
    if (!prompt || prompt.length > 8000) return this.abort(422, "Enter a video prompt between 1 and 8000 characters.");
    await this.models.scenes.update(scene.id, { prompt });
    flash(this.context, "Video prompt saved. Download the scene notebook for 1080p output.");
    return redirect(this.context, `/projects/${scene.project_id}/videoeditor?scene_id=${scene.id}`);
  }

  async kaggleSceneNotebook() {
    const document = await this.kaggleNotebookDocument();
    if (!document) return;
    this.context.response.setHeader("Cache-Control", "private, no-store");
    this.context.response.setHeader("Content-Disposition", `attachment; filename="scene-${document.sceneId}-1080p.ipynb"`);
    return this.text(document.body, 200, "application/x-ipynb+json; charset=utf-8");
  }

  async openKaggleNotebook() {
    const document = await this.kaggleNotebookDocument();
    if (!document) return;
    const token = await createNotebookTransfer(document.body);
    const source = new URL(`/wan-kaggle/import/${token}/notebook.ipynb`, process.env.APP_URL || "http://localhost:3000");
    this.context.response.setHeader("Cache-Control", "private, no-store");
    this.context.response.setHeader("Referrer-Policy", "no-referrer");
    return redirect(this.context, `https://www.kaggle.com/kernels/welcome?src=${encodeURIComponent(source.href)}`);
  }

  async kaggleNotebookImport() {
    const notebook = await readNotebookTransfer(String(this.params.token ?? ""));
    this.context.response.setHeader("Cache-Control", "private, no-store");
    this.context.response.setHeader("X-Robots-Tag", "noindex, nofollow");
    if (!notebook) return this.abort(404, "Notebook import link expired or not found. Open the scene in Kaggle again from the editor.");
    return this.text(notebook, 200, "application/x-ipynb+json; charset=utf-8");
  }

  async kaggleNotebookDocument() {
    const job = await this.kaggleJob();
    if (!job) return;
    const notebook = JSON.parse(await readFile(new URL("../../notebooks/wan21-kaggle.ipynb", import.meta.url), "utf8"));
    const cell = notebook.cells.find(cell => cell.cell_type === "code" && cell.source.join("").includes("if JOB_FILE:"));
    if (!cell) throw new Error("Notebook scene settings cell is missing.");
    const encoded = Buffer.from(JSON.stringify(job), "utf8").toString("hex");
    const settings = `editor_job = json.loads(bytes.fromhex("${encoded}").decode("utf-8"))\nSCENE_ID = editor_job["scene_id"]\nPROMPT = editor_job["prompt"]\nNEGATIVE_PROMPT = editor_job["negative_prompt"]\nPRESET = "1080p"\nSEED = editor_job["seed"]\n\n`;
    cell.source = cell.source.join("").replace("if JOB_FILE:", `${settings}if JOB_FILE:`).split(/(?<=\n)/);
    return { sceneId: job.scene_id, body: JSON.stringify(notebook) };
  }

  async kaggleJob() {
    const scene = await this.findOwned("scenes", this.params.id);
    const project = await this.findOwned("projects", scene.project_id);
    const characters = await this.db.query("SELECT c.name, c.role, c.description FROM characters c INNER JOIN character_scene cs ON cs.character_id = c.id WHERE cs.scene_id = ?", [scene.id]);
    const prompt = [
      project.style_preset ? `Visual style: ${project.style_preset}.` : "Cinematic live-action video.",
      scene.prompt || scene.script_segment,
      characters.length ? `Cast: ${characters.map(character => `${character.name} (${character.role || "supporting"}): ${character.description || ""}`).join("; ")}` : "",
      "Natural motion, coherent anatomy, film lighting, no subtitles or watermarks."
    ].filter(Boolean).join("\n\n");
    if (prompt.length > 12000) {
      this.abort(422, "This scene prompt is too long for the Kaggle notebook. Shorten the scene or cast descriptions first.");
      return null;
    }
    return { format: "sorrydotcomeditor.wan-kaggle.v1", scene_id: Number(scene.id), project_id: Number(scene.project_id), prompt, negative_prompt: "low quality, blurry, distorted faces, extra limbs, cartoon, watermark, text, subtitles", preset: "1080p", seed: 42 };
  }

  async renderLocalScene() {
    return this.renderScene("local");
  }

  async renderScene(provider = "configured") {
    const scene = await this.findOwned("scenes", this.params.id);
    if (["Rendering", "Processing"].includes(scene.status)) return this.abort(409, "This scene already has a running generation job.");
    const characters = await this.db.query(
      "SELECT c.* FROM characters c INNER JOIN character_scene cs ON cs.character_id = c.id WHERE cs.scene_id = ?",
      [scene.id]
    );
    const local = provider === "local";
    let jobId;
    try {
      const service = local ? this.service("comfyLocal")() : this.comfy;
      jobId = await service.submitScene(scene, characters);
    } catch (error) {
      return this.abort(503, `${local ? "Local Wan 2.1" : "ComfyUI"} could not queue the scene: ${error.message}`);
    }
    await this.models.scenes.update(scene.id, { status: "Rendering", generation_job_id: local ? `local:${jobId}` : jobId, generation_error: null });
    if (this.wantsHtml) {
      flash(this.context, "Scene render queued.");
      if (local) return redirect(this.context, `/projects/${scene.project_id}/videoeditor?scene_id=${scene.id}`);
      return redirect(this.context, `/scenes/${scene.id}`);
    }
    return this.json({ scene_id: scene.id, job_id: jobId, status: "Rendering" }, 202);
  }

  async syncSceneRender() {
    const scene = await this.findOwned("scenes", this.params.id);
    if (!scene.generation_job_id) return this.abort(409, "This scene has no ComfyUI render job.");
    const local = scene.generation_job_id.startsWith("local:");
    const service = local ? this.service("comfyLocal")() : this.comfy;
    let videoPath;
    try {
      videoPath = await service.downloadCompletedVideo(local ? scene.generation_job_id.slice(6) : scene.generation_job_id);
    } catch (error) {
      await this.models.scenes.update(scene.id, { status: "failed", generation_error: error.message });
      return this.abort(502, error.message);
    }
    if (!videoPath) {
      if (this.wantsHtml) {
        flash(this.context, "The scene render is still processing.");
        if (local) return redirect(this.context, `/projects/${scene.project_id}/videoeditor?scene_id=${scene.id}`);
        return redirect(this.context, `/scenes/${scene.id}`);
      }
      return this.json({ scene_id: scene.id, status: scene.status, pending: true }, 202);
    }
    await this.models.scenes.update(scene.id, { video_path: videoPath, status: "Ready", generation_error: null });
    if (this.wantsHtml) {
      flash(this.context, "Scene render completed.");
      if (local) return redirect(this.context, `/projects/${scene.project_id}/videoeditor?scene_id=${scene.id}`);
      return redirect(this.context, `/scenes/${scene.id}`);
    }
    return this.json({ scene_id: scene.id, status: "Ready", video_path: `/public/${videoPath}` });
  }

  async attachVideo() {
    const scene = await this.findOwned("scenes", this.params.id);
    const videoPath = String(this.body?.video_path ?? "");
    if (!/^uploads\/scenes\/[a-zA-Z0-9._-]+$/.test(videoPath)) return this.abort(422, "Choose a valid generated scene video.");
    const absolute = path.resolve(process.cwd(), "public", videoPath);
    const root = path.resolve(process.cwd(), "public", "uploads", "scenes");
    if (!absolute.startsWith(`${root}${path.sep}`)) return this.abort(422, "Invalid video path.");
    try {
      const { size } = await import("node:fs/promises").then(({ stat }) => stat(absolute));
      if (!size) return this.abort(422, "The selected video file is empty.");
    } catch (error) {
      if (error.code === "ENOENT") return this.abort(404, "The selected video file was not found.");
      throw error;
    }
    await this.models.scenes.update(scene.id, { video_path: videoPath, status: "Ready" });
    return redirect(this.context, `/projects/${scene.project_id}/videoeditor`);
  }

  async renderBatch() {
    const project = await this.findOwned("projects", this.params.id);
    const scenes = await this.db.query("SELECT * FROM scenes WHERE project_id = ? AND status NOT IN ('Rendering', 'Processing') ORDER BY order_index, id", [project.id]);
    let queued = 0;
    for (const scene of scenes) {
      try {
        const characters = await this.db.query(
          "SELECT c.* FROM characters c INNER JOIN character_scene cs ON cs.character_id = c.id WHERE cs.scene_id = ?",
          [scene.id]
        );
        const jobId = await this.comfy.submitScene(scene, characters);
        await this.models.scenes.update(scene.id, { status: "Rendering", generation_job_id: jobId, generation_error: null });
        queued += 1;
      } catch (error) {
        await this.models.scenes.update(scene.id, { status: "failed", generation_error: error.message });
      }
    }
    await this.db.query("UPDATE projects SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [queued ? "generating" : "failed", project.id]);
    if (this.wantsHtml) {
      flash(this.context, `${queued} scene render${queued === 1 ? "" : "s"} queued.`);
      return redirect(this.context, `/projects/${project.id}`);
    }
    return this.json({ project_id: project.id, queued, attempted: scenes.length });
  }

  async generatePosterIdeas() {
    const project = await this.findOwned("projects", this.body?.project_id);
    const response = await this.service("ai").generate(
      `Movie: ${project.title}\nStory: ${project.description ?? ""}\nStyle: ${project.style_preset ?? ""}\nAspect ratio: ${project.aspect_ratio ?? ""}`,
      { instructions: "Act as a movie-poster creative director. Return only JSON with exactly two string keys: tagline (under 12 words) and direction (one production-ready paragraph covering subject, setting, composition, palette, lighting, typography placement and visual symbolism). No markdown." }
    );
    try {
      const ideas = JSON.parse(response.text);
      if (typeof ideas.tagline !== "string" || typeof ideas.direction !== "string") throw new Error("Invalid response shape");
      if (this.wantsHtml) {
        flash(this.context, `Suggested tagline: ${ideas.tagline} | Creative direction: ${ideas.direction}`);
        return redirect(this.context, "/posters");
      }
      return this.json({ tagline: ideas.tagline, direction: ideas.direction });
    } catch {
      return this.json({ message: "The AI provider returned invalid poster ideas." }, 502);
    }
  }

  async generatePoster() {
    const project = await this.findOwned("projects", this.body?.project_id ?? this.params.id);
    const tagline = String(this.body?.tagline ?? "").trim().slice(0, 180);
    const direction = String(this.body?.direction ?? "").trim().slice(0, 2000);
    const prompt = `Create a premium vertical theatrical movie poster in a 2:3 composition.\nMovie title: ${project.title}\nTagline: ${tagline}\nStory: ${project.description ?? ""}\nVisual style: ${project.style_preset ?? ""}\nCreative direction: ${direction}\nMake it cinematic, photorealistic and dramatic. Display the movie title accurately. No invented studio logos, billing blocks, watermarks or unrelated text.`;
    const filename = `poster-project-${project.id}-${randomUUID()}.png`;
    let posterPath;
    try {
      posterPath = await saveGeneratedImage(prompt, filename, "1024x1536");
    } catch (error) {
      return this.abort(error.message.startsWith("Set OPENAI") ? 503 : 502, error.message);
    }
    await this.models.projects.update(project.id, { poster_image: posterPath });
    if (this.wantsHtml) {
      flash(this.context, "Poster generated.");
      return redirect(this.context, `/projects/${project.id}`);
    }
    return this.json({ project_id: project.id, poster_url: `/public/${posterPath}` }, 201);
  }

  async generateCharacterImage() {
    const character = await this.findOwned("characters", this.params.id);
    const prompt = String(character.prompt ?? character.description ?? "").trim();
    if (!prompt) return this.abort(422, "Add a character image prompt before generating a reference image.");
    const filename = `character-${character.id}-${randomUUID()}.png`;
    let imagePath;
    try {
      imagePath = await saveGeneratedImage(
        `Create a photorealistic cinematic character reference image. Character: ${character.name}. Role: ${character.role ?? ""}. ${prompt}. No text, logo or watermark.`,
        filename
      );
    } catch (error) {
      return this.abort(error.message.startsWith("Set OPENAI") ? 503 : 502, error.message);
    }
    await this.models.characters.update(character.id, { image_path: imagePath });
    if (this.wantsHtml) {
      flash(this.context, "Character reference image generated.");
      return redirect(this.context, `/characters/${character.id}`);
    }
    return this.json({ character_id: character.id, image_url: `/public/${imagePath}` }, 201);
  }

  async exportCharacter() {
    const character = await this.findOwned("characters", this.params.id);
    return this.json(this.characterExportData(character));
  }

  characterExportData(character) {
    return Object.fromEntries(
      ["name", "ai_tag", "role", "description", "personality", "dialogue_style", "prompt", "video_prompt"]
        .map((key) => [key, character[key] ?? null])
    );
  }

  async exportCharacters() {
    const rows = await this.db.query(
      "SELECT c.* FROM characters c LEFT JOIN projects p ON p.id = c.project_id WHERE p.user_id = ? OR c.project_id IS NULL ORDER BY c.id",
      [this.context.session.userId]
    );
    return this.json(rows.map((character) => this.characterExportData(character)));
  }

  async prepareSyncFace() {
    const character = await this.findOwned("characters", this.params.id);
    const imagePath = String(character.image_path ?? "");
    if (!/^uploads\/[a-zA-Z0-9._/-]+$/.test(imagePath) || imagePath.split("/").includes("..")) {
      return this.abort(422, "No character image found. Generate or add an image first.");
    }
    const image = path.resolve(process.cwd(), "public", imagePath);
    const uploadsRoot = path.resolve(process.cwd(), "public", "uploads");
    if (!image.startsWith(`${uploadsRoot}${path.sep}`)) return this.abort(422, "Invalid character image path.");
    try {
      const { stat } = await import("node:fs/promises");
      if (!(await stat(image)).isFile()) return this.abort(422, "The character image is unavailable.");
    } catch (error) {
      if (error.code === "ENOENT") return this.abort(422, "The character image is unavailable.");
      throw error;
    }
    const videoPrompt = String(character.video_prompt ?? character.prompt ?? "").trim();
    if (!videoPrompt) return this.abort(422, "Add a video or image prompt before preparing face synchronization.");
    await this.models.characters.update(character.id, { video_prompt: videoPrompt, face_sync_status: "video_ready" });
    flash(this.context, "Character prepared for real-to-video cutscene and trailer generation.");
    return redirect(this.context, `/characters/${character.id}`);
  }

  async importCharacters() {
    const projectId = routeId(this.body?.project_id);
    const project = await this.findOwned("projects", projectId);
    let list = this.body?.characters;
    if (typeof list !== "object" || !Array.isArray(list)) {
      try {
        list = JSON.parse(String(this.body?.characters_json ?? ""));
      } catch {
        return this.abort(422, "Provide valid JSON containing a character array.");
      }
    }
    if (!Array.isArray(list) || list.length === 0 || list.length > 200) return this.abort(422, "Provide between 1 and 200 character records.");
    const imported = [];
    for (const item of list) {
      if (!item || typeof item !== "object" || typeof item.name !== "string" || !item.name.trim()) {
        return this.abort(422, "Every imported character must have a name.");
      }
      imported.push(await this.models.characters.create({
        ...item,
        project_id: project.id
      }));
    }
    return this.json({ imported: imported.length, characters: imported }, 201);
  }

  async transferCharacter() {
    const character = await this.findOwned("characters", this.params.id);
    const projectId = routeId(this.body?.project_id);
    if (!projectId) return this.abort(422, "Choose a valid destination project.");
    await this.findOwned("projects", projectId);
    await this.models.characters.update(character.id, { project_id: projectId });
    return redirect(this.context, `/characters/${character.id}`);
  }

  async assignEditor() {
    const project = await this.findOwned("projects", this.params.id);
    const editorId = routeId(this.body?.editor_id);
    if (!editorId) return this.abort(422, "Choose a valid editor.");
    const editor = await this.models.editors.find(editorId);
    if (!editor) return this.abort(404, "Editor not found.");
    await this.db.query(
      "INSERT INTO editor_project (editor_id, project_id, assigned_task) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE assigned_task = VALUES(assigned_task)",
      [editor.id, project.id, String(this.body?.assigned_task ?? "Production editing").slice(0, 255)]
    );
    return redirect(this.context, `/projects/${project.id}`);
  }

  async downloadVideo() {
    const project = await this.findOwned("projects", this.params.id);
    const source = String(project.final_movie_path ?? "");
    if (!/^uploads\/[a-zA-Z0-9._/-]+$/.test(source) || source.split("/").includes("..")) {
      return this.abort(404, "No rendered movie is available.");
    }
    this.context.response.setHeader("Location", `/public/${source}`);
    return this.text("", 303);
  }

  async parseScript() {
    const script = String(this.body?.script ?? "");
    if (!script.trim()) return this.abort(422, "Script text is required.");
    const result = await this.service("ai").generate(
      `Split this script into numbered production scenes. Return JSON array objects with order_index and script_segment.\n\n${script}`,
      { instructions: "Extract scene boundaries without adding plot details." }
    );
    if (this.wantsHtml) {
      flash(this.context, `Scene breakdown: ${result.text}`);
      return redirect(this.context, "/tools/script");
    }
    return this.json({ scenes: result.text });
  }

  async settings() {
    const variables = ["APP_NAME", "APP_URL", "DB_TYPE", "DB_HOST", "DB_NAME", "AI_ENABLED", "AI_PROVIDER", "SEEDANCE_BASE_URL", "COMFYUI_URL"];
    return this.render("settings", {
      message: takeFlash(this.context),
      navigation,
      fields: variables.map((name) => ({ name, label: name, value: process.env[name] ?? "", type: "text" })),
    });
  }

  async posters() {
    const projects = await this.db.query(
      "SELECT * FROM projects WHERE user_id = ? ORDER BY updated_at DESC LIMIT 200",
      [this.context.session.userId]
    );
    return this.render("posters", {
      message: takeFlash(this.context),
      navigation,
      createHref: "/projects/create",
      projects: projects.map((project) => ({
        ...rowForView(project, "/projects"),
        id: String(project.id),
        posterUrl: mediaUrl(project.poster_image),
        taglineId: `tagline-${project.id}`,
        directionId: `direction-${project.id}`
      })),
      hasRows: projects.length > 0,
    });
  }

  async planning() {
    const projects = await this.db.query(
      "SELECT id, title, description FROM projects WHERE user_id = ? ORDER BY updated_at DESC LIMIT 200",
      [this.context.session.userId]
    );
    if (!projects.length) {
      return this.render("planning", {
        pageType: "index",
        message: takeFlash(this.context),
        navigation,
        createHref: "/projects/create",
        projects: [],
        hasRows: false
      });
    }
    const requestedId = routeId(this.query.project_id);
    const selected = projects.find((project) => Number(project.id) === requestedId) ?? projects[0];
    const [plans] = await this.db.query("SELECT * FROM production_plans WHERE project_id = ? LIMIT 1", [selected.id]);
    const plan = plans ?? {};
    const planFields = [
      ["logline", "Logline", true],
      ["full_story", "Full story script", true],
      ["script_outline", "Script outline / acts", true],
      ["scene_breakdown", "Scene breakdown", true],
      ["schedule_start_date", "Start date", false, "date"],
      ["schedule_end_date", "End date", false, "date"],
      ["shooting_days", "Shooting days", false, "number"],
      ["daily_call_time", "Daily call time", false, "time"],
      ["render_deadline", "Render deadline", false, "date"],
      ["release_target", "Release target", false, "date"],
      ["production_notes", "Production notes", true]
    ].map(([name, label, multiline, type = "text"]) => ({
      name, label, multiline, type, value: String(plan[name] ?? "")
    }));
    return this.render("planning", {
      pageType: "form",
      message: takeFlash(this.context),
      navigation,
      createHref: "/projects/create",
      projectOptions: projects.map((project) => ({
        value: String(project.id),
        label: project.title,
        selected: Number(project.id) === Number(selected.id)
      })),
      fields: planFields,
      selectedProjectId: String(selected.id),
      logline: String(plan.logline ?? selected.description ?? ""),
      projectTitle: selected.title
    });
  }

  async toolPage(tool) {
    const title = { "sync-face": "Face sync preparation", "gen-score": "Score generation", script: "Script breakdown" }[tool];
    if (!title) return this.abort(404, "Tool not found.");
    const rows = tool === "sync-face"
      ? await this.db.query(
        "SELECT c.id, c.name, c.description, c.image_path FROM characters c LEFT JOIN projects p ON p.id = c.project_id WHERE p.user_id = ? OR c.project_id IS NULL ORDER BY c.name LIMIT 200",
        [this.context.session.userId]
      )
      : [];
    return this.render("tools", {
      title,
      pageType: tool,
      message: takeFlash(this.context),
      navigation,
      rows: rows.map((row) => ({
        ...rowForView(row, "/characters"),
        prepareHref: `/characters/${row.id}`
      })),
      hasRows: rows.length > 0,
      scriptText: ""
    });
  }
}

export function registerProductionRoutes(app) {
  app.get("/login", ProductionController.handle("showLogin"));
  app.get("/setup", ProductionController.handle("setup"));
  app.post("/setup", ProductionController.handle("createFirstUser"));
  app.post("/login", ProductionController.handle("login"));
  app.post("/logout", ProductionController.handle("logout"));
  app.get("/", ({ session, response, text }) => {
    response.setHeader("Location", session.userId ? "/dashboard" : "/login");
    return text("", 303);
  });
  app.get("/dashboard", ProductionController.handle("dashboard"));
  app.get("/timeline", ProductionController.handle("recentTimeline"));
  app.get("/videoeditor", ProductionController.handle("recentVideoEditor"));
  app.get("/dashboard/runpod-status", ProductionController.handle("runPodStatus"));
  app.get("/wan-local/status", ProductionController.handle("localWanStatus"));
  app.get("/wan-kaggle/notebook", ProductionController.handle("kaggleNotebook"));
  app.get("/wan-kaggle/connection", ProductionController.handle("kaggleConnection"));
  app.post("/wan-kaggle/connect", ProductionController.handle("connectKaggle"));
  app.post("/wan-kaggle/disconnect", ProductionController.handle("disconnectKaggle"));
  app.post("/scenes/:id/render-kaggle", ProductionController.handle("renderKaggleScene"));
  app.post("/scenes/:id/kaggle-progress", ProductionController.handle("kaggleSceneProgress"));
  app.get("/scenes/:id/kaggle-job", ProductionController.handle("kaggleScene"));
  app.post("/scenes/:id/kaggle-prompt", ProductionController.handle("saveKagglePrompt"));
  app.get("/scenes/:id/kaggle-notebook", ProductionController.handle("kaggleSceneNotebook"));
  app.get("/scenes/:id/kaggle-open", ProductionController.handle("openKaggleNotebook"));
  app.get("/wan-kaggle/import/:token/notebook.ipynb", ProductionController.handle("kaggleNotebookImport"));
  app.get("/characters/export", ProductionController.handle("exportCharacters"));
  app.post("/characters/generate-details", ProductionController.handle("generateCharacterDetails"));
  app.post("/characters/import", ProductionController.handle("importCharacters"));
  app.post("/characters/:id/transfer", ProductionController.handle("transferCharacter"));
  app.post("/characters/:id/generate-image", ProductionController.handle("generateCharacterImage"));
  app.post("/characters/:id/sync-face", ProductionController.handle("prepareSyncFace"));
  app.get("/characters/:id/export", ProductionController.handle("exportCharacter"));
  app.post("/planning/generate-story", ProductionController.handle("generateStory"));
  app.post("/tools/script/parse", ProductionController.handle("parseScript"));
  app.post("/scenes/generate-video", ProductionController.handle("generateSceneVideo"));
  app.post("/scenes/:id/render", ProductionController.handle("renderScene"));
  app.post("/scenes/:id/render-local", ProductionController.handle("renderLocalScene"));
  app.post("/scenes/:id/generate-seedance", ProductionController.handle("generateSeedanceForScene"));
  app.post("/scenes/:id/sync-render", ProductionController.handle("syncSceneRender"));
  app.post("/scenes/:id/attach-video", ProductionController.handle("attachVideo"));
  app.post("/posters/generate-ideas", ProductionController.handle("generatePosterIdeas"));
  app.post("/posters/generate", ProductionController.handle("generatePoster"));
  app.post("/projects/:id/assign-editor", ProductionController.handle("assignEditor"));
  for (const path of Object.keys(resourceDefinitions)) {
    app.get(`/${path}`, (context) => new ProductionController(context).resourceIndex(path));
  }

  for (const path of Object.keys(resourceDefinitions)) {
    app.get(`/${path}/create`, (context) => new ProductionController(context).resourceForm(path));
    app.get(`/${path}/:id/edit`, (context) => {
      const controller = new ProductionController({ ...context, params: { ...context.params, resource: path } });
      return controller.resourceForm(path, context.params.id);
    });
    app.get(`/${path}/:id`, (context) => {
      const controller = new ProductionController({ ...context, params: { ...context.params, resource: path } });
      return controller.resourceShow(path, context.params.id);
    });
    app.post(`/${path}`, (context) => {
      const controller = new ProductionController(context);
      return controller.resourceStore(path);
    });
    app.post(`/${path}/:id`, (context) => {
      const controller = new ProductionController(context);
      return controller.resourceStore(path, context.params.id);
    });
    app.post(`/${path}/:id/delete`, (context) => {
      const controller = new ProductionController(context);
      return controller.resourceDelete(path, context.params.id);
    });
  }

  app.get("/projects/:id/editor", (context) => new ProductionController(context).projectView("editor"));
  app.get("/projects/:id/timeline", (context) => new ProductionController(context).projectView("timeline"));
  app.get("/projects/:id/videoeditor", ProductionController.handle("projectVideoEditor"));
  app.get("/projects/:id/export-xml", ProductionController.handle("exportXml"));
  app.post("/projects/:id/render-batch", ProductionController.handle("renderBatch"));
  app.get("/projects/:id/download-video/:quality", ProductionController.handle("downloadVideo"));
  app.get("/planning", ProductionController.handle("planning"));
  app.post("/planning", (context) => new ProductionController(context).resourceStore("production_plans"));
  app.post("/planning/generate-story", ProductionController.handle("generateStory"));
  app.get("/posters", ProductionController.handle("posters"));
  app.post("/projects/:id/generate-poster", ProductionController.handle("generatePoster"));
  app.get("/settings", ProductionController.handle("settings"));
  for (const tool of ["sync-face", "gen-score", "script"]) {
    app.get(`/tools/${tool}`, (context) => new ProductionController(context).toolPage(tool));
  }
}

export function requireAuthentication(context, next) {
  const path = new URL(context.request.url, "http://localhost").pathname;
  if (["/login", "/setup", "/health"].includes(path)) return next();
  if (/^\/wan-kaggle\/import\/\d{13}-[a-f0-9]{64}\/notebook\.ipynb$/.test(path)) return next();
  if (!context.session?.userId) {
    context.response.setHeader("Location", "/login");
    return context.text("", 303);
  }
  return next();
}

export { resourceDefinitions };
