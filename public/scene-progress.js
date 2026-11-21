// Live, interactive progress bar for scenes with a running generation job.
// Show confirmed provider stages and elapsed time.
(() => {
  const clip = document.querySelector("[data-scene-video]");
  if (clip) {
    const player = document.createElement("video");
    player.controls = true;
    player.preload = "metadata";
    player.src = clip.href;
    player.className = "scene-preview-player";
    clip.before(player);
  }

  const panel = document.querySelector(".render-progress[data-progress-url]");
  if (!panel) return;

  const $ = (selector) => panel.querySelector(selector);
  const url = panel.dataset.progressUrl;
  const fill = $(".render-progress-fill");
  const track = $(".render-progress-track");
  const detail = $(".render-progress-detail");
  const detailLabel = $(".render-progress-detail-label");
  const detailTrack = $(".render-progress-detail-track");
  const detailFill = $(".render-progress-detail-fill");
  const percentLabel = $(".render-progress-percent");
  const title = $(".render-progress-title");
  const elapsedLabel = $(".render-progress-elapsed");
  const etaLabel = $(".render-progress-eta");
  const nextLabel = $(".render-progress-next");
  const messageLabel = $(".render-progress-message");
  const checkButton = $(".render-progress-check");
  const toggleButton = $(".render-progress-toggle");
  const notebookLink = $(".render-progress-notebook");
  const cancelForm = $(".render-progress-cancel");
  const stepList = $(".render-progress-steps");
  const steps = [...panel.querySelectorAll("[data-step]")];
  const order = ["queued", "running", "downloading", "ready"];
  const providerNames = { kaggle: "Kaggle (CogVideoX)", local: "Local Wan 2.1", comfy: "ComfyUI", none: "render worker" };
  const POLL_MS = 8000;

  let state = null, clockOffset = 0, paused = false, timer = null, inFlight = false, done = false;

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
    return null;
  };

  const stageLabels = { queued: "Queued", running: "Generating", downloading: "Importing", ready: "Ready" };
  const detailLabels = {
    model_loaded: "Model loaded · generating frames",
    sampling: "Sampling",
    loading_opening: "Loading the opening-shot model",
    opening: "Opening shot",
    loading_main: "Loading the main-shot model",
    main: "Main shot",
    exporting: "Exporting the MP4"
  };
  const paint = () => {
    if (!state) return;
    const stage = state.failed ? null : stageOf(state);
    const current = order.indexOf(stage);
    const detail = state.progress_detail;
    const showPercent = !state.failed && state.busy && detail?.total > 0 && stage !== "downloading" && stage !== "ready";
    const percent = showPercent ? Math.floor(100 * detail.step / detail.total) : null;
    percentLabel.textContent = percent === null
      ? state.failed ? "Failed" : stageLabels[stage] || "Checking"
      : `${percent}% sampled`;
    track.setAttribute("aria-valuetext", stageLabels[stage] || percentLabel.textContent);
    fill.style.width = current < 0 ? "0" : ((current + 1) * 25) + "%";
    const seconds = elapsed();
    elapsedLabel.textContent = "Elapsed " + format(seconds);
    const delayed = state.busy && !state.failed && !state.ready
      && (state.stalled || (state.eta_seconds > 0 && seconds >= state.eta_seconds));
    let delayText = "";
    if (delayed) {
      const lastStage = stage === "queued" ? "Waiting in the render queue"
        : stage === "downloading" ? "Importing the finished video"
        : detailLabels[detail?.stage] || (stage === "running" ? "Generating video" : "Waiting for provider status");
      delayText = `Taking longer than usual · ${lastStage}`;
    }
    if (etaLabel.textContent !== delayText) etaLabel.textContent = delayText;
    panel.classList.toggle("is-queued", stage === "queued");
    stepList.style.setProperty("--rp-steps", String(stage === "ready" ? 3 : Math.max(0, current)));
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
    panel.classList.toggle("is-stalled", Boolean(data.stalled) && !data.failed);
    title.textContent = data.failed ? "Generation failed" : data.ready ? "Scene ready" : providerNames[data.provider] || "Render worker";
    const stage = stageOf(data);
    const progress = data.progress_detail;
    const progressLabel = detailLabels[progress?.stage];
    detail.hidden = !progressLabel || data.failed || stage === "downloading" || stage === "ready";
    if (!detail.hidden) {
      detailLabel.textContent = progress.total
        ? `${progressLabel} · ${progress.step} / ${progress.total} steps`
        : progress.stage === "model_loaded" ? `${progressLabel} · waiting for step count` : progressLabel;
      detailTrack.hidden = !progress.total;
      if (progress.total) {
        detailTrack.setAttribute("aria-valuemax", String(progress.total));
        detailTrack.setAttribute("aria-valuenow", String(progress.step));
        detailFill.style.width = `${100 * progress.step / progress.total}%`;
      }
    }
    messageLabel.textContent = data.message || ({
      queued: "Waiting for the provider to start the render.",
      running: "The provider is generating the video.",
      downloading: "The provider finished. The studio is importing the clip.",
      ready: "Video imported. Reloading?"
    })[stage] || "Waiting for a provider status update.";
    cancelForm.hidden = !(data.busy && data.stalled);
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
    cancelForm.hidden = true;
    if (data.ready || !data.failed) {
      fill.style.width = "100%";
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
