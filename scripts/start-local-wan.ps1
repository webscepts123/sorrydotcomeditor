$ErrorActionPreference = 'Stop'
$workerRoot = 'D:\AI'
$workerPython = Join-Path $workerRoot 'wan21-venv\Scripts\python.exe'
$workerMain = Join-Path $workerRoot 'ComfyUI\main.py'
if (!(Test-Path -LiteralPath $workerPython) -or !(Test-Path -LiteralPath $workerMain)) {
    throw 'Local Wan is not installed. See deployment/LOCAL-WAN.md.'
}
# This machine has a GTX 970 (4 GB). Use the CPU build rather than an
# incompatible CUDA wheel. The worker is reachable only from this computer.
& $workerPython $workerMain --cpu --force-fp32 --listen 127.0.0.1 --port 8188 --disable-auto-launch --preview-method none
exit $LASTEXITCODE
