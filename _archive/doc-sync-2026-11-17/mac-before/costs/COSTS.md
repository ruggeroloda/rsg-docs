# Cost register

_Every recurring RSG cost, now vs target. "?" = unknown, fill from the bank transactions in Airtable or the invoice. Last updated 2026-11-11 (evening)._

| Item | Provider | Now /mo | Target /mo | Status |
|---|---|---|---|---|
| WordPress hosting (custom Expert) | Rocket.net | ~$370 | **$83** (Business yearly, no storage add-on; $996/yr) | Agreed in principle 10 Nov (ticket 122395); lean staging done 11 Nov (~27 GB of 40 GB), so no 15 GB add-on; confirmation mail sent 11 Nov, waiting for the reply |
| Old DFS server (Linode 4 GB) + backups | Akamai/Linode | $29 ($24 + $5) | $0 | New DFS live 10 Nov; cancel in Phase F (from ~17 Nov) |
| Volume "stagingBackups" | Akamai/Linode | $0 (was $9.60) | $0 | ✅ Deleted 5 Nov |
| DFS production server rsg-dfs (CAX11 + Hetzner Backups) | Hetzner | ? | same | Running since 9 Nov; amount from the first Hetzner invoice |
| Hetzner rsgmedia CPX32 (n8n, Stargrid, DFS test bench) | Hetzner | €20 (grandfathered) | €20 | Keep; do not resize |
| Email newsletter | MailerLite | ~$88 (~$1,058/yr, Ruggero's figure, to confirm on the invoice) | ? | List cleanup, then platform decision |
| Transactional email (quiz picks, DFS alerts, monthly report) | MailerSend | ? (free plan, 100 mails/day) | ? | |
| Automations | Make.com (Core, 10k ops) | ~$10 (est.) | $0 | Keep until accounting + shoe inserts are migrated |
| Automations | n8n Cloud Starter | €24 + VAT | €0 | Cancel after the bank import runs on Hetzner n8n |
| Off-server backups | Google Drive (existing 5 TB) | €0 | €0 | rsgmedia since 5 Nov, rsg-dfs since 9 Nov, WordPress (Rocket.net → Drive, ~25 GB) since 11 Nov; own Google OAuth client (free) |
| Heartbeat monitoring | healthchecks.io | €0 (free) | €0 | `rsg-dfs` since 9 Nov, `rsg-site` since 11 Nov |
| Database / ops | Airtable | ? | ? | |
| Background removal | Pixian.AI (replaces remove.bg) | — | ~$0.20 ($5 pack every 2 yrs) | Live 5 Nov; pack valid to 2028-11-05. Close the remove.bg account (its API ends 1 Dec 2026) |
| Shoes for reviews | Running Warehouse | — | ~$100 | Restart plan: 1 shoe/month |
| Video work per shoe | Reviewer | — | ~$30/shoe | Restart plan |

**Known now:** about **$500/mo** (Rocket.net ~$370, MailerLite ~$88, Linode $29, Make ~$10) **+ €44/mo** (Hetzner rsgmedia €20, n8n Cloud €24 + VAT) + rsg-dfs + the unknowns.
**Known target:** about **$181/mo** (Rocket.net $83, MailerLite ~$88, Make ~$10 until migrated) **+ €20** + rsg-dfs + the unknowns.
**Revenue side** (for context): Raptive ads, affiliate (Amazon mainly), deals emails.
