import { randomBytes } from "node:crypto";
import { mkdir, readFile, readdir, unlink, writeFile } from "node:fs/promises";
import path from "node:path";

const lifetime = 15 * 60 * 1000;
const tokenPattern = /^\d{13}-[a-f0-9]{64}$/;
const defaultRoot = () => path.resolve(process.cwd(), "storage", "kaggle-notebooks");

export async function createNotebookTransfer(notebook, root = defaultRoot(), now = Date.now()) {
  await mkdir(root, { recursive: true, mode: 0o700 });
  for (const name of await readdir(root)) {
    if (name.endsWith(".ipynb") && tokenPattern.test(name.slice(0, -6)) && Number(name.split("-")[0]) <= now) {
      await unlink(path.join(root, name)).catch(error => { if (error.code !== "ENOENT") throw error; });
    }
  }
  const token = `${now + lifetime}-${randomBytes(32).toString("hex")}`;
  await writeFile(path.join(root, `${token}.ipynb`), notebook, { mode: 0o600, flag: "wx" });
  return token;
}

export async function readNotebookTransfer(token, root = defaultRoot(), now = Date.now()) {
  if (!tokenPattern.test(token) || Number(token.split("-")[0]) <= now) return null;
  try { return await readFile(path.join(root, `${token}.ipynb`), "utf8"); }
  catch (error) { if (error.code === "ENOENT") return null; throw error; }
}
