// Original VOID SHADOW editor interface, ported from the Blade view.
import { escapeHtml, mediaUrl, localWanControls } from "./studio-helpers.js";
export function renderProductionEditor({ project, scenes, characters = [], currentScene = null, csrfToken = "" }) {
const currentSceneId = currentScene?.id;
const currentSceneIndex = Math.max(0, scenes.findIndex(s => s.id === currentSceneId));
const clipWidth = 180, playheadLeft = currentSceneIndex * 182;
const timelineSeconds = Math.max(30, scenes.length * 15), rulerStep = 5;
const sceneFileName = scene => scene?.video_path?.split("/").pop() ?? "No rendered video";
const sceneStatus = scene => String(scene?.status ?? "Draft").toUpperCase();
const formatTimecode = seconds => `01:${String(Math.floor(seconds / 60)).padStart(2, "0")}:${String(seconds % 60).padStart(2, "0")}:00`;
let output = "";
output += "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"><title>" + escapeHtml(project.title) + " — Production Editor</title><link rel=\"stylesheet\" href=\"/public/studio/bootstrap.min.css\"><link rel=\"stylesheet\" href=\"/public/studio/bootstrap-icons.css\"><link rel=\"stylesheet\" href=\"/public/studio/fonts.css\"><link rel=\"icon\" href=\"data:,\"></head><body>\n";
output += "\n";
output += "\n";
output += "<div class=\"container-fluid bg-black text-white p-0\" style=\"min-height: 100vh;\">\n";
output += "    <div class=\"d-flex justify-content-between align-items-center p-3 border-bottom border-secondary bg-dark\">\n";
output += "        <div class=\"d-flex align-items-center\">\n";
output += "            <h5 class=\"mb-0 fw-light tracking-widest\" style=\"font-family: 'Syncopate';\">VOID SHADOW EDITOR <span class=\"text-secondary small ms-2\">v1.0</span></h5>\n";
output += "            <span class=\"badge bg-danger ms-3 rounded-0 small tracking-widest uppercase\">LIVE: " + escapeHtml(project.title) + "</span>\n";
output += "        </div>\n";
output += "        <div class=\"d-flex gap-3\">\n";
output += "            <a href=\"" + escapeHtml(`/projects/${project.id}/export-xml`) + "\" class=\"btn btn-outline-secondary btn-sm rounded-0 tracking-widest uppercase\">Export XML</a>\n";
output += "            <form action=\"" + escapeHtml(`/projects/${project.id}/render-batch`) + "\" method=\"POST\" class=\"m-0\" data-studio-submit=\"0\">\n";
output += "                <input type=\"hidden\" name=\"_csrf\" value=\"" + escapeHtml(csrfToken) + "\">\n";
output += "                <button id=\"render-batch\" type=\"submit\" class=\"btn btn-white bg-white text-black btn-sm rounded-0 fw-bold tracking-widest px-4\" " + escapeHtml(!scenes.length ? 'disabled' : '') + ">BATCH RENDER (ALL SCENES)</button>\n";
output += "            </form>\n";
output += "        </div>\n";
output += "    </div>\n";
output += "\n";
output += "    <div class=\"row g-0\" style=\"height: calc(100vh - 70px);\">\n";
output += "        \n";
output += "        <div class=\"col-md-2 border-end border-secondary p-3 overflow-auto\">\n";
output += "            <h6 class=\"text-secondary small tracking-widest mb-4 uppercase\">Cast & Seeds</h6>\n";
for (const char of characters) {
output += "            <div class=\"p-2 border border-secondary mb-2 bg-dark d-flex align-items-center\">\n";
output += "                <div class=\"bg-secondary rounded-circle me-2\" style=\"width: 10px; height: 10px;\"></div>\n";
output += "                <span class=\"small uppercase fw-bold\">" + escapeHtml(char.name) + "</span>\n";
output += "                <code class=\"ms-auto text-info\" style=\"font-size: 10px;\">" + escapeHtml(char.ai_tag) + "</code>\n";
output += "            </div>\n";
}
if (!characters.length) {
output += "                <div class=\"border border-secondary p-3 text-center\">\n";
output += "                    <p class=\"text-secondary small mb-2\">No cast assigned to this project.</p>\n";
output += "                    <a href=\"" + escapeHtml(`/characters/create?project_id=${project.id}`) + "\" class=\"btn btn-outline-light btn-sm rounded-0 w-100 tracking-widest\" style=\"font-size:9px;\">ADD CHARACTER</a>\n";
output += "                </div>\n";
}
output += "            \n";
output += "            <hr class=\"border-secondary my-4\">\n";
output += "            \n";
output += "            <h6 class=\"text-secondary small tracking-widest mb-3 uppercase\">Style DNA</h6>\n";
output += "            <div class=\"small p-2 bg-dark border border-secondary text-secondary italic uppercase tracking-widest\" style=\"font-size: 11px;\">\n";
output += "                " + escapeHtml(project.style_preset) + " <br>\n";
output += "                <span style=\"font-size: 10px;\">ASPECT: " + escapeHtml(project.aspect_ratio) + "</span>\n";
output += "            </div>\n";
output += "        </div>\n";
output += "\n";
output += "        <div class=\"col-md-7 p-4 overflow-auto border-end border-secondary\">\n";
output += "            <h6 class=\"text-secondary small tracking-widest mb-4 uppercase\">Production Timeline (" + escapeHtml(scenes.length) + " Segments)</h6>\n";
output += "\n";
if (!scenes.length) {
output += "                <div class=\"border border-secondary d-flex align-items-center justify-content-center text-center p-5\" style=\"min-height:360px;\">\n";
output += "                    <div>\n";
output += "                        <i class=\"bi bi-film text-secondary fs-1\"></i>\n";
output += "                        <h6 class=\"mt-3 tracking-widest\">TIMELINE IS EMPTY</h6>\n";
output += "                        <p class=\"text-secondary small\">Create the first scene to begin building this movie.</p>\n";
output += "                        <a href=\"" + escapeHtml(`/scenes/create?project_id=${project.id}`) + "\" class=\"btn btn-outline-info rounded-0 px-4 tracking-widest small\">NEW CLIP +</a>\n";
output += "                    </div>\n";
output += "                </div>\n";
}
output += "            \n";
for (const scene of scenes) {
output += "            <div class=\"scene-row mb-4 p-3 border border-secondary bg-black transition-hover shadow-sm\">\n";
output += "                <div class=\"d-flex justify-content-between align-items-start mb-3\">\n";
output += "                    <div>\n";
output += "                        <span class=\"text-secondary small fw-bold tracking-widest\">SCENE #" + escapeHtml(String(scene.order_index).padStart(3, '0')) + "</span>\n";
if (scene.status == 'Processing') {
output += "                            <span class=\"ms-3 badge border border-warning text-warning small uppercase rounded-0\">Processing <span class=\"spinner-grow spinner-grow-sm ms-1\" style=\"width: 0.5rem; height: 0.5rem;\"></span></span>\n";
} else {
output += "                            <span class=\"ms-3 badge border border-secondary small text-secondary uppercase rounded-0\">" + escapeHtml(scene.status ?? 'Draft') + "</span>\n";
}
output += "                    </div>\n";
output += "                    \n";
output += "                    <div class=\"dropdown\">\n";
output += "                        <button class=\"btn btn-link text-white p-0\" data-bs-toggle=\"dropdown\"><i class=\"bi bi-three-dots-vertical\"></i></button>\n";
output += "                        <ul class=\"dropdown-menu dropdown-menu-dark rounded-0 border-secondary shadow-lg\">\n";
output += "                        <li>\n";
output += "                            <a class=\"dropdown-item small text-info uppercase tracking-widest py-2\" href=\"" + escapeHtml(`/projects/${project.id}/videoeditor`) + "?active_scene=" + escapeHtml(scene.id) + "\">\n";
output += "                                <i class=\"bi bi-film me-2\"></i> Open Video Editor\n";
output += "                            </a>\n";
output += "                        </li>\n";
output += "                            \n";
output += "                            <li>\n";
output += "                                <a class=\"dropdown-item small text-white uppercase tracking-widest py-2\" href=\"" + escapeHtml(`/scenes/${scene.id}`) + "\">\n";
output += "                                    <i class=\"bi bi-sliders me-2\"></i> Reconfigure Params\n";
output += "                                </a>\n";
output += "                            </li>\n";
output += "                            \n";
output += "                            <li><hr class=\"dropdown-divider border-secondary\"></li>\n";
output += "                            \n";
output += "                            <li>\n";
output += "                                <form action=\"" + escapeHtml(`/scenes/${scene.id}/render`) + "\" method=\"POST\">\n";
output += "                                    <input type=\"hidden\" name=\"_csrf\" value=\"" + escapeHtml(csrfToken) + "\">\n";
output += "                                    <button type=\"submit\" class=\"dropdown-item small text-warning uppercase tracking-widest py-2 bg-transparent border-0 w-100 text-start\">\n";
output += "                                        <i class=\"bi bi-cpu-fill me-2\"></i> Regenerate Seed\n";
output += "                                    </button>\n";
output += "                                </form>\n";
output += "                            </li>\n";
output += "                            \n";
output += "                            <li>\n";
output += "                                <form action=\"" + escapeHtml(`/scenes/${scene.id}/delete`) + "\" method=\"POST\" data-studio-submit=\"1\">\n";
output += "                                    <input type=\"hidden\" name=\"_csrf\" value=\"" + escapeHtml(csrfToken) + "\">\n";
output += "                                    \n";
output += "                                    <button type=\"submit\" class=\"dropdown-item small text-danger uppercase tracking-widest py-2 bg-transparent border-0 w-100 text-start\">\n";
output += "                                        <i class=\"bi bi-trash3 me-2\"></i> Delete Segment\n";
output += "                                    </button>\n";
output += "                                </form>\n";
output += "                            </li>\n";
output += "                        </ul>\n";
output += "                    </div>\n";
output += "                </div>\n";
output += "\n";
output += "                <div class=\"row g-3\">\n";
output += "                    <div class=\"col-md-4 d-flex flex-column\">\n";
output += "                        <div class=\"ratio ratio-16x9 bg-dark border border-secondary d-flex align-items-center justify-content-center overflow-hidden mb-2\">\n";
if (scene.video_path) {
output += "                                <video src=\"" + escapeHtml(mediaUrl(scene.video_path)) + "\" controls class=\"w-100\"></video>\n";
} else {
output += "                                <div class=\"text-center\">\n";
output += "                                    <i class=\"bi bi-play-circle text-secondary fs-3\"></i>\n";
output += "                                    <p class=\"text-secondary mb-0 mt-2 tracking-widest uppercase\" style=\"font-size: 10px;\">AWAITING RENDER</p>\n";
output += "                                </div>\n";
}
output += "                        </div>\n";
output += "                        \n";
output += "                        <a href=\"" + escapeHtml(`/projects/${project.id}/videoeditor`) + "?active_scene=" + escapeHtml(scene.id) + "\" class=\"btn btn-outline-secondary btn-sm w-100 rounded-0 uppercase tracking-widest mt-auto\" style=\"font-size: 10px;\">\n";
output += "                            <i class=\"bi bi-film me-1\"></i> ADVANCED VIDEO EDIT\n";
output += "                        </a>\n";
output += "                    </div>\n";
output += "                    \n";
output += "                    <div class=\"col-md-8\">\n";
output += "                        <label class=\"text-secondary small tracking-widest mb-1 uppercase\" style=\"font-size: 10px;\">Script / AI Prompt</label>\n";
output += "                        \n";
output += "                        <form action=\"" + escapeHtml(`/scenes/${scene.id}`) + "\" method=\"POST\">\n";
output += "                            <input type=\"hidden\" name=\"_csrf\" value=\"" + escapeHtml(csrfToken) + "\">\n";
output += "                            \n";
output += "                            <input type=\"hidden\" name=\"project_id\" value=\"" + escapeHtml(project.id) + "\">\n";
output += "                            <input type=\"hidden\" name=\"order_index\" value=\"" + escapeHtml(scene.order_index) + "\">\n";
output += "                            <input type=\"hidden\" name=\"status\" value=\"" + escapeHtml(scene.status) + "\">\n";
output += "\n";
output += "                            <input type=\"hidden\" name=\"return_to_editor\" value=\"1\"><textarea name=\"script_segment\" class=\"form-control bg-transparent border-secondary text-info font-monospace small rounded-0 p-3 mb-2\" rows=\"3\" placeholder=\"Describe the movement and action here...\">" + escapeHtml(scene.script_segment) + "</textarea>\n";
output += "                            \n";
output += "                            <div class=\"d-flex gap-2\">\n";
output += "                                <button type=\"submit\" class=\"btn btn-outline-light btn-sm rounded-0 px-4 uppercase small tracking-widest\">SAVE</button>\n";
output += "                                \n";
output += "                            </div>\n";
output += "                        </form>\n";
output += "\n";
output += "                        <form action=\"" + escapeHtml(`/scenes/${scene.id}/render`) + "\" method=\"POST\" class=\"mt-2\">\n";
output += "                            <input type=\"hidden\" name=\"_csrf\" value=\"" + escapeHtml(csrfToken) + "\">\n";
output += "                            <button type=\"submit\" class=\"btn btn-outline-info w-100 btn-sm rounded-0 small uppercase tracking-widest fw-bold py-2\">\n";
output += "                                <i class=\"bi bi-cpu-fill me-1\"></i> RENDER WITH CONFIGURED COMFYUI\n";
output += "                            </button>\n";
output += "                        </form>\n";
if (scene.generation_job_id) {
output += "                            <form action=\"" + escapeHtml(`/scenes/${scene.id}/sync-render`) + "\" method=\"POST\" class=\"mt-2\">\n";
output += "                                <input type=\"hidden\" name=\"_csrf\" value=\"" + escapeHtml(csrfToken) + "\">\n";
output += "                                <button type=\"submit\" class=\"btn btn-outline-warning w-100 btn-sm rounded-0 small uppercase tracking-widest\">CHECK RENDER STATUS</button>\n";
output += "                            </form>\n";
}
output += localWanControls(scene, csrfToken);
if (scene.generation_error) {
output += "                            <small class=\"text-danger d-block mt-2\">" + escapeHtml(scene.generation_error) + "</small>\n";
}
output += "                    </div>\n";
output += "                </div>\n";
output += "            </div>\n";
}
output += "        </div>\n";
output += "\n";
output += "        <div class=\"col-md-3 bg-dark p-3\">\n";
output += "            <h6 class=\"text-secondary small tracking-widest mb-4 uppercase\">System Queue</h6>\n";
output += "            <div id=\"render-queue\" class=\"p-3 border border-secondary bg-black mb-4 d-flex align-items-center justify-content-center\" style=\"min-height: 200px;\">\n";
output += "                <div class=\"text-center text-secondary small italic\">\n";
output += "                    <div class=\"spinner-border spinner-border-sm mb-2 text-secondary\" role=\"status\"></div>\n";
output += "                    <br>Render jobs appear on each scene.\n";
output += "                </div>\n";
output += "            </div>\n";
output += "\n";
output += "            <h6 class=\"text-secondary small tracking-widest mb-3 uppercase\">Master Audio Layer</h6>\n";
output += "            <div class=\"p-3 border border-secondary bg-black d-flex align-items-center gap-2\">\n";
output += "                <i class=\"bi bi-waveform text-info fs-5\"></i>\n";
output += "                <div>\n";
output += "                    <span class=\"small d-block fw-bold tracking-widest uppercase\" style=\"font-size: 10px;\">No master audio attached</span>\n";
output += "                    <span class=\"small text-secondary font-monospace\" style=\"font-size: 10px;\">Add a soundtrack from the project overview</span>\n";
output += "                </div>\n";
output += "            </div>\n";
output += "        </div>\n";
output += "    </div>\n";
output += "</div>\n";
output += "\n";
output += "<link rel=\"stylesheet\" href=\"/public/studio/editor.css\">\n";
output += "\n";
output += "<script src=\"/public/studio/editor.js\" defer></script>\n";
output += "\n";
output += "<script src=\"/public/studio/bootstrap.bundle.min.js\"></script></body></html>";
return output.replace('</head>', '<script src="/public/studio/local-wan.js" defer></script><script src="/public/studio/kaggle.js" defer></script></head>');
}
