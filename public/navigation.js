const currentPath = window.location.pathname;
const projectMatch = currentPath.match(/^\/projects\/(\d+)(?:\/|$)/);

for (const toggle of document.querySelectorAll(".nav-toggle")) {
  const submenu = document.getElementById(toggle.getAttribute("aria-controls"));
  if (!submenu) continue;
  const isProductionTools = submenu.id === "production-tools";
  const isCurrentSection = [...submenu.closest(".nav-group").querySelectorAll(".nav-group-heading a")]
    .some((link) => {
      const path = new URL(link.href, window.location.origin).pathname;
      return path === currentPath || (path !== "/timeline" && currentPath.startsWith(`${path}/`));
    });
  const initiallyOpen = isProductionTools || isCurrentSection;
  toggle.setAttribute("aria-expanded", String(initiallyOpen));
  submenu.hidden = !initiallyOpen;
  toggle.addEventListener("click", () => {
    const open = toggle.getAttribute("aria-expanded") !== "true";
    toggle.setAttribute("aria-expanded", String(open));
    submenu.hidden = !open;
  });
}

if (projectMatch) {
  for (const timeline of document.querySelectorAll('.side-nav a[href="/timeline"]')) {
    timeline.href = `/projects/${projectMatch[1]}/timeline`;
  }
  const newClip = document.querySelector('.topbar form[action="/scenes/create"]');
  if (newClip) {
    const project = document.createElement("input");
    project.type = "hidden";
    project.name = "project_id";
    project.value = projectMatch[1];
    newClip.appendChild(project);
  }
}

for (const link of document.querySelectorAll(".side-nav a")) {
  const path = new URL(link.href, window.location.origin).pathname;
  const isTimeline = /^\/projects\/\d+\/timeline$/.test(currentPath);
  const active = path === currentPath
    || (path === "/timeline" && isTimeline)
    || (path === "/projects" && currentPath.startsWith("/projects/") && !isTimeline)
    || (path !== "/projects" && path !== "/timeline" && currentPath.startsWith(`${path}/`));
  if (active) {
    link.classList.add("active");
    link.setAttribute("aria-current", "page");
  }
}
