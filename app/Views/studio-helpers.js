export function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, character => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[character]);
}

export function mediaUrl(value) {
  if (!value) return "";
  const relative = String(value).replace(/^\/+/, "");
  if (relative.split("/").some(part => part === "..") || !/^[a-zA-Z0-9_./-]+$/.test(relative)) return "";
  return `/public/${relative}`;
}

export function localWanControls(scene, csrfToken) {
  const id = escapeHtml(scene.id);
  const busy = ["Rendering", "Processing"].includes(scene.status);
  const localJob = String(scene.generation_job_id ?? "").startsWith("local:");
  return `${kaggleControls(scene)}<section class="p-2 mt-2 border border-secondary" data-local-wan>
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

export function kaggleControls(scene) {
  return `<section class="p-2 mt-2 border border-info" data-kaggle-wan>
    <strong class="text-info small">WAN 2.1 — KAGGLE GPU</strong>
    <p class="text-secondary small">Generate online using your Kaggle GPU quota. Save your scene prompt, download the notebook, then import it in Kaggle with Internet and GPU enabled.</p>
    <div class="d-grid gap-2">
      <a class="nle-btn btn btn-sm btn-outline-info" href="/wan-kaggle/notebook" download>Download Kaggle notebook</a>
      <a class="nle-btn btn btn-sm btn-outline-secondary" href="/scenes/${escapeHtml(scene.id)}/kaggle-job" download>Export this scene for Kaggle</a>
      <a class="nle-btn btn btn-sm btn-outline-secondary" href="https://www.kaggle.com/code" target="_blank" rel="noopener noreferrer">Open Kaggle notebooks</a>
    </div>
    <p class="text-secondary small mt-2">Download the generated MP4, then run <code>npm run kaggle:import -- --scene ${escapeHtml(scene.id)} --file "path/to/video.mp4"</code> in your editor project.</p>
  </section>`;
}
