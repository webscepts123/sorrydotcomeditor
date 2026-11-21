import { mkdir, open, readFile, readdir, rm, rmdir, stat, writeFile } from "node:fs/promises";
import path from "node:path";
import { importKaggleVideo } from "./KaggleVideoImport.js";

export const maxSceneUpload = 64 * 1024 ** 2;
const maxChunk = 128 * 1024;
const uploadId = /^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/;

async function readOptional(file) {
  try { return JSON.parse(await readFile(file, "utf8")); }
  catch (error) { if (error.code === "ENOENT") return null; throw error; }
}

export async function uploadSceneVideo(db, userId, scene, body, { root = path.resolve("storage/scene-uploads"), publicRoot } = {}) {
  const { id, offset, size, data } = body ?? {};
  if (!uploadId.test(id ?? "") || !Number.isSafeInteger(offset) || offset < 0
    || !Number.isSafeInteger(size) || size < 12 || size > maxSceneUpload
    || typeof data !== "string" || data.length > 4 * Math.ceil(maxChunk / 3)) {
    throw new Error("Select an MP4 up to 64 MB.");
  }
  const chunk = Buffer.from(data, "base64");
  if (!chunk.length || chunk.length > maxChunk || offset + chunk.length > size || chunk.toString("base64") !== data) {
    throw new Error("Invalid video upload chunk.");
  }
  const folder = path.join(root, String(Number(userId)), String(Number(scene.id)));
  const directory = path.join(folder, id);
  if (offset === 0) {
    await mkdir(folder, { recursive: true, mode: 0o700 });
    for (const entry of await readdir(folder)) {
      if (!uploadId.test(entry) || entry === id) continue;
      const old = path.join(folder, entry);
      const info = await stat(old).catch(() => null);
      if (info && Date.now() - info.mtimeMs > 2 * 60 * 60 * 1000) await rm(old, { recursive: true, force: true });
    }
    await mkdir(directory, { recursive: true, mode: 0o700 });
  }
  const lock = path.join(directory, "lock");
  try { await mkdir(lock); }
  catch (error) {
    if (error.code === "ENOENT") throw new Error("Upload expired. Select the file again.");
    if (error.code === "EEXIST") throw new Error("Another request is processing this upload. Try again.");
    throw error;
  }
  try {
    const receipt = path.join(directory, "receipt.json");
    const saved = await readOptional(receipt);
    if (saved) return saved;
    const manifest = path.join(directory, "size.json");
    const previous = await readOptional(manifest);
    if (previous === null && offset === 0) await writeFile(manifest, JSON.stringify(size), { mode: 0o600 });
    else if (previous !== size) throw new Error("The upload file changed. Select the file again.");
    const source = path.join(directory, "clip.mp4");
    const handle = await open(source, offset === 0 ? "a+" : "r+", 0o600);
    try {
      const info = await handle.stat();
      if (info.size === offset) {
        for (let written = 0; written < chunk.length;) {
          const { bytesWritten } = await handle.write(chunk, written, chunk.length - written, offset + written);
          if (!bytesWritten) throw new Error("The video chunk could not be saved.");
          written += bytesWritten;
        }
      } else if (info.size === offset + chunk.length) {
        const duplicate = Buffer.alloc(chunk.length);
        await handle.read(duplicate, 0, duplicate.length, offset);
        if (!duplicate.equals(chunk)) throw new Error("The upload file changed. Select the file again.");
      } else {
        throw new Error("Upload chunks arrived out of order. Select the file again.");
      }
    } finally { await handle.close(); }
    const received = offset + chunk.length;
    if (received < size) return { received };
    await importKaggleVideo(db, { sceneId: Number(scene.id), source, uploaded: true, publicRoot });
    const result = { received, ready: true };
    await writeFile(receipt, JSON.stringify(result), { mode: 0o600 });
    await rm(source, { force: true });
    return result;
  } finally {
    await rmdir(lock);
  }
}
