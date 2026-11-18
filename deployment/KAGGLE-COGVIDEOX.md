# CogVideoX-5B in Kaggle Notebooks

This workflow runs video generation in your Kaggle GPU notebook and imports the MP4 into the editor on your Windows PC. Your local GTX 970 is not used for generation. It does not require a public tunnel or a connection from Kaggle to your local MySQL database.

## 1. Import the notebook

The file is [`notebooks/cogvideox-kaggle.ipynb`](../notebooks/cogvideox-kaggle.ipynb). You can also download it from a scene's **Video editor → AI Engine → COGVIDEOX-5B — KAGGLE GPU** panel.

1. Sign in at [Kaggle Notebooks](https://www.kaggle.com/code).
2. Create a new notebook, then use **File → Import Notebook** and upload `cogvideox-kaggle.ipynb` (menu wording may vary).
3. Enable **Internet** and select an available **GPU accelerator** in notebook settings. The notebook uses GPU 0; T4 x2 does not automatically behave like one GPU with combined memory.
4. Run the GPU-check cell. This notebook targets at least 12 GiB GPU memory, 24 GiB system RAM, 30 GiB free temporary disk (the ~21 GB model is cached in `/tmp`, outside the size-capped `/kaggle/working`), and 1 GiB free output disk. These are checks for this implementation, not universal minimum requirements for CogVideoX.

If GPU selection is unavailable, check your Kaggle quota, account verification, and accelerator availability in Kaggle. Signing in alone does not guarantee a GPU session. No Kaggle API credential is needed for this manual import workflow.

## 2. Choose your scene and run

In the editor's **AI Engine → Kaggle** panel, enter your **Video prompt** and click **Save video prompt**. Describe the subject, action, setting, camera movement, and lighting. Click **Open scene in Kaggle · 1080p** to open Kaggle directly with your saved prompt and scene ID included. Sign in, enable GPU and Internet, and run the cells in order. Download the final `-1080p.mp4`, rather than the lower-resolution source file. **Download notebook instead** remains available as a fallback.

Direct opening uses Kaggle's `kernels/welcome?src=` notebook import. A random private import link allows Kaggle to fetch the notebook without your editor session and expires after 15 minutes. Expired snapshots are removed when a new transfer is created. The link contains only the exported notebook, not database credentials. Keep the imported Kaggle notebook private. If sign-in takes too long, open the scene in Kaggle again from the editor. This opens a notebook; running inference still requires your Kaggle session and GPU quota.

The scene notebook uses `PRESET = "1080p"`, generating at 720×480 and then upscaling to a verified 1920×1080 MP4. This is not native 1080p generation. The generic notebook still lets you edit `PROMPT` and `SCENE_ID` in **Scene settings** or choose `quick` for a shorter compatibility test.

Alternatively, use **Export this scene for Kaggle** in the editor. Add the downloaded JSON as a private notebook input, set `JOB_FILE` to its `/kaggle/input/...` path, and rerun from Scene settings. That export contains the saved scene script, visual style, and assigned cast descriptions; save changes before exporting.

The notebook loads the full CogVideoX-5B pipeline (T5 text encoder, 5B transformer, VAE). It uses BF16 only on GPUs with native BF16 support (compute capability 8.0+), otherwise FP16, so T4/P100 run in FP16. Below 20 GiB VRAM it uses sequential CPU offloading, which fits a 16 GB GPU but is slow; VAE slicing and tiling reduce decode memory. Prompts longer than about 226 T5 tokens are truncated by the model, so keep scene and cast descriptions concise.

- `quick`: 720×480, 25 frames at 8 fps, 25 sampling steps; about 3 seconds, reduced quality.
- `480p`: 720×480 (native), 49 frames at 8 fps, 50 sampling steps; about 6 seconds.
- `1080p` (default): generates the same 720×480 clip, then uses FFmpeg Lanczos scaling and aspect-preserving padding to export exactly 1920×1080 (the 3:2 picture fills 1620×1080 with side padding). It retains the generated source. This is upscaled output, not native 1080p generation or AI detail enhancement. The manifest records generation and output dimensions separately.

The model download is about 21 GB. Free GPU quota, session time, throughput, available RAM, and successful inference depend on Kaggle's current session. If a run runs out of memory, restart the session and use `quick`. No cloud generation was executed from this local workspace; the notebook needs its first real GPU run in your signed-in Kaggle session.

## 3. Download and import the MP4

Use the final notebook cell or Kaggle's Output panel to download the MP4 from `/kaggle/working/cogvideox-output`. Download the neighboring JSON manifest as well if you want a record of the prompt, seed, settings, and versions. Keep both before the session ends.

From a terminal in this editor project:

```powershell
npm.cmd run kaggle:import -- --scene 1 --file "D:\Downloads\scene-1-cogvideox-YOUR_TIMESTAMP-1080p.mp4"
```

Replace `1` with your actual scene ID and use the actual downloaded file path. This local maintenance command uses the project's database credentials. It checks the MP4 header and size, copies the video to `public/uploads/scenes/`, marks the scene ready, and prints its editor URL. It preserves the source download and refuses to overwrite an active generation job. A failed database import removes only the new copy it created.

Refresh the editor to preview the imported clip. Stop your Kaggle GPU session when finished.

## Source and validation

The notebook is generated from `scripts/build-kaggle-notebook.py`; rerun that script after changing notebook cells. Library versions are pinned without replacing Kaggle's supplied PyTorch/CUDA build. Local checks validate notebook JSON, Python syntax, export access controls, and video-import behavior; they do not establish a successful cloud inference run.

References: [Kaggle notebooks](https://www.kaggle.com/docs/notebooks), [GPU notebook setup](https://docs.nvidia.com/datascience/deployment/latest/platforms/kaggle/), [CogVideoX-5B model](https://huggingface.co/THUDM/CogVideoX-5b), [Diffusers CogVideoX documentation](https://huggingface.co/docs/diffusers/api/pipelines/cogvideox).
