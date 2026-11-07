#!/usr/bin/env bash
set -euo pipefail
umask 077
BASE="/home/studiosorrydotco/apps/sorrydotcomeditor"
export HOME=/home/studiosorrydotco
export PATH="$HOME/.local/node/bin:$PATH"
export LD_LIBRARY_PATH="$HOME/.local${LD_LIBRARY_PATH:+:$LD_LIBRARY_PATH}"
ARCHIVE="${1:?Usage: live-deploy.sh archive commit}"
REVISION="${2:?Commit SHA required}"
[[ "$REVISION" =~ ^[a-f0-9]{40}$ ]] || exit 2
mkdir -p "$BASE/releases" "$BASE/shared/uploads" "$BASE/shared/storage" "$BASE/shared/logs"
mkdir "$BASE/shared/deploy.lock" || { echo 'Another deployment is running'; exit 1; }
trap 'rmdir "$BASE/shared/deploy.lock"' EXIT
RELEASE="$BASE/releases/$REVISION"
[[ ! -e "$RELEASE" ]] || { echo 'Release already exists; refusing to overwrite it'; exit 1; }
mkdir "$RELEASE"
tar -xzf "$ARCHIVE" -C "$RELEASE"
ln -s "$BASE/shared/.env" "$RELEASE/.env"
ln -s "$BASE/shared/uploads" "$RELEASE/public/uploads"
ln -s "$BASE/shared/storage" "$RELEASE/storage"
ln -s "$BASE/shared/storage/app/public" "$RELEASE/public/storage"
cd "$RELEASE"
npm ci --omit=dev --no-audit --no-fund
npm run migrate
OLD_PORT="$(cat "$BASE/shared/active-port" 2>/dev/null || echo 3102)"
PORT=3101
[[ "$OLD_PORT" != 3101 ]] || PORT=3102
export PORT HOST=127.0.0.1 NODE_ENV=production
nohup node server.js > "$BASE/shared/logs/$REVISION.log" 2>&1 < /dev/null &
PID=$!
printf '%s\n' "$PID" > "$RELEASE/process.pid"
READY=0
for attempt in {1..30}; do
  if curl -fsS "http://127.0.0.1:$PORT/health" | grep -q '"status":"ok"'; then READY=1; break; fi
  kill -0 "$PID" 2>/dev/null || break
  sleep 1
done
if [[ "$READY" != 1 ]]; then
  kill "$PID" 2>/dev/null || true
  tail -n 40 "$BASE/shared/logs/$REVISION.log"
  echo 'Health check failed; live release unchanged'
  exit 1
fi
PREVIOUS="$(readlink "$BASE/current" 2>/dev/null || true)"
ln -s "$RELEASE" "$BASE/current.next"
mv -Tf "$BASE/current.next" "$BASE/current"
printf '%s\n' "$PORT" > "$BASE/shared/active-port.next"
mv -f "$BASE/shared/active-port.next" "$BASE/shared/active-port"
printf '%s\n' "$REVISION" > "$BASE/shared/revision"
if [[ -n "$PREVIOUS" && -f "$PREVIOUS/process.pid" ]]; then
  OLD_PID="$(cat "$PREVIOUS/process.pid")"
  [[ ! "$OLD_PID" =~ ^[0-9]+$ ]] || kill "$OLD_PID" 2>/dev/null || true
fi
echo "Deployed $REVISION on port $PORT"
