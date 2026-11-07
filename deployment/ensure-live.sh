#!/usr/bin/env bash
set -euo pipefail
export HOME=/home/studiosorrydotco
BASE="$HOME/apps/sorrydotcomeditor"
[[ -f "$BASE/shared/active-port" && -L "$BASE/current" ]] || exit 0
export PORT="$(cat "$BASE/shared/active-port")"
curl -fsS --max-time 5 "http://127.0.0.1:$PORT/health" >/dev/null 2>&1 && exit 0
mkdir "$BASE/shared/deploy.lock" 2>/dev/null || exit 0
trap 'rmdir "$BASE/shared/deploy.lock"' EXIT
export PATH="$HOME/.local/node/bin:$PATH"
export LD_LIBRARY_PATH="$HOME/.local${LD_LIBRARY_PATH:+:$LD_LIBRARY_PATH}"
export HOST=127.0.0.1 NODE_ENV=production
cd "$BASE/current"
nohup node server.js >> "$BASE/shared/logs/watchdog.log" 2>&1 < /dev/null &
echo "$!" > process.pid
