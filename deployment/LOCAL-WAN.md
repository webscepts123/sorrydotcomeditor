# Local Wan 2.1 on D:

The additional local provider uses `http://127.0.0.1:8188`, independently of the existing `COMFYUI_URL` worker. It never sends local jobs to the remote worker. Model files, the isolated Python environment, and generated ComfyUI output live under `D:\AI`.

## This computer

Detected: NVIDIA GTX 970, 4 GB VRAM, approximately 16 GB system RAM. The official Wan 2.1 reference configuration for T2V 1.3B reports 8.19 GB VRAM. This installation uses CPU PyTorch and `--cpu --force-fp32`; GPU acceleration on this card is not verified or enabled. CPU generation is experimental, may need Windows paging, and can take a very long time. An available worker and installed models do not establish that a full generation will fit in memory or complete in practical time.

## Start and use

```powershell
npm.cmd run wan:start
```

Keep that process running. Open a project's **Video editor → AI Engine** tab, save the scene prompt, then use **Render with local Wan 2.1**. The availability check verifies that the worker has the required nodes and all three model files. Use **Check local render / import video** after rendering to import the WebM into the scene. Failed jobs show an error instead of remaining marked as rendering.

The default API workflow is `database/workflows/wan21-local-api.json`. It uses the 1.3B model, CPU text encoding, 256×256 pixels, 17 frames, 16 fps, and 10 sampling steps as a short compatibility test. These are reduced test settings, not a production quality preset. The original configured ComfyUI rendering option is still available.

## Installed paths

- ComfyUI: `D:\AI\ComfyUI`
- Python environment: `D:\AI\wan21-venv`
- Diffusion model: `D:\AI\ComfyUI\models\diffusion_models\wan2.1_t2v_1.3B_fp16.safetensors`
- Text encoder: `D:\AI\ComfyUI\models\text_encoders\umt5_xxl_fp8_e4m3fn_scaled.safetensors`
- VAE: `D:\AI\ComfyUI\models\vae\wan_2.1_vae.safetensors`
- Worker output: `D:\AI\ComfyUI\output`
- Imported editor media: `public/uploads/scenes/` (this project is also on D:)

To reproduce the runtime installation with Python 3.13 already installed:

```powershell
git clone --depth 1 https://github.com/Comfy-Org/ComfyUI.git D:/AI/ComfyUI
py -3.13 -m venv D:/AI/wan21-venv
D:/AI/wan21-venv/Scripts/python.exe -m pip install torch torchvision --index-url https://download.pytorch.org/whl/cpu --no-cache-dir
Get-Content D:/AI/ComfyUI/requirements.txt | Where-Object { $_ -notmatch '^comfyui-workflow-templates==' } | Set-Content D:/AI/requirements-runtime.txt
D:/AI/wan21-venv/Scripts/python.exe -m pip install -r D:/AI/requirements-runtime.txt --no-cache-dir
py -3.13 scripts/download-wan-models.py
```

The runtime installation omits ComfyUI's bundled workflow gallery media archives; they are not needed by the editor's API workflow. The downloader uses official Comfy-Org model files, verifies newly downloaded weights against the Hugging Face LFS SHA-256 metadata, and resumes partial downloads. Downloads are several GB. Restart the worker after adding models if its file lists are stale.

References: [Wan 2.1 official project](https://github.com/Wan-Video/Wan2.1), [ComfyUI Wan examples and model locations](https://comfyanonymous.github.io/ComfyUI_examples/wan/).
