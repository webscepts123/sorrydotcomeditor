for (const panel of document.querySelectorAll('[data-kaggle-wan]')) {
  const id = panel.dataset.sceneId;
  const csrf = panel.querySelector('[name="_csrf"]').value;
  const connection = panel.querySelector('[data-kaggle-connection]');
  const message = panel.querySelector('[data-kaggle-progress-message]');
  const generate = panel.querySelector('[data-kaggle-generate]');
  const check = panel.querySelector('[data-kaggle-progress]');
  const details = panel.querySelector('[data-kaggle-connect-panel]');
  const disconnect = panel.querySelector('[data-kaggle-disconnect]');
  const prompt = panel.querySelector('textarea[name="prompt"]');
  const link = panel.querySelector('[data-kaggle-notebook-link]');
  let connected = false, active = panel.dataset.jobActive === 'true', checking = false, submitting = false, dirty = false;
  prompt.addEventListener('input', () => { dirty = true; });
  async function request(url, body) {
    const response = await fetch(url, {
      method: body === undefined ? 'GET' : 'POST',
      headers: { accept: 'application/json', 'content-type': 'application/json', 'x-csrf-token': csrf },
      ...(body === undefined ? {} : { body: JSON.stringify(body) }),
    });
    let data;
    try { data = await response.json(); } catch { throw new Error('Could not contact the studio. Refresh and sign in again.'); }
    if (!response.ok) throw new Error(data.message || 'Kaggle request failed. Please try again.');
    return data;
  }
  function buttons() {
    generate.disabled = !connected || active || submitting;
    generate.textContent = active ? 'Scene generation in progress…' : 'Generate this scene · Kaggle GPU';
    disconnect.hidden = !connected;
  }
  async function loadConnection() {
    try {
      const data = await request('/wan-kaggle/connection');
      connected = data.connected; connection.textContent = data.message;
      details.open = !connected && data.available;
    } catch (error) { connection.textContent = error.message; }
    buttons();
  }
  panel.querySelector('[data-kaggle-connect]').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget, button = form.querySelector('button');
    button.disabled = true; connection.textContent = 'Connecting to Kaggle…';
    try {
      await request('/wan-kaggle/connect', Object.fromEntries(new FormData(form)));
      form.querySelector('[name="secret"]').value = '';
      await loadConnection();
    } catch (error) { connection.textContent = error.message; }
    finally { button.disabled = false; }
  });
  disconnect.addEventListener('click', async () => {
    try { await request('/wan-kaggle/disconnect', {}); connected = false; await loadConnection(); }
    catch (error) { connection.textContent = error.message; }
  });
  function notebook(url) {
    if (url?.startsWith('https://www.kaggle.com/code/')) { link.href = url; link.hidden = false; }
  }
  generate.addEventListener('click', async () => {
    if (!prompt.value.trim()) { prompt.focus(); message.textContent = 'Write a video prompt first.'; return; }
    submitting = true; buttons(); message.textContent = 'Saving your prompt and sending this scene to Kaggle…';
    const submittedPrompt = prompt.value;
    try {
      const data = await request(`/scenes/${id}/render-kaggle`, { prompt: submittedPrompt });
      active = true; panel.dataset.jobActive = 'true'; dirty = prompt.value !== submittedPrompt; notebook(data.notebook_url);
      message.textContent = 'Scene queued on Kaggle. The finished video will return here automatically. First-time model setup may take a while.';
    } catch (error) { message.textContent = error.message; }
    finally { submitting = false; buttons(); }
  });
  async function progress() {
    if (checking) return;
    checking = true; check.disabled = true;
    try {
      const data = await request(`/scenes/${id}/kaggle-progress`, {});
      notebook(data.notebook_url);
      if (data.status === 'Ready' || data.ready) {
        active = false;
        message.textContent = 'Video ready and attached to this scene.';
        if (data.editor_url) {
          const open = document.createElement('a'); open.className = 'nle-btn w-100';
          open.href = data.editor_url; open.textContent = 'Open finished scene'; message.append(open);
        }
        if (!dirty && panel.dataset.jobActive === 'true') location.reload();
      } else if (data.status === 'failed') {
        active = false; message.textContent = data.message || 'Generation failed. Open the Kaggle job for details.';
      } else {
        active = Boolean(data.pending);
        if (active) panel.dataset.jobActive = 'true';
        message.textContent = data.message || (active ? `Kaggle: ${data.status || 'processing'}. The video will attach automatically.` : 'No Kaggle job running. Connect and generate this scene.');
      }
    } catch (error) { message.textContent = error.message; }
    finally { checking = false; check.disabled = false; buttons(); }
  }
  check.addEventListener('click', progress);
  loadConnection();
  if (active) progress();
  const timer = setInterval(() => { if (active && !document.hidden) progress(); }, 20000);
  window.addEventListener('pagehide', () => clearInterval(timer), { once: true });
}
