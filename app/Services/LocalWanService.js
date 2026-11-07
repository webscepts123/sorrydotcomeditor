import { ComfyVideoService } from "./ComfyVideoService.js";

export class LocalWanService extends ComfyVideoService {
  constructor() {
    super({
      baseUrl: process.env.WAN_LOCAL_URL || "http://127.0.0.1:8188",
      workflowPath: process.env.WAN_LOCAL_WORKFLOW || "database/workflows/wan21-local-api.json"
    });
    const url = new URL(this.baseUrl);
    if (!["127.0.0.1", "localhost", "[::1]"].includes(url.hostname)) {
      throw new Error("WAN_LOCAL_URL must point to this machine's loopback address.");
    }
  }

  async readiness() {
    const status = await this.status();
    const response = await fetch(`${this.baseUrl}/object_info`, { signal: AbortSignal.timeout(10000) });
    if (!response.ok) throw new Error("Could not inspect the local ComfyUI models.");
    const nodes = await response.json();
    const requirements = [
      ["UNETLoader", "unet_name", "wan2.1_t2v_1.3B_fp16.safetensors"],
      ["CLIPLoader", "clip_name", "umt5_xxl_fp8_e4m3fn_scaled.safetensors"],
      ["VAELoader", "vae_name", "wan_2.1_vae.safetensors"]
    ];
    const missing = requirements.filter(([node, input, file]) =>
      !nodes[node]?.input?.required?.[input]?.[0]?.includes(file)
    ).map(([, , file]) => file);
    for (const node of ["KSampler", "CLIPTextEncode", "EmptyHunyuanLatentVideo", "ModelSamplingSD3", "VAEDecode", "SaveWEBM"]) {
      if (!nodes[node]) missing.push(`ComfyUI node: ${node}`);
    }
    const cpu = String(status.gpu_name).toLowerCase().includes("cpu");
    const freeRam = typeof status.ram_total_gb === "number" && typeof status.ram_used_gb === "number"
      ? Math.round((status.ram_total_gb - status.ram_used_gb) * 10) / 10 : null;
    const message = missing.length ? `Missing: ${missing.join(", ")}`
      : cpu ? `Local Wan models are installed. CPU only; ${freeRam === null ? "available RAM is unknown" : `${freeRam} GB RAM is free`}. Generation is experimental, can be extremely slow, and may run out of memory.`
      : "Wan 2.1 1.3B models are available on the local GPU worker. Full generation has not been benchmarked.";
    return {
      ...status, ready: missing.length === 0, missing,
      mode: cpu ? "cpu" : "gpu", ram_free_gb: freeRam, message
    };
  }

  async submitScene(scene, cast = []) {
    const status = await this.readiness();
    if (!status.ready) throw new Error(status.message);
    return super.submitScene(scene, cast);
  }
}
