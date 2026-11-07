
function startBatchRender(form) {
    const button = document.getElementById('render-batch');
    button.disabled = true;
    button.textContent = 'QUEUEING SCENES...';
    return true;
}

document.querySelectorAll('[data-studio-submit="0"]').forEach(element => element.addEventListener("submit", function(event) { const result = (() => { return startBatchRender(this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-submit="1"]').forEach(element => element.addEventListener("submit", function(event) { const result = (() => { return confirm('CRITICAL: Permanently purge this sequence from the timeline?'); } )(); if (result === false) event.preventDefault(); }));
