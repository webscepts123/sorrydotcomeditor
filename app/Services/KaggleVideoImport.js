import { constants } from "node:fs";
import { copyFile, mkdir, open, stat, unlink } from "node:fs/promises";
import path from "node:path";
import { randomUUID } from "node:crypto";

export async function importKaggleVideo(database, { sceneId, source, publicRoot = path.resolve("public"), expectedJobId = null }) {
  if (!Number.isSafeInteger(sceneId) || sceneId <= 0) throw new Error("Supply a positive scene ID with --scene.");
  if (!source || path.extname(source).toLowerCase() !== ".mp4") throw new Error("Select the downloaded Kaggle .mp4 file with --file.");
  const sourcePath = path.resolve(source);
  const info = await stat(sourcePath);
  if (!info.isFile() || info.size < 12 || info.size > 512 * 1024 ** 2) throw new Error("The MP4 must be a regular file between 12 bytes and 512 MB.");
  const handle = await open(sourcePath, "r");
  try {
    const header = Buffer.alloc(12);
    await handle.read(header, 0, 12, 0);
    if (header.toString("ascii", 4, 8) !== "ftyp") throw new Error("The selected file does not have an MP4 file header.");
  } finally { await handle.close(); }

  const relative = `uploads/scenes/kaggle-${sceneId}-${randomUUID()}.mp4`;
  const destination = path.join(publicRoot, relative);
  await mkdir(path.dirname(destination), { recursive: true });
  await copyFile(sourcePath, destination, constants.COPYFILE_EXCL);
  try {
    const scene = await database.transaction(async connection => {
      const [rows] = await connection.execute("SELECT id, project_id, status, generation_job_id FROM scenes WHERE id = ? FOR UPDATE", [sceneId]);
      if (!rows.length) throw new Error(`Scene ${sceneId} does not exist.`);
      if (expectedJobId ? rows[0].generation_job_id !== expectedJobId : ["Rendering", "Processing"].includes(rows[0].status)) throw new Error("This scene has a running generation job. Wait for it to finish before importing a video.");
      await connection.execute("UPDATE scenes SET video_path = ?, status = 'Ready', generation_job_id = NULL, generation_error = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [relative, sceneId]);
      await connection.execute("UPDATE projects SET completed_clips = (SELECT COUNT(*) FROM scenes WHERE project_id = ? AND video_path IS NOT NULL AND video_path <> ''), updated_at = CURRENT_TIMESTAMP WHERE id = ?", [rows[0].project_id, rows[0].project_id]);
      return rows[0];
    });
    return { scene_id: sceneId, project_id: scene.project_id, video_path: relative, editor_url: `/projects/${scene.project_id}/videoeditor?scene_id=${sceneId}` };
  } catch (error) {
    await unlink(destination).catch(() => {});
    throw error;
  }
}
