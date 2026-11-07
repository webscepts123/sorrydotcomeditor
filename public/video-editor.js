const monitor = document.getElementById("editor-preview");
const mount = document.getElementById("video-mount");
const message = document.getElementById("editor-message");
let preview = null;
if (monitor && mount && monitor.dataset.videoUrl) {
  preview = document.createElement("video");
  preview.controls = true;
  preview.preload = "metadata";
  preview.addEventListener("error", () => {
    message.textContent = "The selected video could not be loaded. Check that the rendered file is available.";
  });
  preview.src = monitor.dataset.videoUrl;
  mount.replaceChildren(preview);
}
const headline = document.querySelector("[data-editor-output='headline']");
const subtitle = document.querySelector("[data-editor-output='subtitle']");

function updatePreview() {
  if (!preview) return;
  const value = (name, fallback) => Number(document.querySelector(`[data-editor-control="${name}"]`)?.value ?? fallback);
  const x = value("x", 0);
  const y = value("y", 0);
  const scale = value("scale", 100) / 100;
  const rotation = value("rotation", 0);
  const opacity = value("opacity", 100) / 100;
  const grain = value("grain", 0) / 100;
  const vignette = value("vignette", 0) / 100;
  const halation = value("halation", 0) / 100;
  const brightness = value("brightness", 100) / 100;
  const contrast = value("contrast", 100) / 100;
  const saturation = value("saturation", 100) / 100;
  const hue = value("hue", 0);
  preview.style.transform = `translate(${x}%, ${y}%) scale(${scale}) rotate(${rotation}deg)`;
  preview.style.opacity = String(opacity);
  preview.style.filter = `brightness(${brightness}) contrast(${contrast * (1 + halation * 0.2)}) saturate(${saturation}) hue-rotate(${hue}deg) drop-shadow(0 0 ${halation * 12}px rgba(255, 92, 48, ${halation * 0.5}))`;
  monitor.style.setProperty("--editor-grain", String(grain));
  monitor.style.setProperty("--editor-vignette", String(vignette));
}

for (const control of document.querySelectorAll("[data-editor-control]")) {
  control.addEventListener("input", () => {
    if (control.dataset.editorControl === "headline" && headline) headline.textContent = control.value;
    if (control.dataset.editorControl === "subtitle" && subtitle) subtitle.textContent = control.value;
    updatePreview();
  });
}

for (const reset of document.querySelectorAll("[data-editor-reset]")) reset.addEventListener("click", () => {
  for (const control of document.querySelectorAll("[data-editor-control]")) {
    control.value = control.type === "range"
      ? control.defaultValue
      : "";
  }
  if (headline) headline.textContent = "";
  if (subtitle) subtitle.textContent = "";
  updatePreview();
});

const workspaceButtons = document.querySelectorAll("[data-workspace-button]");
const workspacePanels = document.querySelectorAll("[data-workspace-panel]");
for (const button of workspaceButtons) {
  button.addEventListener("click", () => {
    for (const item of workspaceButtons) item.setAttribute("aria-pressed", String(item === button));
    for (const panel of workspacePanels) panel.hidden = panel.dataset.workspacePanel !== button.dataset.workspaceButton;
  });
}

for (const button of document.querySelectorAll("[data-editor-action]")) {
  button.addEventListener("click", async () => {
    if (!preview) return;
    switch (button.dataset.editorAction) {
      case "start": preview.currentTime = 0; break;
      case "back": preview.currentTime = Math.max(0, preview.currentTime - 1 / 30); break;
      case "forward": preview.currentTime = Math.min(Number.isFinite(preview.duration) ? preview.duration : preview.currentTime, preview.currentTime + 1 / 30); break;
      case "mute":
        preview.muted = !preview.muted;
        button.setAttribute("aria-pressed", String(preview.muted));
        button.textContent = preview.muted ? "Unmute audio" : "Mute audio";
        break;
      case "play":
        if (preview.paused) {
          try { await preview.play(); } catch (error) {
            console.error("Video playback failed:", error);
            message.textContent = "Video playback failed. Check that the rendered file is available.";
          }
        } else preview.pause();
        break;
    }
  });
}

document.getElementById("playback-speed")?.addEventListener("change", (event) => {
  if (preview) preview.playbackRate = Number(event.target.value);
});

document.getElementById("master-volume")?.addEventListener("input", (event) => {
  if (preview) preview.volume = Number(event.target.value) / 100;
});

updatePreview();
