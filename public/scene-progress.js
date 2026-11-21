// Live, interactive progress bar for scenes with a running generation job.
// Providers do not report a true percentage, so the bar is estimated from the
// elapsed time against each provider's typical duration, and snaps to stage
// milestones (queued / generating / importing / ready) from the server.
(() => {
  const panel = document.querySelector(".render-progress[data-progress-url]");
  if (!panel) return;

  const $ = (selector) => panel.querySelector(selector);
  const url = panel.dataset.progressUrl;
  const fill = $(".render-progress-fill");
  const track = $(".render-progress-track");
  const percentLabel = $(".render-progress-percent");
  const title = $(".render-progress-title");
  const elapsedLabel = $(".render-progress-elapsed");
  const etaLabel = $(".render-progress-eta");
  const nextLabel = $(".render-progress-next");
  const messageLabel = $(".render-progress-message");
  const checkButton = $(".render-progress-check");
  const toggleButton = $(".render-progress-toggle");
  const notebookLink = $(".render-progress-notebook");
  const steps = [...panel.querySelectorAll("[data-step]")];
  const order = ["queued", "running", "downloading", "ready"];
  const providerNames = { kaggle: "Kaggle (CogVideoX)", local: "Local Wan 2.1", comfy: "ComfyUI", none: "render worker" };
  const POLL_MS = 8000;

  let state = null, clockOffset = 0, paused = false, timer = null, inFlight = false, shown = 0, done = false;

  const format = (seconds) => {
    seconds = Math.max(0, Math.round(seconds));
    const h = Math.floor(seconds / 3600), m = Math.floor((seconds % 3600) / 60), s = seconds % 60;
    return h ? `${h}:${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}` : `${m}:${String(s).padStart(2, "0")}`;
  };

  const elapsed = () => {
    if (!state?.started_at) return 0;
    return (Date.now() + clockOffset - Date.parse(state.started_at)) / 1000;
  };

  const stageOf = (data) => {
    if (data.ready) return "ready";
    const remote = String(data.remote_status || "").toLowerCase();
    if (remote === "downloading" || remote === "complete" || remote === "completed") return "downloading";
    if (remote === "queued" || remote === "pending") return "queued";
    if (remote === "running") return "running";
    return elapsed() < 45 ? "queued" : "running";
  };

  // Ease toward 95% over the provider ETA, then creep slowly so it never stalls visibly.
  const estimate = () => {
    if (!state) return 0;
    if (state.ready) return 100;
    const eta = state.eta_seconds || 300, t = elapsed();
    let value = t <= eta ? 90 * (t / eta) : 90 + 8 * (1 - Math.exp(-(t - eta) / eta));
    const stage = stageOf(state);
    if (stage === "queued") value = Math.min(value, 8);
    if (stage === "running") value = Math.max(value, 10);
    if (stage === "downloading") value = Math.max(value, 92);
    return Math.min(99, Math.max(2, value));
  };

  const paint = () => {
    if (!state) return;
    const target = estimate();
    shown = state.failed ? shown : Math.max(shown, target); // never move backwards
    const pct = Math.round(shown);
    fill.style.width = `${shown}%`;
    percentLabel.textContent = state.failed ? "Failed" : `${pct}%`;
    track.setAttribute("aria-valuenow", String(pct));
    const t = elapsed();
    elapsedLabel.textContent = `Elapsed ${format(t)}`;
    if (state.ready) etaLabel.textContent = "Done";
    else if (state.failed) etaLabel.textContent = "";
    else {
      const left = (state.eta_seconds || 300) - t;
      etaLabel.textContent = left > 0 ? `≈ ${format(left)} remaining` : "Taking longer than usual — still working";
    }
    const stage = state.failed ? null : stageOf(state);
    const current = order.indexOf(stage);
    steps.forEach((step, index) => {
      step.classList.toggle("done", current > index || stage === "ready");
      step.classList.toggle("active", current === index && stage !== "ready");
    });
  };

  const apply = (data) => {
    state = data;
    if (data.server_time) clockOffset = Date.parse(data.server_time) - Date.now();
    panel.classList.toggle("is-failed", Boolean(data.failed));
    panel.classList.toggle("is-ready", Boolean(data.ready));
    title.textContent = data.ready ? "Scene ready" : data.failed ? "Generation failed" : `Generating with ${providerNames[data.provider] || "render worker"}`;
    messageLabel.textContent = data.message
      || (data.ready ? "Video imported. Reloading…"
        : "Rendering in progress. You can leave this page — the job keeps running.");
    if (data.notebook_url) { notebookLink.href = data.notebook_url; notebookLink.hidden = false; }
    nextLabel.textContent = data.busy && data.next_check_seconds ? `Next provider check in ${data.next_check_seconds}s` : "";
    paint();
    if (data.ready || data.failed || !data.busy) finish(data);
  };

  const finish = (data) => {
    if (done) return;
    done = true;
    clearTimeout(timer);
    checkButton.disabled = true; toggleButton.disabled = true;
    if (data.ready || !data.failed) {
      shown = 100; fill.style.width = "100%"; percentLabel.textContent = "100%";
      setTimeout(() => window.location.reload(), 1500);
    } else {
      checkButton.textContent = "Reload page"; checkButton.disabled = false;
      checkButton.onclick = () => window.location.reload();
    }
  };

  const poll = async () => {
    if (inFlight || done) return;
    inFlight = true;
    panel.classList.add("is-checking");
    checkButton.disabled = true;
    try {
      const response = await fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin", cache: "no-store" });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(data.message || `Status ${response.status}`);
      apply(data);
    } catch (error) {
      messageLabel.textContent = `Could not reach the server (${error.message}). Retrying…`;
    } finally {
      inFlight = false;
      panel.classList.remove("is-checking");
      if (!done) checkButton.disabled = false;
      schedule();
    }
  };

  const schedule = () => {
    clearTimeout(timer);
    if (!paused && !done && document.visibilityState === "visible") timer = setTimeout(poll, POLL_MS);
  };

  checkButton.addEventListener("click", () => { if (!done) { clearTimeout(timer); poll(); } });
  track.addEventListener("click", () => { if (!done) { clearTimeout(timer); poll(); } });
  toggleButton.addEventListener("click", () => {
    paused = !paused;
    toggleButton.setAttribute("aria-pressed", String(paused));
    toggleButton.textContent = paused ? "Resume auto-refresh" : "Pause auto-refresh";
    panel.classList.toggle("is-paused", paused);
    paused ? clearTimeout(timer) : poll();
  });
  document.addEventListener("visibilitychange", () => { if (document.visibilityState === "visible" && !paused) poll(); else clearTimeout(timer); });

  setInterval(paint, 1000); // smooth ticking between server polls
  poll();
})();
