// VOID SHADOW scene editor interactions.
(() => {
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const scenes = $$(".ed-scene");
  const store = {
    get(key) { try { return JSON.parse(localStorage.getItem(key) ?? "null"); } catch { return null; } },
    set(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* storage unavailable */ } }
  };

  // ---- Toast -------------------------------------------------------------
  const toastEl = $(".ed-toast");
  let toastTimer;
  function toast(message, isError = false) {
    if (!toastEl) return;
    toastEl.textContent = message;
    toastEl.classList.toggle("is-error", isError);
    toastEl.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toastEl.hidden = true; }, 3200);
  }

  // ---- Progress bars -----------------------------------------------------
  for (const fill of $$("[data-progress]")) {
    const percent = Math.max(0, Math.min(100, Number(fill.dataset.progress) || 0));
    requestAnimationFrame(() => requestAnimationFrame(() => { fill.style.width = `${percent}%`; }));
  }

  // ---- Tabs --------------------------------------------------------------
  for (const list of $$(".ed-tabs")) {
    const tabs = $$('[role="tab"]', list);
    const select = (tab, focus = false) => {
      for (const other of tabs) {
        const active = other === tab;
        other.setAttribute("aria-selected", String(active));
        other.tabIndex = active ? 0 : -1;
        const panel = document.getElementById(other.getAttribute("aria-controls"));
        if (panel) panel.hidden = !active;
      }
      if (focus) tab.focus();
    };
    tabs.forEach((tab, index) => {
      tab.addEventListener("click", () => select(tab));
      tab.addEventListener("keydown", (event) => {
        const step = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
        if (!step) return;
        event.preventDefault();
        select(tabs[(index + step + tabs.length) % tabs.length], true);
      });
    });
  }

  // ---- Collapse ----------------------------------------------------------
  const collapsedKey = `ed-collapsed:${location.pathname}`;
  const collapsed = new Set(store.get(collapsedKey) ?? []);
  function setCollapsed(scene, value, persist = true) {
    const button = $(".ed-collapse", scene);
    const body = $(".ed-scene-body", scene);
    scene.classList.toggle("is-collapsed", value);
    button?.setAttribute("aria-expanded", String(!value));
    button?.setAttribute("aria-label", value ? "Expand scene" : "Collapse scene");
    if (body) body.hidden = value;
    value ? collapsed.add(scene.dataset.sceneId) : collapsed.delete(scene.dataset.sceneId);
    if (persist) store.set(collapsedKey, [...collapsed]);
  }
  for (const scene of scenes) {
    if (collapsed.has(scene.dataset.sceneId)) setCollapsed(scene, true, false);
    $(".ed-collapse", scene)?.addEventListener("click", () => setCollapsed(scene, !scene.classList.contains("is-collapsed")));
  }
  const collapseAll = $("[data-ed-collapse-all]");
  const syncCollapseAll = () => {
    if (!collapseAll) return;
    const all = scenes.length > 0 && scenes.every((scene) => scene.classList.contains("is-collapsed"));
    collapseAll.setAttribute("aria-pressed", String(all));
    $("span", collapseAll).textContent = all ? "Expand all" : "Collapse all";
    $("i", collapseAll).className = `bi ${all ? "bi-arrows-expand" : "bi-arrows-collapse"}`;
  };
  collapseAll?.addEventListener("click", () => {
    const collapse = collapseAll.getAttribute("aria-pressed") !== "true";
    for (const scene of scenes) setCollapsed(scene, collapse);
    syncCollapseAll();
  });
  syncCollapseAll();

  // ---- Focus / navigation ------------------------------------------------
  let focusedScene = null;
  function focusScene(scene, scroll = true) {
    if (!scene) return;
    focusedScene?.classList.remove("is-focused");
    focusedScene = scene;
    scene.classList.add("is-focused");
    for (const link of $$(".ed-nav-item")) link.classList.toggle("is-active", link.dataset.sceneLink === scene.dataset.sceneId);
    if (scroll) {
      if (scene.classList.contains("is-collapsed")) setCollapsed(scene, false);
      scene.scrollIntoView({ behavior: "smooth", block: "start" });
      history.replaceState(null, "", `#${scene.id}`);
    }
  }
  for (const link of $$("[data-scene-link]")) {
    link.addEventListener("click", (event) => {
      const scene = document.getElementById(`scene-${link.dataset.sceneLink}`);
      if (!scene) return;
      event.preventDefault();
      if (scene.hidden) applyFilter("all");
      focusScene(scene);
    });
  }
  for (const scene of scenes) scene.addEventListener("focusin", () => focusScene(scene, false));

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver((entries) => {
      const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
      if (!visible) return;
      for (const link of $$(".ed-nav-item")) link.classList.toggle("is-active", link.dataset.sceneLink === visible.target.dataset.sceneId);
    }, { rootMargin: "-80px 0px -60% 0px" });
    scenes.forEach((scene) => observer.observe(scene));
  }
  const initial = location.hash ? document.getElementById(decodeURIComponent(location.hash.slice(1))) : null;
  if (initial?.classList?.contains("ed-scene")) setTimeout(() => focusScene(initial), 50);

  // ---- Navigator search --------------------------------------------------
  const search = $("[data-ed-search]");
  const navEmpty = $(".ed-nav-empty");
  search?.addEventListener("input", () => {
    const term = search.value.trim().toLowerCase();
    let shown = 0;
    for (const item of $$(".ed-nav li")) {
      const match = !term || item.textContent.toLowerCase().includes(term);
      item.hidden = !match;
      if (match) shown += 1;
    }
    if (navEmpty) navEmpty.hidden = shown > 0;
  });
  search?.addEventListener("keydown", (event) => {
    if (event.key === "Enter") { event.preventDefault(); $(".ed-nav li:not([hidden]) a")?.click(); }
    if (event.key === "Escape") { search.value = ""; search.dispatchEvent(new Event("input")); search.blur(); }
  });

  // ---- Status filter -----------------------------------------------------
  const filters = $$("[data-ed-filter]");
  function applyFilter(key) {
    for (const button of filters) button.setAttribute("aria-pressed", String(button.dataset.edFilter === key));
    for (const scene of scenes) scene.hidden = key !== "all" && scene.dataset.status !== key;
  }
  filters.forEach((button) => button.addEventListener("click", () => applyFilter(button.dataset.edFilter)));

  // ---- Keyboard shortcuts ------------------------------------------------
  const typing = () => ["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement?.tagName) || document.activeElement?.isContentEditable;
  document.addEventListener("keydown", (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "s") {
      const scene = document.activeElement?.closest(".ed-scene") ?? focusedScene;
      const form = scene && ($('[role="tabpanel"]:not([hidden]) form[data-ed-save], [role="tabpanel"]:not([hidden]) form[action$="/kaggle-prompt"]', scene) ?? $("form[data-ed-save]", scene));
      if (form) { event.preventDefault(); form.requestSubmit(); }
      return;
    }
    if (typing() || event.ctrlKey || event.metaKey || event.altKey) return;
    if (event.key === "/" && search) { event.preventDefault(); search.focus(); return; }
    if (event.key === "j" || event.key === "k") {
      const visible = scenes.filter((scene) => !scene.hidden);
      if (!visible.length) return;
      const index = visible.indexOf(focusedScene);
      const next = event.key === "j" ? Math.min(visible.length - 1, index + 1) : Math.max(0, index - 1);
      focusScene(visible[index === -1 ? 0 : next]);
    }
  });

  // ---- Inline saving -----------------------------------------------------
  const dirtyForms = new Set();
  function markDirty(form, dirty) {
    dirty ? dirtyForms.add(form) : dirtyForms.delete(form);
    const scene = form.closest(".ed-scene");
    const badge = scene && $(".ed-dirty", scene);
    if (badge) badge.hidden = ![...dirtyForms].some((other) => other.closest(".ed-scene") === scene);
  }

  async function postForm(form) {
    const isPrompt = /\/kaggle-prompt$/.test(form.getAttribute("action"));
    const body = new URLSearchParams(new FormData(form));
    const response = await fetch(form.getAttribute("action"), {
      method: "POST",
      body,
      credentials: "same-origin",
      headers: { accept: isPrompt ? "application/json" : "text/html" }
    });
    if (response.redirected && /\/login(?:$|\?)/.test(new URL(response.url).pathname)) throw new Error("Session expired. Sign in again.");
    if (!response.ok) {
      let message = `Save failed (${response.status}).`;
      try { message = (await response.json()).message || message; } catch { /* html error */ }
      throw new Error(message);
    }
  }

  const saveForms = $$('.ed-scene form[data-ed-save], .ed-scene form[action$="/kaggle-prompt"]');
  for (const form of saveForms) {
    const field = $("textarea", form);
    const counter = $("[data-ed-count]", form);
    const state = $(".ed-save-state", form);
    const button = $('[type="submit"]', form);
    const label = button && ($("span", button) ?? button);
    const original = label?.textContent;
    let saved = field?.value ?? "";
    const updateCount = () => { if (counter && field) counter.textContent = field.value.length.toLocaleString(); };
    updateCount();
    field?.addEventListener("input", () => { updateCount(); markDirty(form, field.value !== saved); if (state) state.textContent = ""; });

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!field?.value.trim() && form.matches('[action$="/kaggle-prompt"]')) { toast("Write a video prompt first.", true); field?.focus(); return; }
      const value = field?.value ?? "";
      if (button) button.disabled = true;
      if (label) label.textContent = "Saving…";
      try {
        await postForm(form);
        saved = value;
        markDirty(form, field ? field.value !== saved : false);
        if (state) { state.classList.remove("is-error"); state.textContent = "Saved"; }
        toast("Scene saved");
      } catch (error) {
        if (state) { state.classList.add("is-error"); state.textContent = error.message; }
        toast(error.message, true);
      } finally {
        if (button) button.disabled = false;
        if (label) label.textContent = original;
      }
    });
  }
  window.addEventListener("beforeunload", (event) => {
    if (!dirtyForms.size) return;
    event.preventDefault();
    event.returnValue = "";
  });

  // ---- Busy / confirm forms ----------------------------------------------
  for (const form of $$("form[data-ed-busy]")) {
    form.addEventListener("submit", () => {
      const button = $('[type="submit"]', form);
      const label = button && ($("span", button) ?? button);
      if (label) label.textContent = form.dataset.edBusy;
      setTimeout(() => { if (button) button.disabled = true; }, 0);
    });
  }
  for (const form of $$('[data-studio-submit="0"]')) {
    form.addEventListener("submit", (event) => {
      if (!confirm("Queue every scene in this production for rendering?")) { event.preventDefault(); return; }
      const button = document.getElementById("render-batch");
      button.textContent = "QUEUEING SCENES…";
      setTimeout(() => { button.disabled = true; }, 0);
    });
  }
  for (const form of $$('[data-studio-submit="1"]')) {
    form.addEventListener("submit", (event) => {
      if (!confirm("Permanently delete this scene from the timeline? This cannot be undone.")) event.preventDefault();
    });
  }

  // ---- Live render queue -------------------------------------------------
  const jobs = $$("[data-queue-scene]");
  const queueCount = $("[data-queue-count]");
  const queueEmpty = $(".ed-queue-empty");
  let finished = 0;

  function updateScene(sceneId, key, label) {
    const scene = document.getElementById(`scene-${sceneId}`);
    if (!scene) return;
    scene.dataset.status = key;
    const pill = $(".ed-pill", scene);
    if (pill) { pill.dataset.status = key; pill.textContent = label; }
    const dot = $(`.ed-nav-item[data-scene-link="${sceneId}"] .ed-dot`);
    if (dot) dot.dataset.status = key;
  }

  for (const job of jobs) {
    const id = job.dataset.queueScene;
    const fill = $(".ed-job-fill", job);
    const msg = $(".ed-job-msg", job);
    let timer = null, data = null, offset = 0;

    const render = () => {
      if (!data) return;
      if (data.ready) return;
      const started = data.started_at ? Date.parse(data.started_at) : NaN;
      const elapsed = Number.isNaN(started) ? 0 : (Date.now() + offset - started) / 1000;
      const percent = Math.min(95, Math.max(4, (elapsed / (data.eta_seconds || 600)) * 100));
      fill.style.width = `${percent}%`;
    };

    async function poll() {
      if (document.hidden) { timer = setTimeout(poll, 5000); return; }
      try {
        const response = await fetch(`/scenes/${id}/progress`, { headers: { accept: "application/json" }, credentials: "same-origin" });
        if (!response.ok) throw new Error(`Status check failed (${response.status})`);
        data = await response.json();
        if (data.server_time) offset = Date.parse(data.server_time) - Date.now();
        if (data.ready) {
          job.classList.add("is-ready");
          fill.style.width = "100%";
          msg.innerHTML = "";
          msg.append("Render complete. ");
          const reload = document.createElement("a");
          reload.href = `${location.pathname}#scene-${id}`;
          reload.textContent = "Reload to view";
          reload.addEventListener("click", (event) => { event.preventDefault(); location.hash = `scene-${id}`; location.reload(); });
          msg.append(reload);
          updateScene(id, "ready", "Ready");
          toast(`Scene ${id} finished rendering`);
          return settle();
        }
        if (data.failed || !data.busy) {
          job.classList.add("is-failed");
          fill.style.width = "100%";
          msg.textContent = data.message || "Render stopped.";
          updateScene(id, data.failed ? "failed" : "draft", data.status || "Failed");
          return settle();
        }
        msg.textContent = data.stalled
          ? "Taking much longer than usual. Check the scene page."
          : data.message || `${data.remote_status || "Processing"}…`;
        render();
      } catch (error) {
        msg.textContent = error.message;
      }
      timer = setTimeout(poll, Math.max(8, Math.min(30, data?.next_check_seconds || 10)) * 1000);
    }

    function settle() {
      clearTimeout(timer);
      finished += 1;
      if (queueCount) queueCount.textContent = String(Math.max(0, jobs.length - finished));
      if (finished === jobs.length && queueEmpty) queueEmpty.hidden = false;
    }

    setInterval(render, 1000);
    poll();
  }
})();
