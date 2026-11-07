const localPanels = document.querySelectorAll("[data-local-wan]");
let localCheckRunning = false;
async function checkLocalWan() {
  if (localCheckRunning) return;
  localCheckRunning = true;
  try {
    const response = await fetch("/wan-local/status", { headers: { accept: "application/json" }, signal: AbortSignal.timeout(25000) });
    if (!response.ok) throw new Error("Could not check the local worker.");
    const status = await response.json();
    for (const panel of localPanels) {
      panel.querySelector("[data-local-wan-status]").textContent = status.message;
      const button = panel.querySelector("[data-local-wan-render]");
      button.disabled = !status.ready || button.dataset.busy === "true";
    }
  } catch (error) {
    for (const panel of localPanels) {
      panel.querySelector("[data-local-wan-status]").textContent = `Local worker unavailable: ${error.message}. Run npm run wan:start.`;
      panel.querySelector("[data-local-wan-render]").disabled = true;
    }
  } finally {
    localCheckRunning = false;
  }
}
for (const button of document.querySelectorAll("[data-local-wan-check]")) button.addEventListener("click", checkLocalWan);
for (const form of document.querySelectorAll('form[action$="/render-local"]')) form.addEventListener("submit", () => {
  const button = form.querySelector("button");
  button.disabled = true;
  button.textContent = "Queueing local render…";
});
if (localPanels.length) checkLocalWan();
