"""Build the importable Kaggle notebook without executing cloud inference."""
import json
from pathlib import Path
import textwrap

cells = []
def markdown(source):
    cells.append({"cell_type": "markdown", "metadata": {}, "source": textwrap.dedent(source).strip().splitlines(keepends=True)})
def code(source):
    cells.append({"cell_type": "code", "execution_count": None, "metadata": {}, "outputs": [], "source": textwrap.dedent(source).strip().splitlines(keepends=True)})

markdown('''
# Wan 2.1 on Kaggle — sorrydotcomeditor

Generate a short video using Kaggle's GPU, then download the MP4 and import it into your local editor. Your GTX 970 is not used.

1. Import this `.ipynb` into a new Kaggle notebook.
2. In notebook settings enable **Internet** and choose an available **GPU** accelerator (T4 or P100 is suitable for this notebook's checks).
3. Run the cells in order. Edit the prompt in **Scene settings** first.
4. Download the MP4 from the final cell or notebook Output panel before ending the session.

Free GPU access depends on your account's quota and availability. This notebook does not create a public server or connect Kaggle to your local database. No paid inference API or API key is required for the public Wan model.

Optional: export a scene JSON from your editor's **AI Engine → Kaggle** panel, add it as a **private** notebook input, and set `JOB_FILE` below. Only upload the scene data you want processed on Kaggle.
''')
code('''
import os
from pathlib import Path
import shutil
import torch
import psutil

if not Path("/kaggle/working").is_dir():
    raise RuntimeError("Import and run this notebook inside Kaggle, with a GPU accelerator enabled.")
if not torch.cuda.is_available():
    raise RuntimeError("No CUDA GPU. Enable an available GPU in Kaggle notebook settings, then restart the session.")
gpu = torch.cuda.get_device_properties(0)
vram_gb = gpu.total_memory / 1024**3
ram_gb = psutil.virtual_memory().total / 1024**3
disk_gb = shutil.disk_usage("/kaggle/working").free / 1024**3
print(f"GPU: {gpu.name}; VRAM: {vram_gb:.1f} GiB; system RAM: {ram_gb:.1f} GiB; disk free: {disk_gb:.1f} GiB")
print(f"GPU count: {torch.cuda.device_count()}. This notebook uses GPU 0; multiple GPUs do not combine VRAM automatically.")
if vram_gb < 12:
    raise RuntimeError("This notebook targets a GPU with at least 12 GiB VRAM. Select a larger available accelerator.")
if ram_gb < 24:
    raise RuntimeError("This two-stage workflow needs at least 24 GiB system RAM for CPU text encoding. Select a larger notebook session.")
if disk_gb < 25:
    raise RuntimeError("Free at least 25 GiB of notebook disk space for model downloads and output.")
os.environ["HF_HOME"] = "/kaggle/working/.cache/huggingface"
os.environ["TOKENIZERS_PARALLELISM"] = "false"
os.environ["USE_TF"] = "0"
os.environ["USE_FLAX"] = "0"
os.chdir("/kaggle/working")
''')
markdown('''
## Install notebook libraries

Keep Kaggle's supplied CUDA/PyTorch installation. This installs pinned Diffusers and Transformers versions for the API used below. If Kaggle asks for a session restart, restart, rerun the GPU check, then continue. First-time model downloads are several GB and require Internet access.
''')
code('''
import subprocess
import sys

subprocess.check_call([
    sys.executable, "-m", "pip", "install", "--quiet",
    "diffusers==0.35.2", "transformers==4.51.3", "accelerate==1.7.0",
    "huggingface-hub>=0.34,<1", "safetensors>=0.4.3,<1",
    "sentencepiece", "ftfy", "imageio", "imageio-ffmpeg", "psutil"
])
print("Libraries installed. Continue to scene settings.")
''')
markdown('''
## Scene settings

The default `1080p` preset generates a 480p clip, then upscales it to a **1920×1080 MP4** with FFmpeg Lanczos scaling. This is an upscaled export, not native 1080p generation or AI detail restoration. It preserves the image proportions and pads the edges as needed. Use `quick` for a shorter compatibility test. These presets do not guarantee a completion time or that every GPU session will have enough free memory.

For an exported editor scene, add its JSON as a private Kaggle input and set `JOB_FILE` to the uploaded file's path. Otherwise edit `PROMPT` and optionally `SCENE_ID` directly. The scene number makes importing the finished video easier.
''')
code('''
import json

MODEL_ID = "Wan-AI/Wan2.1-T2V-1.3B-Diffusers"
JOB_FILE = ""  # Example: /kaggle/input/my-private-scene/scene-1-wan-job.json
SCENE_ID = None  # Replace with your editor's scene ID, if known.
PROMPT = "A cinematic shot of a quiet railway platform at sunrise, soft golden light, a woman waits beside a vintage train, natural human movement, slow camera dolly forward."
NEGATIVE_PROMPT = "low quality, blurry, distorted faces, extra limbs, cartoon, watermark, text, subtitles"
PRESET = "1080p"  # quick, 480p, or 1080p (480p generation + upscale)
SEED = 42

if JOB_FILE:
    job_path = Path(JOB_FILE)
    if job_path.stat().st_size > 128 * 1024:
        raise ValueError("Scene JSON is too large.")
    job = json.loads(job_path.read_text(encoding="utf-8-sig"))
    if not isinstance(job, dict) or job.get("format") != "sorrydotcomeditor.wan-kaggle.v1":
        raise ValueError("Select a scene JSON exported by this editor.")
    SCENE_ID = job.get("scene_id")
    PROMPT = job.get("prompt", "")
    NEGATIVE_PROMPT = job.get("negative_prompt", NEGATIVE_PROMPT)
    PRESET = job.get("preset", "1080p")
    SEED = job.get("seed", 42)

if not isinstance(PROMPT, str) or not PROMPT.strip() or len(PROMPT) > 12000:
    raise ValueError("Enter a scene prompt between 1 and 12000 characters.")
if not isinstance(NEGATIVE_PROMPT, str) or len(NEGATIVE_PROMPT) > 4000:
    raise ValueError("Invalid negative prompt.")
if SCENE_ID is not None and (type(SCENE_ID) is not int or SCENE_ID <= 0):
    raise ValueError("SCENE_ID must be a positive integer or None.")
if type(SEED) is not int or not 0 <= SEED < 2**32:
    raise ValueError("SEED must be an integer from 0 to 4294967295.")
PRESETS = {
    "quick": {"width": 480, "height": 272, "num_frames": 33, "num_inference_steps": 20},
    "480p": {"width": 832, "height": 480, "num_frames": 81, "num_inference_steps": 30},
    "1080p": {"width": 832, "height": 480, "num_frames": 81, "num_inference_steps": 30},
}
if PRESET not in PRESETS:
    raise ValueError("PRESET must be quick, 480p, or 1080p.")
settings = PRESETS[PRESET].copy()
UPSCALE_1080P = PRESET == "1080p"
FPS = 16
print(f"Scene: {SCENE_ID}; preset: {PRESET}; clip length: {settings['num_frames'] / FPS:.2f} seconds; seed: {SEED}")
print(PROMPT)
''')
markdown('''
## Encode the prompt on the CPU

This stage uses FP32 for the T5 text encoder, avoiding reliance on BF16 support on older notebook GPUs. It can use substantial system RAM. The encoder is freed before loading the video transformer; the large text encoder and video model are not kept in memory together.
''')
code('''
import gc
from diffusers import WanPipeline
from transformers import UMT5EncoderModel

# Clear a previous run before encoding a new scene.
if "pipe" in globals():
    del pipe
gc.collect()
torch.cuda.empty_cache()
try:
    encoder = UMT5EncoderModel.from_pretrained(
        MODEL_ID, subfolder="text_encoder", torch_dtype=torch.float32,
        low_cpu_mem_usage=True, use_safetensors=True,
    ).eval()
    prompt_pipe = WanPipeline.from_pretrained(
        MODEL_ID, text_encoder=encoder, transformer=None, vae=None,
        torch_dtype=torch.float32,
    )
    with torch.inference_mode():
        prompt_embeds, negative_prompt_embeds = prompt_pipe.encode_prompt(
            prompt=PROMPT, negative_prompt=NEGATIVE_PROMPT,
            do_classifier_free_guidance=True, device=torch.device("cpu"),
            dtype=torch.float32, max_sequence_length=256,
        )
    if not torch.isfinite(prompt_embeds).all() or not torch.isfinite(negative_prompt_embeds).all():
        raise RuntimeError("Prompt encoding produced invalid values. Stop and restart the session.")
finally:
    if "prompt_pipe" in globals():
        del prompt_pipe
    if "encoder" in globals():
        del encoder
    gc.collect()
    torch.cuda.empty_cache()
print("Prompt embeddings prepared; text encoder released.")
''')
markdown('''
## Load the video model and generate

The transformer uses BF16 on a compatible GPU and FP16 otherwise. The VAE stays FP32. CPU offloading and VAE tiling reduce GPU memory use. If an out-of-memory error occurs, restart the notebook session and try the `quick` preset. Do not assume a failed session produced a video.
''')
code('''
import time
from diffusers import AutoencoderKLWan
from diffusers.schedulers.scheduling_unipc_multistep import UniPCMultistepScheduler
from diffusers.utils import export_to_video

dtype = torch.bfloat16 if torch.cuda.is_bf16_supported() else torch.float16
vae = AutoencoderKLWan.from_pretrained(
    MODEL_ID, subfolder="vae", torch_dtype=torch.float32,
    low_cpu_mem_usage=True, use_safetensors=True,
)
pipe = WanPipeline.from_pretrained(
    MODEL_ID, text_encoder=None, vae=vae, torch_dtype=dtype,
    low_cpu_mem_usage=True, use_safetensors=True,
)
del vae
pipe.scheduler = UniPCMultistepScheduler.from_config(pipe.scheduler.config, flow_shift=3.0)
pipe.vae.enable_tiling()
pipe.enable_model_cpu_offload(gpu_id=0)

started = time.monotonic()
try:
    with torch.inference_mode():
        frames = pipe(
            prompt_embeds=prompt_embeds,
            negative_prompt_embeds=negative_prompt_embeds,
            guidance_scale=5.0,
            generator=torch.Generator(device="cpu").manual_seed(SEED),
            **settings,
        ).frames[0]
except torch.OutOfMemoryError:
    print("Out of GPU memory. Restart the session and try the quick preset.")
    raise
print(f"Generated {len(frames)} frames in {time.monotonic() - started:.1f} seconds.")
''')
markdown('''
## Save, preview, and download

Both the video and a small generation manifest are saved to `/kaggle/working/wan-output`. The manifest records the scene ID and settings; it does not contain credentials. Download the MP4 before your session ends.

For `1080p`, FFmpeg scales the completed 480p video on the CPU, streaming frames to avoid keeping a full 1080p clip in memory. Both the source and final export are retained. Upscaling increases pixel dimensions; it does not recover native 1080p detail.
''')
code('''
from datetime import datetime, timezone
from IPython.display import display, FileLink, Video
import importlib.metadata
import numpy as np
import imageio_ffmpeg

if len(frames) != settings["num_frames"] or not np.isfinite(frames).all():
    raise RuntimeError("Invalid generated frames. No MP4 was exported.")
output_dir = Path("/kaggle/working/wan-output")
output_dir.mkdir(parents=True, exist_ok=True)
stamp = datetime.now(timezone.utc).strftime("%Y%m%d-%H%M%S")
label = f"scene-{SCENE_ID}" if SCENE_ID is not None else "preview"
source_path = output_dir / f"{label}-wan21-{stamp}-source.mp4"
export_to_video(frames, str(source_path), fps=FPS)
video_path = source_path
if UPSCALE_1080P:
    video_path = output_dir / f"{label}-wan21-{stamp}-1080p.mp4"
    subprocess.run([
        imageio_ffmpeg.get_ffmpeg_exe(), "-hide_banner", "-loglevel", "error", "-y",
        "-i", str(source_path),
        "-vf", "scale=1920:1080:force_original_aspect_ratio=decrease:flags=lanczos,pad=1920:1080:(ow-iw)/2:(oh-ih)/2,setsar=1",
        "-c:v", "libx264", "-preset", "medium", "-crf", "18", "-pix_fmt", "yuv420p",
        "-movflags", "+faststart", "-an", str(video_path),
    ], check=True)
if not video_path.is_file() or video_path.stat().st_size == 0:
    raise RuntimeError("MP4 export failed.")
probe = imageio_ffmpeg.read_frames(str(video_path))
try:
    output_metadata = next(probe)
finally:
    probe.close()
if UPSCALE_1080P and tuple(output_metadata["size"]) != (1920, 1080):
    raise RuntimeError("Export did not produce 1920x1080 video.")
manifest = {
    "format": "sorrydotcomeditor.wan-kaggle-result.v1",
    "scene_id": SCENE_ID, "model": MODEL_ID, "preset": PRESET,
    "prompt": PROMPT, "negative_prompt": NEGATIVE_PROMPT, "seed": SEED,
    "fps": FPS, **settings, "gpu": gpu.name, "created_at": stamp,
    "video_file": video_path.name,
    "source_file": source_path.name,
    "output_width": output_metadata["size"][0], "output_height": output_metadata["size"][1],
    "upscaled": UPSCALE_1080P, "upscale_method": "FFmpeg Lanczos + aspect-preserving padding" if UPSCALE_1080P else None,
    "versions": {package: importlib.metadata.version(package) for package in ["torch", "diffusers", "transformers", "accelerate"]},
}
video_path.with_suffix(".json").write_text(json.dumps(manifest, indent=2), encoding="utf-8")
display(Video(str(video_path), embed=True, width=640))
display(FileLink(str(video_path.relative_to("/kaggle/working"))))
display(FileLink(str(video_path.with_suffix(".json").relative_to("/kaggle/working"))))
if UPSCALE_1080P:
    print("1920x1080 upscaled export ready. The original generated clip is also available:")
    display(FileLink(str(source_path.relative_to("/kaggle/working"))))
print("You can also download these files from Kaggle's Output panel.")
if SCENE_ID is not None:
    print("After downloading the MP4, run this from your local editor project:")
    print(f'npm run kaggle:import -- --scene {SCENE_ID} --file "D:/Downloads/{video_path.name}"')
''')
markdown('''
## Import into sorrydotcomeditor

On your Windows PC, open a terminal in the editor project. Run:

```powershell
npm.cmd run kaggle:import -- --scene YOUR_SCENE_ID --file "D:\\Downloads\\YOUR_VIDEO.mp4"
```

Replace the scene ID and file path with your actual values. The command copies the MP4 into the editor's media directory and attaches it to that scene. It keeps the downloaded original. Refresh the project's video editor to preview it.

Stop the Kaggle GPU session when you finish. Kaggle quota, GPU availability, and session duration depend on your account. This notebook has been checked locally for structure and code, but cloud inference must be verified in your own Kaggle session.

References: [Wan model](https://huggingface.co/Wan-AI/Wan2.1-T2V-1.3B-Diffusers), [Diffusers Wan pipeline](https://huggingface.co/docs/diffusers/api/pipelines/wan), [Kaggle notebooks](https://www.kaggle.com/docs/notebooks).
''')

notebook = {
    "cells": cells,
    "metadata": {
        "kernelspec": {"display_name": "Python 3", "language": "python", "name": "python3"},
        "language_info": {"name": "python", "version": "3.11"},
        "kaggle": {"accelerator": "gpu", "isInternetEnabled": True, "language": "python", "sourceType": "notebook"},
    },
    "nbformat": 4, "nbformat_minor": 4,
}
target = Path(__file__).resolve().parent.parent / "notebooks" / "wan21-kaggle.ipynb"
target.parent.mkdir(exist_ok=True)
target.write_text(json.dumps(notebook, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
print(target)
