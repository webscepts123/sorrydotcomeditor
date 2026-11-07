#!/usr/bin/env bash
set -euo pipefail
# Keep the CLI outside release folders, so deployments reuse the installation.
BASE="/home/studiosorrydotco/apps/sorrydotcomeditor"
VENV="$BASE/shared/kaggle-venv"
if [[ -x "$VENV/bin/python" ]] && "$VENV/bin/python" -c 'from importlib.metadata import version; assert version("kaggle") == "2.2.5"' 2>/dev/null; then
  exit 0
fi
PYTHON=""
for candidate in python3.14 python3.13 python3.12 python3.11 /opt/alt/python313/bin/python3 /opt/alt/python312/bin/python3 /opt/alt/python311/bin/python3 python3; do
  if "$candidate" -c 'import sys; assert sys.version_info >= (3, 11)' 2>/dev/null; then
    PYTHON="$candidate"
    break
  fi
done
[[ -n "$PYTHON" ]] || { echo 'Kaggle requires Python 3.11 or newer on the studio server.'; exit 1; }
"$PYTHON" -m venv "$VENV"
"$VENV/bin/python" -m pip install --disable-pip-version-check -r deployment/kaggle-requirements.txt
