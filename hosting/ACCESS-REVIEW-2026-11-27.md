# Server access review — findings 27 Nov 2026 (brief for the next chat)

Ruggero wants to revisit how he reaches his servers and services. Checked live on 27 Nov 2026 (sshd effective config, UFW, listening ports, authorised keys, and a port test of the home IP from rsgmedia). No access changes were made in this review except Force SSL on the n8n proxy host (below). No secrets here.

**Status 27 Nov 2026, 16:10 (same day, next chat):** OptiPlex part **DONE** — Tailscale on OptiPlex + Mac + phone, Sonarr 8989 and dead 2222 forwards deleted, SSH keys only, sudo password changed; Plex 32400 stays open (family streams). Current state: `optiplex/RUNBOOK.md`. **Paused by Ruggero, still open:** Hetzner leftovers (`n8n@stargrid` key, Postgres-over-Tailscale rule: Postgres is also still published on `100.100.225.92:5432` in compose with a sysctl + Docker start-order tweak, so the full cleanup recreates the Postgres container, after Cyber Monday), Rocket.net panel + WordPress admin 2FA check, playbook passwords into 1Password, SiteGround DNS leftovers. Hetzner stays without Tailscale, also for the phone (decided).

## Current state
| Server | How Ruggero gets in | Security as checked |
|---|---|---|
| **rsgmedia** (Hetzner, 116.203.146.177) | SSH 2222; private admin pages through `hosting/hetzner/Open RSG server tools.command` (SSH tunnel); n8n at https://n8n.rsgmedia.info; earnings dashboard https://earnings.rsgmedia.info | Key only, root off, `AllowUsers ruggero`, MaxAuthTries 2, fail2ban; UFW 80/443/2222 only; tunnel `PermitOpen` = 5 admin ports. Keys: Ruggero (1Password), Claude standing key, `n8n@stargrid` (RSA, unused). Every NPM proxy host Force SSL since 27 Nov (n8n was off) |
| **rsg-dfs** (Hetzner, 135.181.110.157) | SSH 2222; DFS screens through `dfs/production/Open DFS production.command` | Same hardening; UFW 2222 only; keys Ruggero + Claude |
| **Rocket.net** (194.39.148.145) | SSH key `theme/.ssh-rocket-key` from the Mac only; Rocket.net panel | Keys: Ruggero RSA (`ruggeroloda@gmail.com`) + `cowork-fix` (Claude). `wp-login.php` 403 to unknown IPs. **Not checked:** 2FA on the Rocket.net panel login and on WordPress admin accounts |
| **OptiPlex** (home, 192.168.68.79, Ubuntu 22.04) | At home only: SSH 22, xrdp 3389, ~11 web UIs on their own ports (Sonarr 8989, Radarr 7878, Prowlarr 9696, Bazarr 6767, SABnzbd 8080, Deluge 8112, Portainer 9000, Tautulli 8181, Netdata 19999, Smokeping 8011, Plex 32400) | SSH key **or password**, no fail2ban, UFW limits 22/3389 to the LAN; Docker-published ports bypass UFW. ddclient running. From the internet (home IP 178.84.136.120) only **8989 Sonarr (plain HTTP login page)** and **32400 Plex** answer of 17 ports tested; 22 and 3389 closed |
| Linode (45.33.39.133) | old DFS, SSH 2200 | root + password; retire in DFS Phase F after Cyber Monday (`dfs/PLAN-STEP7.md`) |

## Findings, worst first
1. Sonarr open to the internet over plain HTTP (router forward 8989); Plex 32400 open (Plex was the 4 Nov compromise vector).
2. No way into the OptiPlex from outside for SSH, remote desktop or the web UIs (SSH-from-phone via dyndns no longer works on 22).
3. OptiPlex: Docker ports skip UFW on the LAN; SSH accepts passwords, no fail2ban (fine while LAN-only).
4. Plain-text passwords in `~/Documents/claude/playbooks/optiplex.md` (OptiPlex sudo, Prowlarr): belong in 1Password only.
5. Leftovers: unused `n8n@stargrid` key and dead Postgres-over-Tailscale UFW rule on rsgmedia; SiteGround DNS `portainer|adminer|npm.rsgmedia.info`.

## Direction discussed (Ruggero to confirm)
- rsgmedia, rsg-dfs: keep SSH 2222 key-only without Tailscale dependency (his standing preference). Tailscale optional later for phone convenience only.
- Rocket.net: SSH fine; check/enable 2FA on the panel and WordPress admins.
- OptiPlex: Tailscale on the OptiPlex, Mac and phone (SSH, RDP via Microsoft's Windows App, all web UIs, in and out of the home network); then close the Sonarr forward; Plex 32400 his call (keep only if someone streams from outside without Tailscale). The Tailscale (Google/Apple) login needs 2FA.
- Open question from Ruggero's earlier preference: he wants SSH to the Hetzner servers from anywhere **without** depending on Tailscale; for the OptiPlex he declined Tailscale earlier ("for now") and now asked whether it would sort access out.
