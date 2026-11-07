"""Download and verify the official Wan 2.1 1.3B ComfyUI model files on D:."""
import hashlib
import json
import pathlib
import time
import urllib.request

root = pathlib.Path('D:/AI/ComfyUI/models')
repo = 'Comfy-Org/Wan-2.1'
files = [
    'diffusion_models/wan2.1_t2v_1.3B_fp16.safetensors',
    'text_encoders/umt5_xxl_fp8_e4m3fn_scaled.safetensors',
    'vae/wan_2.1_vae.safetensors',
]

for relative in files:
    target = root / relative
    target.parent.mkdir(parents=True, exist_ok=True)
    source = f'https://huggingface.co/{repo}/resolve/main/split_files/{relative}'
    head = urllib.request.urlopen(urllib.request.Request(source, method='HEAD'), timeout=60)
    expected = int(head.headers.get('content-length', 0))
    head.close()
    if target.exists() and expected and target.stat().st_size == expected:
        print(f'Already downloaded: {target.name}', flush=True)
        continue
    partial = target.with_suffix('.safetensors.part')
    offset = partial.stat().st_size if partial.exists() else 0
    headers = {'Range': f'bytes={offset}-'} if offset else {}
    response = urllib.request.urlopen(urllib.request.Request(source, headers=headers), timeout=120)
    if response.status != 206:
        offset = 0
    total = offset + int(response.headers.get('content-length', 0))
    downloaded = offset
    print(f'Downloading {target.name} ({total / 1024**3:.2f} GiB)', flush=True)
    last = time.monotonic()
    with partial.open('ab' if offset else 'wb') as output:
        while chunk := response.read(8 * 1024**2):
            output.write(chunk)
            downloaded += len(chunk)
            if time.monotonic() - last > 15:
                print(f'{target.name}: {downloaded / 1024**3:.2f}/{total / 1024**3:.2f} GiB', flush=True)
                last = time.monotonic()
    response.close()
    if total and downloaded != total:
        raise RuntimeError(f'Incomplete download: {target.name}')
    # The Hub metadata exposes the LFS SHA-256, independently of the download.
    api = f'https://huggingface.co/api/models/{repo}/tree/main/split_files/{relative.split("/")[0]}'
    metadata = json.load(urllib.request.urlopen(api, timeout=60))
    entry = next(item for item in metadata if item['path'] == f'split_files/{relative}')
    expected_hash = entry.get('lfs', {}).get('oid')
    if not expected_hash:
        raise RuntimeError(f'No SHA-256 metadata for {target.name}')
    digest = hashlib.file_digest(partial.open('rb'), 'sha256').hexdigest()
    if digest != expected_hash:
        raise RuntimeError(f'SHA-256 verification failed: {target.name}')
    partial.replace(target)
    print(f'Verified: {target.name}', flush=True)
