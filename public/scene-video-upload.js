const sceneUpload = document.querySelector("[data-scene-upload]");

sceneUpload?.addEventListener("submit", async event => {
  event.preventDefault();
  const file = sceneUpload.elements.clip.files[0];
  const status = sceneUpload.querySelector("[data-upload-status]");
  const button = sceneUpload.querySelector("button");
  if (!file || !/\.mp4$/i.test(file.name) || file.size < 12 || file.size > Number(sceneUpload.dataset.uploadLimit)) {
    status.textContent = "Select an MP4 up to 64 MB.";
    return;
  }
  button.disabled = true;
  const id = crypto.randomUUID();
  const chunkSize = 128 * 1024;
  try {
    for (let offset = 0; offset < file.size;) {
      const bytes = new Uint8Array(await file.slice(offset, offset + chunkSize).arrayBuffer());
      let binary = "";
      for (let i = 0; i < bytes.length; i += 8192) binary += String.fromCharCode(...bytes.subarray(i, i + 8192));
      const body = JSON.stringify({ id, offset, size: file.size, data: btoa(binary) });
      const send = () => fetch(sceneUpload.action, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-Token": sceneUpload.elements._csrf.value },
        body
      });
      let response;
      try { response = await send(); } catch { response = await send(); }
      const result = await response.json().catch(() => null);
      if (!response.ok || !result || result.received !== offset + bytes.length) {
        throw new Error(result?.message || "The upload was interrupted. Select the MP4 and try again.");
      }
      offset = result.received;
      status.textContent = result.ready ? "Video attached. Reloading…" : `Uploading · ${Math.floor(100 * offset / file.size)}%`;
    }
    window.location.reload();
  } catch (error) {
    status.textContent = error.message;
    button.disabled = false;
  }
});
