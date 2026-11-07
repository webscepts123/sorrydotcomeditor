# Wan 2.1 in Kaggle Notebooks

This workflow runs video generation in your Kaggle GPU notebook and imports the MP4 into the editor on your Windows PC. Your local GTX 970 is not used for generation. It does not require a public tunnel or a connection from Kaggle to your local MySQL database.

## 1. Import the notebook

The file is [`notebooks/wan21-kaggle.ipynb`](../notebooks/wan21-kaggle.ipynb). You can also download it from a scene's **Video editor → AI Engine → WAN 2.1 — KAGGLE GPU** panel.

1. Sign in at [Kaggle Notebooks](https://www.kaggle.com/code).
2. Create a new notebook, then use **File → Import Notebook** and upload `wan21-kaggle.ipynb` (menu wording may vary).
3. Enable **Internet** and select an available **GPU accelerator** in notebook settings. The notebook uses GPU 0; T4 x2 does not automatically behave like one GPU with combined memory.
4. Run the GPU-check cell. This notebook targets at least 12 GiB GPU memory, 24 GiB system RAM, and 25 GiB free notebook disk. These are checks for this implementation, not universal minimum requirements for Wan.

If GPU selection is unavailable, check your Kaggle quota, account verification, and accelerator availability in Kaggle. Signing in alone does not guarantee a GPU session. No Kaggle API credential is needed for this manual import workflow.

## 2. Choose your scene and run

In the **Scene settings** cell, edit `PROMPT`, set `SCENE_ID` to the scene number from your editor URL, and leave `PRESET = "quick"` for the first run. Run the remaining cells in order.

Alternatively, use **Export this scene for Kaggle** in the editor. Add the downloaded JSON as a private notebook input, set `JOB_FILE` to its `/kaggle/input/...` path, and rerun from Scene settings. That export contains the saved scene script, visual style, and assigned cast descriptions; save changes before exporting.

The notebook encodes text in a separate CPU stage using FP32, releases the text encoder, then loads the 1.3B video transformer on the GPU. It selects BF16 only when the assigned GPU supports it, otherwise FP16 for the transformer. The VAE uses FP32. This avoids assuming T4/P100 GPUs support BF16.

- `quick`: 480×272, 33 frames at 16 fps, 20 sampling steps; about 2 seconds, reduced quality.
- `480p`: 832×480, 81 frames at 16 fps, 30 sampling steps; about 5 seconds.

Model downloads are several GB. Free GPU quota, session time, throughput, available RAM, and successful inference depend on Kaggle's current session. If a run runs out of memory, restart the session and use `quick`. No cloud generation was executed from this local workspace; the notebook needs its first real GPU run in your signed-in Kaggle session.

## 3. Download and import the MP4

Use the final notebook cell or Kaggle's Output panel to download the MP4 from `/kaggle/working/wan-output`. Download the neighboring JSON manifest as well if you want a record of the prompt, seed, settings, and versions. Keep both before the session ends.

From a terminal in this editor project:

```powershell
npm.cmd run kaggle:import -- --scene 1 --file "D:\Downloads\scene-1-wan21-YOUR_TIMESTAMP.mp4"
```

Replace `1` with your actual scene ID and use the actual downloaded file path. This local maintenance command uses the project's database credentials. It checks the MP4 header and size, copies the video to `public/uploads/scenes/`, marks the scene ready, and prints its editor URL. It preserves the source download and refuses to overwrite an active generation job. A failed database import removes only the new copy it created.

Refresh the editor to preview the imported clip. Stop your Kaggle GPU session when finished.

## Source and validation

The notebook is generated from `scripts/build-kaggle-notebook.py`; rerun that script after changing notebook cells. Library versions are pinned without replacing Kaggle's supplied PyTorch/CUDA build. Local checks validate notebook JSON, Python syntax, export access controls, and video-import behavior; they do not establish a successful cloud inference run.

References: [Kaggle notebooks](https://www.kaggle.com/docs/notebooks), [GPU notebook setup](https://docs.nvidia.com/datascience/deployment/latest/platforms/kaggle/), [official Wan model](https://huggingface.co/Wan-AI/Wan2.1-T2V-1.3B-Diffusers), [Diffusers Wan documentation](https://huggingface.co/docs/diffusers/api/pipelines/wan).
