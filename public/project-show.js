// Production overview interactions: progress bar, scene search/filter,
// confirm dialogs and busy states for long-running form actions.
// The Noderyx router swaps <main> between pages without reloading scripts,
// so this file is loaded on every app page and re-binds after navigation.
(() => {
  function init() {
    const page = document.querySelector(".ps-page");
    if (!page || page.dataset.psBound === "1") return;
    page.dataset.psBound = "1";

    // Animate render progress.
    const progress = page.querySelector(".ps-progress");
    const fill = progress?.querySelector(".ps-progress-fill");
    if (fill) {
      const percent = Math.max(0, Math.min(100, Number(progress.dataset.progress) || 0));
      requestAnimationFrame(() => requestAnimationFrame(() => { fill.style.width = `${percent}%`; }));
    }

    // Scene search + status filter.
    const scenes = [...page.querySelectorAll(".ps-scene")];
    const search = page.querySelector("[data-scene-search]");
    const filters = [...page.querySelectorAll(".ps-filter")];
    const empty = page.querySelector("[data-filter-empty]");
    let activeFilter = "all";

    for (const button of filters) {
      const key = button.dataset.filter;
      const count = key === "all" ? scenes.length : scenes.filter((scene) => scene.dataset.status === key).length;
      const badge = document.createElement("span");
      badge.className = "ps-count";
      badge.textContent = String(count);
      button.appendChild(badge);
      button.addEventListener("click", () => {
        activeFilter = key;
        for (const other of filters) other.setAttribute("aria-pressed", String(other === button));
        applyFilter();
      });
    }

    function applyFilter() {
      const term = (search?.value ?? "").trim().toLowerCase();
      let visible = 0;
      for (const scene of scenes) {
        const statusMatch = activeFilter === "all" || scene.dataset.status === activeFilter;
        const textMatch = !term || scene.textContent.toLowerCase().includes(term);
        scene.hidden = !(statusMatch && textMatch);
        if (!scene.hidden) visible += 1;
      }
      if (empty) empty.hidden = visible > 0 || scenes.length === 0;
    }

    search?.addEventListener("input", applyFilter);
    search?.addEventListener("keydown", (event) => {
      if (event.key === "Escape") { search.value = ""; applyFilter(); search.blur(); }
    });

    for (const form of page.querySelectorAll("form[data-confirm], form[data-busy-label]")) {
      form.addEventListener("submit", async (event) => {
        if (form.dataset.confirmed === "1") return;
        if (form.dataset.confirm) {
          event.preventDefault();
          if (!(await confirmAction(form.dataset.confirm))) return;
          form.dataset.confirmed = "1";
          markBusy(form);
          form.submit();
          return;
        }
        markBusy(form);
      });
    }
  }

  // "/" focuses the scene search on whichever overview is currently shown.
  document.addEventListener("keydown", (event) => {
    if (event.key !== "/") return;
    const search = document.querySelector(".ps-page [data-scene-search]");
    if (!search) return;
    const tag = document.activeElement?.tagName;
    if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT") return;
    event.preventDefault();
    search.focus();
  });

  // Confirm dialog for destructive forms.
  let dialog;
  function confirmAction(message) {
    if (typeof HTMLDialogElement === "undefined") return Promise.resolve(window.confirm(message));
    if (!dialog || !dialog.isConnected) {
      dialog = document.createElement("dialog");
      dialog.className = "ps-dialog";
      dialog.innerHTML = `
        <form method="dialog">
          <div class="ps-dialog-body"><h3>Are you sure?</h3><p></p></div>
          <div class="ps-dialog-actions">
            <button class="secondary" value="cancel" autofocus>Cancel</button>
            <button class="danger" value="confirm">Delete</button>
          </div>
        </form>`;
      document.body.appendChild(dialog);
    }
    dialog.querySelector("p").textContent = message;
    dialog.returnValue = "";
    dialog.showModal();
    return new Promise((resolve) => {
      dialog.addEventListener("close", () => resolve(dialog.returnValue === "confirm"), { once: true });
    });
  }

  function markBusy(form) {
    const button = form.querySelector("button[type='submit']");
    if (!button) return;
    const label = button.querySelector("span");
    if (label && form.dataset.busyLabel) label.textContent = form.dataset.busyLabel;
    // Disable after the submit event so the browser still sends the form.
    setTimeout(() => { button.disabled = true; }, 0);
  }

  init();
  addEventListener("noderyx:navigate", () => requestAnimationFrame(init));
})();
