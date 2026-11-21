import { existsSync } from "node:fs";
import path from "node:path";

export function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, character => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[character]);
}

export function mediaUrl(value, publicRoot = path.resolve(process.cwd(), "public")) {
  if (!value) return "";
  const relative = String(value).replace(/^\/+/, "").replace(/^public\//, "");
  if (relative.split("/").some(part => part === "..") || !/^[a-zA-Z0-9_./-]+$/.test(relative)) return "";
  // Laravel stores media relative to storage/app/public; new media uses uploads/.
  const stored = !existsSync(path.join(publicRoot, relative))
    && existsSync(path.join(publicRoot, "storage", relative));
  return `/public/${stored ? "storage/" : ""}${relative}`;
}

export function aiEngineControls(scene, csrfToken, comfyConfigured) {
  const id = escapeHtml(scene.id);
  const busy = ["Rendering", "Processing"].includes(scene.status);
  return `<div class="ai-engine">
    ${kaggleControls(scene, csrfToken, { inspector: true })}
    <details class="ai-engine-options">
      <summary>ComfyUI</summary>
      ${comfyConfigured ? `<p>Save the prompt before starting a ComfyUI render.</p>
      <form method="post" action="/scenes/${id}/render">
        <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
        <button type="submit" class="nle-btn w-100" ${busy ? "disabled" : ""}>Render with ComfyUI</button>
      </form>
      ${scene.generation_job_id && !/^(kaggle|local):/.test(scene.generation_job_id) ? `<form method="post" action="/scenes/${id}/sync-render">
        <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
        <button type="submit" class="nle-btn w-100">Check ComfyUI render</button>
      </form>` : ""}` : `<p>No ComfyUI worker is connected. Kaggle generation is available through your Kaggle account.</p>
      <a class="nle-btn" href="/settings#comfyui">Connect a ComfyUI worker</a>`}
    </details>
  </div>`;
}

export function kaggleControls(scene, csrfToken, { inspector = false } = {}) {
  const id = escapeHtml(scene.id);
  const busy = ["Rendering", "Processing"].includes(scene.status);
  const kaggleJob = String(scene.generation_job_id ?? "").startsWith("kaggle:");
  if (inspector) {
    return `<section class="ai-engine-main" data-kaggle-wan data-scene-id="${id}" data-job-active="${busy && kaggleJob}" data-other-job-active="${busy && !kaggleJob}">
      <div class="ai-engine-heading">
        <h2>Kaggle GPU</h2>
        <span>1080p upscaled</span>
      </div>
      <form method="post" action="/scenes/${id}/kaggle-prompt" data-ai-engine-prompt>
        <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
        <label for="aiPromptTextarea">Video prompt</label>
        <textarea id="aiPromptTextarea" name="prompt" rows="5" maxlength="8000" required class="nle-textarea w-100" placeholder="Describe the action, camera movement, and lighting.">${escapeHtml(scene.prompt || scene.script_segment || "")}</textarea>
        <details class="ai-engine-options">
          <summary>Opening shot and character reference</summary>
          <p>Start separate lines with <strong>Opening shot:</strong> and <strong>Main shot:</strong> to create a cut. Add <strong>Reference character:</strong> and the name of an assigned character to use their portrait in the main shot.</p>
        </details>
        <div class="ai-engine-prompt-actions">
          <button type="submit" class="nle-btn">Save prompt</button>
          <button type="button" class="nle-btn" data-studio-click="100">Enhance prompt</button>
        </div>
      </form>
      <button type="button" class="nle-btn nle-btn-primary w-100" data-kaggle-generate disabled>Generate on Kaggle</button>
      <p role="status" data-kaggle-progress-message>${busy && !kaggleJob ? "Another worker is rendering this scene." : ""}</p>
      <div class="ai-engine-job-actions">
        <button type="button" class="nle-btn" data-kaggle-progress ${kaggleJob ? "" : "hidden"}>Check progress</button>
        <a class="nle-btn" data-kaggle-notebook-link hidden target="_blank" rel="noopener noreferrer">Open Kaggle run</a>
        ${busy && !kaggleJob ? `<a class="nle-btn" href="/scenes/${id}">View scene status</a>` : ""}
      </div>
      ${kaggleConnectionControls(csrfToken, "Kaggle account")}
      <details class="ai-engine-options">
        <summary>Manual notebook</summary>
        <p>Save the prompt, open the notebook, then use Kaggle’s Save Version with Run All. Link the saved run from the scene page.</p>
        <div class="ai-engine-job-actions">
          <a class="nle-btn" href="/scenes/${id}/kaggle-open" target="_blank" rel="noopener noreferrer">Open notebook</a>
          <a class="nle-btn" href="/scenes/${id}/kaggle-notebook" download>Download notebook</a>
          <a class="nle-btn" href="/scenes/${id}/kaggle-job" download>Export scene JSON</a>
          <a class="nle-btn" href="/scenes/${id}">Link saved run</a>
        </div>
      </details>
    </section>`;
  }
  return `<section class="p-2 mt-2 border border-info" data-kaggle-wan data-scene-id="${id}" data-job-active="${busy && kaggleJob}">
    <strong class="text-info small">COGVIDEOX-5B — KAGGLE GPU</strong>
    <p class="text-info small"><strong>Full HD output: 1920 × 1080</strong></p>
    <p class="text-secondary small">CogVideoX-5B generates 720×480 at 8 fps (about 6 seconds), then upscales to 1080p. This increases output size; it does not add native 1080p detail.</p>
    ${kaggleConnectionControls(csrfToken, "Connect Kaggle account")}
    <form method="post" action="/scenes/${escapeHtml(scene.id)}/kaggle-prompt" class="mb-3">
      <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
      <label class="small d-block mb-1" for="kaggle-prompt-${escapeHtml(scene.id)}">Video prompt</label>
      <textarea id="kaggle-prompt-${escapeHtml(scene.id)}" name="prompt" rows="6" maxlength="8000" required class="form-control form-control-sm bg-dark text-light" placeholder="A woman walks along a railway platform at sunrise. Slow camera dolly forward, soft golden light, realistic movement, cinematic live-action.">${escapeHtml(scene.prompt || scene.script_segment || "")}</textarea>
      <p class="text-secondary small mt-1">Describe the subject, action, location, camera movement, and lighting. Project style and assigned cast are added automatically.</p>
      <label class="small d-block">Output resolution <input class="form-control form-control-sm bg-dark text-light" value="1920 × 1080 (Full HD, upscaled)" readonly aria-label="Output resolution"></label>
      <button type="submit" class="nle-btn btn btn-sm btn-info w-100 mt-2">Save video prompt</button>
    </form>
    <button type="button" class="nle-btn btn btn-info w-100 mb-2" data-kaggle-generate disabled>Generate this scene · Kaggle GPU</button>
    <button type="button" class="nle-btn w-100 mb-2" data-kaggle-progress>Check progress / load finished video</button>
    <p class="small" role="status" aria-live="polite" data-kaggle-progress-message>Connect Kaggle, write your prompt, then click Generate this scene. The finished video is attached to this scene automatically.</p>
    <a class="nle-btn mb-2" data-kaggle-notebook-link hidden target="_blank" rel="noopener noreferrer">View Kaggle job</a>
    <p class="small text-secondary">Uses your Kaggle GPU quota. Your account must have GPU access. You can leave this page while the job runs.</p>
    <details><summary class="nle-btn mb-2">Manual notebook options</summary>
    <ol class="small ps-3"><li>Save your prompt above.</li><li>Click <strong>Open scene in Kaggle</strong>.</li><li>In Kaggle, choose <strong>Run All</strong> and wait for the output.</li></ol>
    <p class="text-secondary small">The private notebook import link expires after 15 minutes. Keep the imported Kaggle notebook private.</p>
    <div class="d-grid gap-2">
      <a class="nle-btn btn btn-sm btn-info" href="/scenes/${escapeHtml(scene.id)}/kaggle-open" target="_blank" rel="noopener noreferrer">Open scene in Kaggle · 1080p</a>
      <a class="nle-btn btn btn-sm btn-outline-info" href="/scenes/${escapeHtml(scene.id)}/kaggle-notebook" download>Download notebook instead</a>
      <a class="nle-btn btn btn-sm btn-outline-secondary" href="/scenes/${escapeHtml(scene.id)}/kaggle-job" download>Export this scene for Kaggle</a>
      <a class="nle-btn btn btn-sm btn-outline-secondary" href="https://www.kaggle.com/code" target="_blank" rel="noopener noreferrer">Open Kaggle notebooks</a>
    </div>
    </details>
  </section>`;
}

function kaggleConnectionControls(csrfToken, summary) {
  return `<details class="ai-engine-options" data-kaggle-connect-panel>
    <summary>${summary}</summary>
    <p class="small" role="status" data-kaggle-connection>Checking Kaggle connection…</p>
    <a class="nle-btn" href="/settings#kaggle">Manage Kaggle in Settings</a>
    <form method="post" action="/wan-kaggle/connect" data-kaggle-connect>
      <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
      <p class="small">Create a token in <a href="https://www.kaggle.com/settings/api" target="_blank" rel="noopener noreferrer">Kaggle API settings</a>.</p>
      <label class="small d-block mb-2">Kaggle username<input name="username" required autocomplete="username" class="nle-input w-100"></label>
      <label class="small d-block mb-2">Credential type<select name="kind" class="nle-select w-100"><option value="auto">Detect automatically</option><option value="token">API token</option><option value="legacy">Legacy API key (kaggle.json)</option></select></label>
      <label class="small d-block mb-2">API token or key<input type="password" name="secret" required autocomplete="new-password" class="nle-input w-100"></label>
      <button type="submit" class="nle-btn w-100">Connect Kaggle</button>
    </form>
    <button type="button" class="nle-btn" data-kaggle-disconnect hidden>Disconnect Kaggle</button>
  </details>`;
}
