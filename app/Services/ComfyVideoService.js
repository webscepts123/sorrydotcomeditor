import { randomBytes } from "node:crypto";
import { mkdir, readFile, writeFile } from "node:fs/promises";
import path from "node:path";

function replaceTokens(value, tokens) {
  if (Array.isArray(value)) return value.map((item) => replaceTokens(item, tokens));
  if (value && typeof value === "object") {
    return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, replaceTokens(item, tokens)]));
  }
  if (typeof value !== "string") return value;
  return Object.entries(tokens).reduce((result, [token, replacement]) => result.replaceAll(token, replacement), value);
}

async function responseJson(response, message) {
  const body = await response.text();
  let value;
  try { value = JSON.parse(body); } catch { value = null; }
  if (!response.ok) throw new Error(value?.error?.message ?? value?.error ?? body ?? message);
  return value;
}

export class ComfyVideoService {
  constructor({ baseUrl = process.env.COMFYUI_URL, workflowPath = process.env.COMFYUI_VIDEO_WORKFLOW } = {}) {
    this.baseUrl = String(baseUrl ?? "").replace(/\/+$/, "");
    this.workflowPath = path.resolve(process.cwd(), workflowPath || "storage/workflows/wan_video_api.json");
    if (!this.baseUrl) throw new Error("Set COMFYUI_URL to enable ComfyUI rendering.");
  }

  async submitScene(scene, cast = []) {
    const raw = await readFile(this.workflowPath, "utf8").catch((error) => {
      if (error.code === "ENOENT") throw new Error(`ComfyUI workflow not found: ${this.workflowPath}`);
      throw error;
    });
    let workflow;
    try { workflow = JSON.parse(raw); } catch (error) {
      throw new Error(`ComfyUI workflow is not valid JSON: ${error.message}`);
    }

    workflow = replaceTokens(workflow, {
      "{{PROMPT}}": [
        "Create a photorealistic live-action cinematic cutscene with natural movement, film lighting and professional camera work.",
        `Scene action: ${scene.script_segment ?? ""}`,
        cast.length ? `Cast continuity:\n${cast.map((character) => `${character.name} (${character.role ?? "supporting"}): ${character.description ?? ""}\n${character.personality ?? ""}`).join("\n\n")}` : "",
        "No animation, cartoons, illustrations, game graphics, subtitles, logos, or watermarks."
      ].filter(Boolean).join("\n\n"),
      "{{NEGATIVE_PROMPT}}": "cartoon, anime, illustration, low quality, blurry, distorted faces, extra limbs, watermark, text"
    });

    for (const node of Object.values(workflow)) {
      if (node?.class_type === "KSampler" && node.inputs) node.inputs.seed = randomBytes(4).readUInt32BE();
    }
    const response = await fetch(`${this.baseUrl}/prompt`, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ prompt: workflow, client_id: `sorrydotcomeditor-${randomBytes(8).toString("hex")}` }),
      signal: AbortSignal.timeout(20000)
    });
    const result = await responseJson(response, "ComfyUI rejected the workflow.");
    if (!result?.prompt_id) throw new Error("ComfyUI did not return a prompt ID.");
    return result.prompt_id;
  }

  async downloadCompletedVideo(promptId) {
    const historyResponse = await fetch(`${this.baseUrl}/history/${encodeURIComponent(promptId)}`, {
      signal: AbortSignal.timeout(20000)
    });
    const history = await responseJson(historyResponse, "Could not read the ComfyUI job status.");
    const job = history?.[promptId];
    if (!job) return null;
    if (job.status?.status_str === "error") throw new Error("ComfyUI reported a failed generation job.");

    for (const output of Object.values(job.outputs ?? {})) {
      for (const type of ["videos", "gifs", "images"]) {
        for (const file of output[type] ?? []) {
          if (!file.filename || !/\.(mp4|webm|mov|m4v)$/i.test(file.filename)) continue;
          const query = new URLSearchParams({
            filename: file.filename,
            subfolder: file.subfolder ?? "",
            type: file.type ?? "output"
          });
          const download = await fetch(`${this.baseUrl}/view?${query}`, { signal: AbortSignal.timeout(120000) });
          if (!download.ok) continue;
          const length = Number(download.headers.get("content-length") ?? 0);
          if (length > 512 * 1024 * 1024) throw new Error("ComfyUI output exceeds the 512 MB download limit.");
          const data = Buffer.from(await download.arrayBuffer());
          if (data.length > 512 * 1024 * 1024) throw new Error("ComfyUI output exceeds the 512 MB download limit.");
          const extension = path.extname(file.filename).replace(/[^.a-z0-9]/gi, "") || ".mp4";
          const directory = path.resolve(process.cwd(), "public", "uploads", "scenes");
          await mkdir(directory, { recursive: true });
          const name = `comfy-${String(promptId).replace(/[^a-z0-9_-]/gi, "")}${extension}`;
          await writeFile(path.join(directory, name), data, { flag: "wx" }).catch(async (error) => {
            if (error.code !== "EEXIST") throw error;
            await writeFile(path.join(directory, name), data);
          });
          return `uploads/scenes/${name}`;
        }
      }
    }

    if (job.status?.status_str === "error") throw new Error("ComfyUI reported a failed generation job.");
    return null;
  }

  async status() {
    const systemResponse = await fetch(`${this.baseUrl}/system_stats`, { signal: AbortSignal.timeout(8000) });
    const system = await responseJson(systemResponse, "ComfyUI system status request failed.");
    if (!system || typeof system !== "object") throw new Error("ComfyUI returned an invalid system status.");
    let queue;
    try {
      const queueResponse = await fetch(`${this.baseUrl}/queue`, { signal: AbortSignal.timeout(8000) });
      queue = await responseJson(queueResponse, "ComfyUI queue request failed.");
    } catch (error) {
      console.warn("ComfyUI queue status unavailable:", error.message);
    }
    const device = system.devices?.[0] ?? {};
    const systemInfo = system.system ?? {};
    const memory = (total, free) => {
      if (typeof total !== "number" || typeof free !== "number" || !Number.isFinite(total) ||
          !Number.isFinite(free) || total <= 0 || free < 0 || free > total) return null;
      const used = total - free;
      return {
        percent: Math.round(used * 1000 / total) / 10,
        used: Math.round(used / 1024 ** 3 * 100) / 100,
        total: Math.round(total / 1024 ** 3 * 100) / 100
      };
    };
    const ram = memory(systemInfo.ram_total, systemInfo.ram_free);
    const vram = memory(device.vram_total, device.vram_free);
    return {
      online: true,
      gpu_name: device.name ?? null,
      comfyui_version: systemInfo.comfyui_version ?? null,
      ram_percent: ram?.percent ?? null,
      vram_percent: vram?.percent ?? null,
      ram_used_gb: ram?.used ?? null,
      ram_total_gb: ram?.total ?? null,
      vram_used_gb: vram?.used ?? null,
      vram_total_gb: vram?.total ?? null,
      running_jobs: Array.isArray(queue?.queue_running) ? queue.queue_running.length : null,
      pending_jobs: Array.isArray(queue?.queue_pending) ? queue.queue_pending.length : null
    };
  }
}
