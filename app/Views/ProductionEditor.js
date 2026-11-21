// VOID SHADOW scene editor: scene navigator, tabbed scene cards and a live render queue.
import { escapeHtml, mediaUrl, kaggleControls } from "./studio-helpers.js";

const BUSY = ["rendering", "processing", "queued"];

function statusKey(scene) {
  const status = String(scene?.status ?? "").toLowerCase();
  if (BUSY.includes(status)) return "busy";
  if (status === "failed") return "failed";
  if (status === "ready" || status === "completed" || scene?.video_path) return "ready";
  return "draft";
}

const pad = (value) => escapeHtml(String(value ?? 0).padStart(3, "0"));
const csrfField = (token) => `<input type="hidden" name="_csrf" value="${escapeHtml(token)}">`;

function sceneCard(scene, { project, csrfToken, comfyConfigured }) {
  const id = escapeHtml(scene.id);
  const key = statusKey(scene);
  const busy = key === "busy";
  const videoEditor = escapeHtml(`/projects/${project.id}/videoeditor`) + "?active_scene=" + id;
  const comfyJob = scene.generation_job_id && !String(scene.generation_job_id).startsWith("kaggle:");
  const preview = scene.video_path
    ? `<video src="${escapeHtml(mediaUrl(scene.video_path))}" controls preload="metadata"></video>`
    : `<div class="ed-preview-empty">
        <i class="bi ${busy ? "bi-hourglass-split" : "bi-play-circle"}"></i>
        <span>${busy ? "Rendering…" : "Awaiting render"}</span>
      </div>`;

  return `
  <article class="ed-scene" id="scene-${id}" data-scene-id="${id}" data-status="${key}"${busy ? ` data-progress-url="/scenes/${id}/progress"` : ""}>
    <header class="ed-scene-head">
      <button type="button" class="ed-icon-btn ed-collapse" aria-expanded="true" aria-controls="scene-body-${id}" aria-label="Collapse scene">
        <i class="bi bi-chevron-down"></i>
      </button>
      <span class="ed-scene-num">SCENE #${pad(scene.order_index)}</span>
      <span class="ed-pill" data-status="${key}">${escapeHtml(scene.status ?? "Draft")}</span>
      <span class="ed-dirty" hidden>Unsaved</span>
      <div class="ed-scene-tools">
        <a class="ed-icon-btn" href="${videoEditor}" title="Open in video editor"><i class="bi bi-film"></i></a>
        <a class="ed-icon-btn" href="${escapeHtml(`/scenes/${scene.id}`)}" title="Scene details"><i class="bi bi-sliders"></i></a>
        <div class="dropdown">
          <button class="ed-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Scene actions"><i class="bi bi-three-dots-vertical"></i></button>
          <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end ed-menu">
            <li><a class="dropdown-item" href="${videoEditor}"><i class="bi bi-film"></i> Open video editor</a></li>
            <li><a class="dropdown-item" href="${escapeHtml(`/scenes/${scene.id}`)}"><i class="bi bi-sliders"></i> Reconfigure params</a></li>
            ${comfyConfigured ? `<li>
              <form action="${escapeHtml(`/scenes/${scene.id}/render`)}" method="POST">
                ${csrfField(csrfToken)}
                <button type="submit" class="dropdown-item text-warning"><i class="bi bi-cpu-fill"></i> Regenerate seed</button>
              </form>
            </li>` : ""}
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="${escapeHtml(`/scenes/${scene.id}/delete`)}" method="POST" data-studio-submit="1">
                ${csrfField(csrfToken)}
                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash3"></i> Delete segment</button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <div class="ed-scene-body" id="scene-body-${id}">
      <div class="ed-preview-col">
        <div class="ed-preview">${preview}</div>
        <a href="${videoEditor}" class="ed-btn ed-btn-ghost ed-btn-block"><i class="bi bi-film"></i> Advanced video edit</a>
      </div>

      <div class="ed-work-col">
        <div class="ed-tabs" role="tablist" aria-label="Scene ${id} panels">
          <button type="button" role="tab" class="ed-tab" id="tab-script-${id}" aria-controls="panel-script-${id}" aria-selected="true">
            <i class="bi bi-pencil-square"></i> Script
          </button>
          <button type="button" role="tab" class="ed-tab" id="tab-kaggle-${id}" aria-controls="panel-kaggle-${id}" aria-selected="false" tabindex="-1">
            <i class="bi bi-gpu-card"></i> Generate · Kaggle
          </button>
          ${comfyConfigured ? `<button type="button" role="tab" class="ed-tab" id="tab-comfy-${id}" aria-controls="panel-comfy-${id}" aria-selected="false" tabindex="-1">
            <i class="bi bi-cpu"></i> ComfyUI
          </button>` : ""}
        </div>

        <section class="ed-panel" role="tabpanel" id="panel-script-${id}" aria-labelledby="tab-script-${id}">
          <form action="${escapeHtml(`/scenes/${scene.id}`)}" method="POST" class="ed-script-form" data-ed-save>
            ${csrfField(csrfToken)}
            <input type="hidden" name="project_id" value="${escapeHtml(project.id)}">
            <input type="hidden" name="order_index" value="${escapeHtml(scene.order_index)}">
            <input type="hidden" name="status" value="${escapeHtml(scene.status)}">
            <input type="hidden" name="return_to_editor" value="1">
            <label class="ed-label" for="script-${id}">Script / AI prompt</label>
            <textarea id="script-${id}" name="script_segment" class="ed-textarea" rows="5" placeholder="Describe the movement and action here...">${escapeHtml(scene.script_segment)}</textarea>
            <div class="ed-form-foot">
              <span class="ed-hint"><span data-ed-count>0</span> chars · <kbd>Ctrl</kbd>+<kbd>S</kbd> to save</span>
              <span class="ed-save-state" role="status" aria-live="polite"></span>
              <button type="submit" class="ed-btn ed-btn-light"><i class="bi bi-check2"></i> <span>Save</span></button>
            </div>
          </form>
        </section>

        <section class="ed-panel" role="tabpanel" id="panel-kaggle-${id}" aria-labelledby="tab-kaggle-${id}" hidden>
          ${kaggleControls(scene, csrfToken)}
        </section>

        ${comfyConfigured ? `<section class="ed-panel" role="tabpanel" id="panel-comfy-${id}" aria-labelledby="tab-comfy-${id}" hidden>
          <p class="ed-hint">Save the script first, then send this scene to the configured ComfyUI worker.</p>
          <form action="${escapeHtml(`/scenes/${scene.id}/render`)}" method="POST" data-ed-busy="Queuing…">
            ${csrfField(csrfToken)}
            <button type="submit" class="ed-btn ed-btn-accent ed-btn-block" ${busy ? "disabled" : ""}><i class="bi bi-cpu-fill"></i> <span>Render with configured ComfyUI</span></button>
          </form>
          ${comfyJob ? `<form action="${escapeHtml(`/scenes/${scene.id}/sync-render`)}" method="POST" data-ed-busy="Checking…">
            ${csrfField(csrfToken)}
            <button type="submit" class="ed-btn ed-btn-warn ed-btn-block"><i class="bi bi-arrow-repeat"></i> <span>Check render status</span></button>
          </form>` : ""}
        </section>` : ""}

        ${scene.generation_error ? `<div class="ed-error" role="alert"><i class="bi bi-exclamation-triangle"></i> <span>${escapeHtml(scene.generation_error)}</span></div>` : ""}
      </div>
    </div>
  </article>`;
}

export function renderProductionEditor({ project, scenes, characters = [], currentScene = null, csrfToken = "", comfyConfigured = false }) {
  const counts = { ready: 0, busy: 0, failed: 0, draft: 0 };
  for (const scene of scenes) counts[statusKey(scene)] += 1;
  const percent = scenes.length ? Math.round((counts.ready / scenes.length) * 100) : 0;
  const projectHref = escapeHtml(`/projects/${project.id}`);

  const navigator = scenes.map((scene) => {
    const key = statusKey(scene);
    const label = String(scene.script_segment ?? "").trim().slice(0, 60) || "Untitled scene";
    return `<li><a href="#scene-${escapeHtml(scene.id)}" class="ed-nav-item" data-status="${key}" data-scene-link="${escapeHtml(scene.id)}">
      <span class="ed-dot" data-status="${key}"></span>
      <span class="ed-nav-num">${pad(scene.order_index)}</span>
      <span class="ed-nav-text">${escapeHtml(label)}</span>
    </a></li>`;
  }).join("");

  const cast = characters.length
    ? characters.map((char) => `<div class="ed-cast">
        <span class="ed-avatar">${escapeHtml(String(char.name ?? "?").trim().charAt(0).toUpperCase())}</span>
        <span class="ed-cast-name">${escapeHtml(char.name)}</span>
        <code>${escapeHtml(char.ai_tag)}</code>
      </div>`).join("")
    : `<div class="ed-empty-box">
        <p>No cast assigned to this project.</p>
        <a href="${escapeHtml(`/characters/create?project_id=${project.id}`)}" class="ed-btn ed-btn-ghost ed-btn-block">Add character</a>
      </div>`;

  const queue = scenes.filter((scene) => statusKey(scene) === "busy").map((scene) => `
    <li class="ed-job" data-queue-scene="${escapeHtml(scene.id)}">
      <div class="ed-job-head">
        <a href="#scene-${escapeHtml(scene.id)}" data-scene-link="${escapeHtml(scene.id)}">Scene #${pad(scene.order_index)}</a>
        <span class="ed-job-provider">${String(scene.generation_job_id ?? "").startsWith("kaggle:") ? "Kaggle" : "Worker"}</span>
      </div>
      <div class="ed-job-track"><div class="ed-job-fill"></div></div>
      <p class="ed-job-msg">Waiting for status…</p>
    </li>`).join("");

  const body = scenes.length
    ? scenes.map((scene) => sceneCard(scene, { project, csrfToken, comfyConfigured })).join("")
    : `<div class="ed-empty-timeline">
        <i class="bi bi-film"></i>
        <h2>TIMELINE IS EMPTY</h2>
        <p>Create the first scene to begin building this movie.</p>
        <a href="${escapeHtml(`/scenes/create?project_id=${project.id}`)}" class="ed-btn ed-btn-accent">NEW CLIP +</a>
      </div>`;

  return `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>${escapeHtml(project.title)} — Production Editor</title>
<link rel="stylesheet" href="/public/studio/bootstrap.min.css">
<link rel="stylesheet" href="/public/studio/bootstrap-icons.css">
<link rel="stylesheet" href="/public/studio/fonts.css">
<link rel="stylesheet" href="/public/studio/editor.css?v=2">
<link rel="icon" href="data:,">
<script src="/public/studio/kaggle.js" defer></script>
<script src="/public/studio/editor.js?v=2" defer></script>
</head><body class="ed-body">
<header class="ed-header">
  <div class="ed-header-left">
    <a href="${projectHref}" class="ed-icon-btn" title="Back to production overview" aria-label="Back to production overview"><i class="bi bi-arrow-left"></i></a>
    <div>
      <h1 class="ed-brand">VOID SHADOW EDITOR <span>v1.0</span></h1>
      <span class="ed-live"><span class="ed-live-dot"></span>LIVE: ${escapeHtml(project.title)}</span>
    </div>
  </div>
  <div class="ed-header-progress" title="${counts.ready} of ${scenes.length} scenes rendered">
    <div class="ed-header-progress-label"><span>Rendered</span><strong>${counts.ready} / ${scenes.length}</strong></div>
    <div class="ed-progress-track"><div class="ed-progress-fill" data-progress="${percent}"></div></div>
  </div>
  <div class="ed-header-actions">
    <a href="${escapeHtml(`/scenes/create?project_id=${project.id}`)}" class="ed-btn ed-btn-ghost"><i class="bi bi-plus-lg"></i> New clip</a>
    <a href="${escapeHtml(`/projects/${project.id}/export-xml`)}" class="ed-btn ed-btn-ghost"><i class="bi bi-filetype-xml"></i> Export XML</a>
    <form action="${escapeHtml(`/projects/${project.id}/render-batch`)}" method="POST" class="m-0" data-studio-submit="0">
      ${csrfField(csrfToken)}
      <button id="render-batch" type="submit" class="ed-btn ed-btn-light" title="${comfyConfigured ? "Queue every scene on ComfyUI" : "Connect a ComfyUI worker in Settings to batch render"}" ${!scenes.length || !comfyConfigured ? "disabled" : ""}>BATCH RENDER (ALL SCENES)</button>
    </form>
  </div>
</header>

<div class="ed-shell">
  <aside class="ed-side ed-side-left">
    <section class="ed-card">
      <div class="ed-card-head"><h2>Scenes</h2><span class="ed-count">${scenes.length}</span></div>
      ${scenes.length ? `<label class="ed-search"><i class="bi bi-search"></i><input type="search" placeholder="Find scene…" aria-label="Find scene" data-ed-search></label>
      <ul class="ed-nav">${navigator}</ul>
      <p class="ed-hint ed-nav-empty" hidden>No matching scenes.</p>` : `<p class="ed-hint">No scenes yet.</p>`}
    </section>
    <section class="ed-card">
      <div class="ed-card-head"><h2>Cast &amp; seeds</h2><span class="ed-count">${characters.length}</span></div>
      ${cast}
    </section>
    <section class="ed-card">
      <div class="ed-card-head"><h2>Style DNA</h2></div>
      <div class="ed-dna">
        <span><i class="bi bi-palette"></i> ${escapeHtml(project.style_preset)}</span>
        <span><i class="bi bi-aspect-ratio"></i> ${escapeHtml(project.aspect_ratio)}</span>
        ${project.resolution ? `<span><i class="bi bi-badge-hd"></i> ${escapeHtml(project.resolution)}</span>` : ""}
      </div>
    </section>
  </aside>

  <main class="ed-main">
    <div class="ed-main-head">
      <h2>Production timeline <span>(${escapeHtml(scenes.length)} Segments)</span></h2>
      ${scenes.length ? `<div class="ed-main-tools">
        <div class="ed-filters" role="group" aria-label="Filter scenes">
          <button type="button" class="ed-filter" data-ed-filter="all" aria-pressed="true">All <b>${scenes.length}</b></button>
          <button type="button" class="ed-filter" data-ed-filter="ready" aria-pressed="false">Ready <b>${counts.ready}</b></button>
          <button type="button" class="ed-filter" data-ed-filter="busy" aria-pressed="false">Rendering <b>${counts.busy}</b></button>
          <button type="button" class="ed-filter" data-ed-filter="draft" aria-pressed="false">Draft <b>${counts.draft}</b></button>
          <button type="button" class="ed-filter" data-ed-filter="failed" aria-pressed="false">Failed <b>${counts.failed}</b></button>
        </div>
        <button type="button" class="ed-btn ed-btn-ghost" data-ed-collapse-all aria-pressed="false"><i class="bi bi-arrows-collapse"></i> <span>Collapse all</span></button>
      </div>` : ""}
    </div>
    ${body}
  </main>

  <aside class="ed-side ed-side-right">
    <section class="ed-card">
      <div class="ed-card-head"><h2>System queue</h2><span class="ed-count" data-queue-count>${counts.busy}</span></div>
      <ul class="ed-queue" id="render-queue">${queue}</ul>
      <div class="ed-queue-empty"${counts.busy ? " hidden" : ""}>
        <i class="bi bi-check2-circle"></i>
        <p>No active renders. Render jobs appear on each scene.</p>
      </div>
    </section>
    <section class="ed-card">
      <div class="ed-card-head"><h2>Master audio layer</h2></div>
      <div class="ed-audio">
        <i class="bi bi-soundwave"></i>
        <div>
          <strong>No master audio attached</strong>
          <span>Add a soundtrack from the <a href="${projectHref}">project overview</a>.</span>
        </div>
      </div>
    </section>
    <section class="ed-card ed-shortcuts">
      <div class="ed-card-head"><h2>Shortcuts</h2></div>
      <dl>
        <dt><kbd>Ctrl</kbd>+<kbd>S</kbd></dt><dd>Save focused scene</dd>
        <dt><kbd>J</kbd> / <kbd>K</kbd></dt><dd>Next / previous scene</dd>
        <dt><kbd>/</kbd></dt><dd>Find scene</dd>
      </dl>
    </section>
  </aside>
</div>
<div class="ed-toast" role="status" aria-live="polite" hidden></div>
<script src="/public/studio/bootstrap.bundle.min.js"></script></body></html>`;
}
