#!/usr/bin/env bash
set -euo pipefail
# Keep the CLI outside release folders, so deployments reuse the installation.
BASE="/home/studiosorrydotco/apps/sorrydotcomeditor"
VENV="$BASE/shared/kaggle-venv"
probe_kaggle() {
  printf '%s\n' '{"action":"probe"}' | "$VENV/bin/python" scripts/kaggle-api.py
}
if [[ -x "$VENV/bin/python" ]] && probe_kaggle | grep -Fq '"version": "2.2.5"'; then
  echo 'Kaggle SDK import verified.'
  exit 0
fi
if [[ ! -x "$VENV/bin/python" ]]; then
  PYTHON=""
  for candidate in python3.14 python3.13 python3.12 python3.11 /opt/alt/python313/bin/python3 /opt/alt/python312/bin/python3 /opt/alt/python311/bin/python3 python3; do
    if "$candidate" -c 'import sys; assert sys.version_info >= (3, 11)' 2>/dev/null; then
      PYTHON="$candidate"
      break
    fi
  done
  [[ -n "$PYTHON" ]] || { echo 'Kaggle requires Python 3.11 or newer on the studio server.'; exit 1; }
  "$PYTHON" -m venv --without-pip "$VENV"
fi
if ! "$VENV/bin/python" -m pip --version >/dev/null 2>&1; then
  # Some cPanel Python builds omit ensurepip; use pip's official bootstrap.
  curl --fail --location --proto '=https' --tlsv1.2 https://bootstrap.pypa.io/get-pip.py -o "$BASE/shared/kaggle-get-pip.py"
  "$VENV/bin/python" "$BASE/shared/kaggle-get-pip.py" --disable-pip-version-check
fi
"$VENV/bin/python" -m pip install --disable-pip-version-check -r deployment/kaggle-requirements.txt
probe_kaggle
echo 'Kaggle SDK import verified.'
