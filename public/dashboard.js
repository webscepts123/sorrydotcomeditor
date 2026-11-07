(() => {
  const widget = document.getElementById("resource-widget");
  if (!widget) return;

  const fields = Object.fromEntries(
    ["status", "gpu", "version", "vram", "ram", "queue", "updated"]
      .map((name) => [name, document.getElementById(`worker-${name}`)])
  );
  const unavailable = () => {
    fields.gpu.textContent = "GPU: unavailable";
    fields.version.textContent = "ComfyUI: unavailable";
    fields.vram.textContent = "GPU VRAM: unavailable";
    fields.ram.textContent = "System RAM: unavailable";
    fields.queue.textContent = "ComfyUI queue: unavailable";
  };
  const memory = (label, used, total, percent) =>
    [used, total, percent].every((value) => typeof value === "number" && Number.isFinite(value))
      ? `${label}: ${used} / ${total} GB (${percent}%)`
      : `${label}: unavailable`;

  async function refresh() {
    try {
      const response = await fetch(widget.dataset.statusUrl, { cache: "no-store", headers: { Accept: "application/json" } });
      const status = await response.json();
      if (!response.ok || status.online !== true) {
        unavailable();
        fields.status.textContent = "Worker unavailable";
        fields.updated.textContent = typeof status.error === "string" ? status.error : "Unable to check the render worker.";
      } else {
        fields.status.textContent = "Worker online";
        fields.gpu.textContent = `GPU: ${status.gpu_name || "unavailable"}`;
        fields.version.textContent = `ComfyUI: ${status.comfyui_version || "unavailable"}`;
        fields.vram.textContent = memory("GPU VRAM", status.vram_used_gb, status.vram_total_gb, status.vram_percent);
        fields.ram.textContent = memory("System RAM", status.ram_used_gb, status.ram_total_gb, status.ram_percent);
        fields.queue.textContent = Number.isInteger(status.running_jobs) && Number.isInteger(status.pending_jobs)
          ? `ComfyUI queue: ${status.running_jobs} running, ${status.pending_jobs} queued`
          : "ComfyUI queue: unavailable";
        fields.updated.textContent = `Last checked: ${new Date().toLocaleTimeString()}`;
      }
    } catch (error) {
      console.error("Render worker status request failed:", error);
      unavailable();
      fields.status.textContent = "Worker unavailable";
      fields.updated.textContent = "Unable to check the render worker. Check your connection or sign in again.";
    } finally {
      setTimeout(refresh, 5000);
    }
  }
  refresh();
})();
