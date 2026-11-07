import test from "node:test";
import assert from "node:assert/strict";
import { mkdtemp, mkdir, writeFile, rm } from "node:fs/promises";
import { tmpdir } from "node:os";
import path from "node:path";
import { mediaUrl } from "../app/Views/studio-helpers.js";

test("media URLs resolve preserved Laravel files and new uploads safely", async () => {
  const root = await mkdtemp(path.join(tmpdir(), "studio-media-"));
  try {
    await mkdir(path.join(root, "storage", "characters"), { recursive: true });
    await mkdir(path.join(root, "uploads"), { recursive: true });
    await writeFile(path.join(root, "storage", "characters", "existing.png"), "image");
    await writeFile(path.join(root, "uploads", "new.png"), "image");
    assert.equal(mediaUrl("characters/existing.png", root), "/public/storage/characters/existing.png");
    assert.equal(mediaUrl("uploads/new.png", root), "/public/uploads/new.png");
    assert.equal(mediaUrl("/public/storage/characters/existing.png", root), "/public/storage/characters/existing.png");
    assert.equal(mediaUrl("../.env", root), "");
    assert.equal(mediaUrl("", root), "");
  } finally {
    await rm(root, { recursive: true, force: true });
  }
});
