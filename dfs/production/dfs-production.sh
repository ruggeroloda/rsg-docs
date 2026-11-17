#!/usr/bin/env bash
# Open the DFS PRODUCTION Shoes / Feeds / Prices screens (server rsg-dfs, Helsinki, 135.181.110.157).
#   http://localhost:18091
# The screens are not on the internet: this opens a secure tunnel over your normal SSH key
# (1Password + Touch ID). Leave the window open while you work; Ctrl-C closes it.
# (The rsgmedia TEST bench stays at http://localhost:18090 via "Open RSG server tools".)
TARGET="${DFS_SSH_TARGET:-ruggero@135.181.110.157}"
# shellcheck disable=SC2086
OPTS="${DFS_SSH_OPTS:-}"
pid=$(lsof -nP -iTCP:18091 -sTCP:LISTEN -t 2>/dev/null | head -1)
if [ -n "$pid" ]; then
  if [ "$(ps -p "$pid" -o comm= 2>/dev/null | xargs basename 2>/dev/null)" = "ssh" ]; then
    echo "The production tunnel is already open. Opening the page."; command -v open >/dev/null && open http://localhost:18091; exit 0
  fi
  echo "Port 18091 on this Mac is used by another program. Close it and try again."; exit 1
fi
echo "Opening tunnel to DFS production ($TARGET) ... (approve with Touch ID if asked)"
echo "  DFS production screens  http://localhost:18091"
echo "Ctrl-C to close."
( for _ in $(seq 1 60); do curl -s -o /dev/null --max-time 2 http://localhost:18091/ && { command -v open >/dev/null && open http://localhost:18091; break; }; sleep 1; done ) &
# shellcheck disable=SC2086
exec ssh -N -p 2222 $OPTS -o StrictHostKeyChecking=accept-new -o ExitOnForwardFailure=yes -o ServerAliveInterval=30 \
  -L 18091:127.0.0.1:8090 "$TARGET"
