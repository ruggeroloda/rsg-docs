#!/usr/bin/env bash
# rsg-tools — open the private admin tools of the Hetzner server (rsgmedia).
#
#   Portainer              http://localhost:19000
#   Adminer (database)     http://localhost:18080
#   Nginx Proxy Manager    http://localhost:18081
#   Umami (analytics)      http://localhost:18010
#   DFS Shoes screen       http://localhost:18090   (DFS test container; added 6 Nov)
#
# They are NOT on the internet any more. This opens a secure tunnel over your
# normal SSH key (1Password + Touch ID). Leave the window open while you work;
# press Ctrl-C to close the tunnel.
#
# Run:  bash rsg-tools.sh        (or:  chmod +x rsg-tools.sh && ./rsg-tools.sh)
# Optional: RSG_SSH_OPTS="-i /path/to/key -o IdentitiesOnly=yes" bash rsg-tools.sh

TARGET="${RSG_SSH_TARGET:-ruggero@116.203.146.177}"
PORT="${RSG_SSH_PORT:-2222}"
# shellcheck disable=SC2086
OPTS="${RSG_SSH_OPTS:-}"

LOCAL_PORTS="19000 18080 18081 18090 18010"
open_pages() { command -v open >/dev/null && { open http://localhost:19000; open http://localhost:18080; open http://localhost:18081; open http://localhost:18090; open http://localhost:18010; }; }

# Is a tunnel already open (e.g. in another Terminal window)? (added 7 Nov 2026)
busy=""; ssh_pids=""; other=""
for p in $LOCAL_PORTS; do
  pid=$(lsof -nP -iTCP:"$p" -sTCP:LISTEN -t 2>/dev/null | head -1)
  [ -z "$pid" ] && continue
  busy="$busy $p"
  if [ "$(ps -p "$pid" -o comm= 2>/dev/null | xargs basename 2>/dev/null)" = "ssh" ]; then
    ssh_pids="$ssh_pids $pid"
  else
    other="$other $p"
  fi
done
if [ -n "$other" ]; then
  echo "Port(s)$other on this Mac are used by another program, not by the RSG tunnel. Close that program and try again."
  exit 1
fi
if [ "$busy" = " $LOCAL_PORTS" ] && [ -n "$ssh_pids" ]; then
  echo "The tunnel is already open (another Terminal window). Opening the pages."
  open_pages
  exit 0
fi
if [ -n "$ssh_pids" ]; then
  echo "An older tunnel is open without all the tools; closing it and opening a new one."
  # shellcheck disable=SC2086
  kill $(echo $ssh_pids | tr ' ' '\n' | sort -u) 2>/dev/null; sleep 1
fi

echo "Opening tunnel to $TARGET ... (approve with Touch ID if asked)"
echo "  Portainer            http://localhost:19000"
echo "  Adminer              http://localhost:18080"
echo "  Nginx Proxy Manager  http://localhost:18081"
echo "  Umami (analytics)    http://localhost:18010"
echo "  DFS Shoes screen     http://localhost:18090"
echo "Ctrl-C to close."

# open the pages in the browser once the tunnel is up (macOS)
(
  for _ in $(seq 1 60); do
    if curl -s -o /dev/null --max-time 2 http://localhost:19000/; then
      open_pages
      break
    fi
    sleep 1
  done
) &

# shellcheck disable=SC2086
exec ssh -N -p "$PORT" $OPTS \
  -o ExitOnForwardFailure=yes -o ServerAliveInterval=30 \
  -L 19000:127.0.0.1:9000 \
  -L 18080:127.0.0.1:8080 \
  -L 18081:127.0.0.1:81 \
  -L 18090:127.0.0.1:8090 \
  -L 18010:127.0.0.1:3010 \
  "$TARGET"
