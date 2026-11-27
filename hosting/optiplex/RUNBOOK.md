# OptiPlex (home server) — runbook

_Current state only. Checked live 27 Nov 2026 (Tailscale status, sshd, listening ports, authorised keys, port test of the home IP from rsgmedia). Update in the same session as any change. The Running Warehouse scraper that runs here has its own runbook: `../../automations/rw-scraper/README.md`._

## The machine
Dell OptiPlex at home, Ubuntu 22.04.5, user `ruggero`, LAN `192.168.68.79` (router `192.168.68.1`, home IP `178.84.136.120`, ddclient running). Docker runs the media stack; cron runs the RSG scraper.

## How to reach it (same at home and away)
Tailscale (account `ruggeroloda@gmail.com`, Google login with 2-Step Verification) on the OptiPlex, the MacBook and the phone (`ruggeros-s25`). Name `optiplex` (`optiplex.tail5055fa.ts.net`, `100.112.142.127`). The Mac and phone need "Use Tailscale DNS settings" on, or the name does not resolve. At home the LAN address still works without Tailscale.

| What | Address |
|---|---|
| SSH | `ssh ruggero@optiplex` (key only) |
| Remote desktop (xrdp) | Windows App → `optiplex` (login = the account password) |
| Sonarr / Radarr / Prowlarr / Bazarr | `http://optiplex:8989` / `:7878` / `:9696` / `:6767` |
| SABnzbd / Deluge | `http://optiplex:8080` / `:8112` |
| Portainer / Tautulli / Netdata / Smokeping | `http://optiplex:9000` / `:8181` / `:19999` / `:8011` |
| Plex | `http://optiplex:32400/web` |

## Security as set
- **Open to the internet: only Plex 32400** (router forward, kept so the family can stream from any device). The Sonarr 8989 and the dead 2222 forwards were deleted 27 Nov; 14 ports tested from rsgmedia, only 32400 answers.
- **SSH: keys only** (`/etc/ssh/sshd_config.d/10-keys-only.conf`, `PasswordAuthentication no`, since 27 Nov; a password attempt answers "Permission denied (publickey)"). Keys in `~/.ssh/authorized_keys`: `ruggero-1password` (the 1Password key also used for Hetzner, `SHA256:L+GxxXI2…`), `ruggero-s25-juicessh` (phone), `claude-hetzner` (standing Claude key), `claude-sessions@optiplex`. Backup of the file before 27 Nov: `authorized_keys.bak-20261127`. Host key ED25519 `SHA256:hEzdqcEx2PNZGRAnzcAjXKt2pFqkFCPDEvz5l9pcuKw`.
- **Tailscale 1.104.7**: plain network only ("Tailscale SSH" off), key expiry disabled for the OptiPlex (on for Mac and phone). UFW: 22 and 3389 from the LAN and on `tailscale0`; Docker-published ports bypass UFW (reachable on the LAN and over Tailscale).
- **sudo needs the account password** (changed 27 Nov, in 1Password "OptiPlex (ruggero)"): Claude cannot run sudo here; Ruggero runs those commands.
- Plex: no 2FA on the Plex account (Ruggero, 27 Nov). No auto-updater for the containers (Plex image pulled 22 Nov, server 1.43.5.11079).

## How to undo
- Tailscale: `sudo tailscale down`, `sudo apt remove tailscale`, `sudo ufw delete` the two `tailscale0` rules.
- SSH passwords back on: `sudo rm /etc/ssh/sshd_config.d/10-keys-only.conf && sudo systemctl reload ssh`.
- Router forwards: add again (external 8989 → 192.168.68.79:8989 TCP).

## Open
1. **Plex "Secure connections = Required" is parked.** The setting holds a stale value (`secureConnections="2"`; valid are 1 Preferred, 0 Required) and behaves as Preferred. Plex serves an incomplete certificate chain since the 4 Nov renewal (leaf + Let's Encrypt YR1, without the cross-signed Root YR; known Plex bug), so strict clients fail verification and fall back to http. With Required those devices, Tautulli and Bazarr (both use `http://plex:32400`) would stop. Check after a Plex update: `echo | openssl s_client -connect 127.0.0.1:32400 -servername 192-168-68-79.ffd37902ccb2441c8dbbd30d44301372.plex.direct 2>/dev/null | grep "Verify return"` → when it says `0 (ok)`, set Required and switch Tautulli + Bazarr to a secure connection.
2. Plain-text passwords in `~/Documents/claude/playbooks/optiplex.md` (the old sudo password, now invalid; Prowlarr): move to 1Password and clean the file.
3. Plex and the other containers have no update routine.
4. `claude-sessions@optiplex` key: origin not checked.
