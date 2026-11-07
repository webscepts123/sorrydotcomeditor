(() => {
  const bin = document.querySelector('.panel-playlist');
  if (bin) {
    const search = document.createElement('input');
    search.type = 'search'; search.placeholder = 'Find a sequence…';
    search.className = 'nle-input studio-search'; search.setAttribute('aria-label', 'Search media sequences');
    bin.querySelector('.nle-panel-header').after(search);
    const cards = [...bin.querySelectorAll('.col-6')];
    const empty = document.createElement('p'); empty.className = 'studio-no-results'; empty.hidden = true;
    empty.textContent = 'No matching sequences'; empty.setAttribute('role', 'status'); bin.append(empty);
    search.addEventListener('input', () => {
      const query = search.value.trim().toLowerCase();
      cards.forEach(card => { card.hidden = !card.textContent.toLowerCase().includes(query); });
      empty.hidden = cards.some(card => !card.hidden);
    });
  }
  const monitor = document.querySelector('#workspace-edit .panel-monitor');
  if (monitor) {
    const tools = document.createElement('div'); tools.className = 'studio-layout-tools';
    const focus = document.createElement('button'); focus.className = 'nle-btn';
    focus.textContent = 'Expand preview'; focus.setAttribute('aria-pressed', 'false');
    focus.addEventListener('click', () => {
      const expanded = document.body.classList.toggle('studio-focus');
      focus.setAttribute('aria-pressed', String(expanded)); focus.textContent = expanded ? 'Restore panels' : 'Expand preview';
    }); tools.append(focus); monitor.append(tools);
  }
  document.querySelectorAll('.workspace-tab,.insp-tab').forEach(tab => {
    tab.tabIndex = 0; tab.setAttribute('role', 'button');
    tab.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); event.stopPropagation(); tab.click(); }
    });
  });
})();
