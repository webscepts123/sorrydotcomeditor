from pathlib import Path

from huggingface_hub import hf_hub_download


CACHE = "/workspace/hf-cache"
FILES = [
    (
        "Comfy-Org/Wan_2.2_ComfyUI_Repackaged",
        "split_files/diffusion_models/wan2.2_ti2v_5B_fp16.safetensors",
        "/workspace/runpod-slim/ComfyUI/models/diffusion_models/wan2.2_ti2v_5B_fp16.safetensors",
    ),
    (
        "Comfy-Org/Wan_2.1_ComfyUI_repackaged",
        "split_files/text_encoders/umt5_xxl_fp8_e4m3fn_scaled.safetensors",
        "/workspace/runpod-slim/ComfyUI/models/text_encoders/umt5_xxl_fp8_e4m3fn_scaled.safetensors",
    ),
    (
        "Comfy-Org/Wan_2.2_ComfyUI_Repackaged",
        "split_files/vae/wan2.2_vae.safetensors",
        "/workspace/runpod-slim/ComfyUI/models/vae/wan2.2_vae.safetensors",
    ),
]


for repository, filename, destination in FILES:
    print(f"Downloading {filename}", flush=True)
    source = Path(hf_hub_download(repo_id=repository, filename=filename, cache_dir=CACHE))
    target = Path(destination)
    target.parent.mkdir(parents=True, exist_ok=True)
    if target.exists() or target.is_symlink():
        target.unlink()
    target.symlink_to(source)
    print(f"Ready: {target}", flush=True)
