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

export function localWanControls(scene, csrfToken) {
  const id = escapeHtml(scene.id);
  const busy = ["Rendering", "Processing"].includes(scene.status);
  const localJob = String(scene.generation_job_id ?? "").startsWith("local:");
  return `${kaggleControls(scene, csrfToken)}<section class="p-2 mt-2 border border-secondary" data-local-wan>
    <strong class="text-info small">WAN 2.1 — THIS COMPUTER</strong>
    <p class="text-secondary small mb-2" data-local-wan-status role="status">Checking local worker…</p>
    <p class="text-secondary small">GTX 970 / 4 GB: experimental CPU mode. Save your scene prompt first. First-time generation may be very slow.</p>
    <button type="button" class="nle-btn btn btn-sm btn-outline-secondary" data-local-wan-check>Check local worker</button>
    <form method="post" action="/scenes/${id}/render-local" class="mt-2">
      <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
      <input type="hidden" name="return_to_videoeditor" value="1">
      <button type="submit" class="nle-btn btn btn-sm btn-outline-info w-100" data-local-wan-render ${busy ? 'data-busy="true"' : ''} disabled>Render with local Wan 2.1</button>
    </form>
    ${localJob ? `<form method="post" action="/scenes/${id}/sync-render" class="mt-2"><input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}"><button class="nle-btn btn btn-sm btn-outline-warning w-100" type="submit">Check local render / import video</button></form>` : ''}
    ${scene.generation_error ? `<p class="text-danger small">${escapeHtml(scene.generation_error)}</p>` : ''}
  </section>`;
}

export function kaggleControls(scene, csrfToken) {
  return `<section class="p-2 mt-2 border border-info" data-kaggle-wan>
    <strong class="text-info small">WAN 2.1 — KAGGLE GPU</strong>
    <p class="text-info small"><strong>Full HD output: 1920 × 1080</strong></p>
    <p class="text-secondary small">Wan generates at 480p, then upscales to 1080p. This increases output size; it does not add native 1080p detail.</p>
    <form method="post" action="/scenes/${escapeHtml(scene.id)}/kaggle-prompt" class="mb-3">
      <input type="hidden" name="_csrf" value="${escapeHtml(csrfToken)}">
      <label class="small d-block mb-1" for="kaggle-prompt-${escapeHtml(scene.id)}">Video prompt</label>
      <textarea id="kaggle-prompt-${escapeHtml(scene.id)}" name="prompt" rows="6" maxlength="8000" required class="form-control form-control-sm bg-dark text-light" placeholder="A woman walks along a railway platform at sunrise. Slow camera dolly forward, soft golden light, realistic movement, cinematic live-action.">${escapeHtml(scene.prompt || scene.script_segment || "")}</textarea>
      <p class="text-secondary small mt-1">Describe the subject, action, location, camera movement, and lighting. Project style and assigned cast are added automatically.</p>
      <label class="small d-block">Output resolution <input class="form-control form-control-sm bg-dark text-light" value="1920 × 1080 (Full HD, upscaled)" readonly aria-label="Output resolution"></label>
      <button type="submit" class="nle-btn btn btn-sm btn-info w-100 mt-2">Save video prompt</button>
    </form>
    <ol class="small ps-3"><li>Save your prompt above.</li><li>Click <strong>Open scene in Kaggle</strong>. Your prompt and 1080p settings are included.</li><li>Sign in to Kaggle, enable GPU and Internet, then run the cells.</li><li>Download the final <strong>-1080p.mp4</strong> file.</li></ol>
    <p class="text-secondary small">The private notebook import link expires after 15 minutes. Keep the imported Kaggle notebook private.</p>
    <div class="d-grid gap-2">
      <a class="nle-btn btn btn-sm btn-info" href="/scenes/${escapeHtml(scene.id)}/kaggle-open" target="_blank" rel="noopener noreferrer">Open scene in Kaggle · 1080p</a>
      <a class="nle-btn btn btn-sm btn-outline-info" href="/scenes/${escapeHtml(scene.id)}/kaggle-notebook" download>Download notebook instead</a>
      <a class="nle-btn btn btn-sm btn-outline-secondary" href="/scenes/${escapeHtml(scene.id)}/kaggle-job" download>Export this scene for Kaggle</a>
      <a class="nle-btn btn btn-sm btn-outline-secondary" href="https://www.kaggle.com/code" target="_blank" rel="noopener noreferrer">Open Kaggle notebooks</a>
    </div>
    <p class="text-secondary small mt-2">Download the generated MP4, then run <code>npm run kaggle:import -- --scene ${escapeHtml(scene.id)} --file "path/to/video.mp4"</code> in your editor project.</p>
  </section>`;
}
