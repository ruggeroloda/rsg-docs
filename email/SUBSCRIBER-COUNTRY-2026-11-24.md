# Subscriber country: note (24 Nov 2026)

**Update 27 Nov:** wrong premise below — MailerLite has a Country field (17k filled) and its own location field (Gleam); see `RUNBOOK.md` "Subscriber country". Options 2–3 remain for the ~10k with neither.

**Status: idea, not started** (Ruggero 24 Nov: write it down, don't do it yet). Earliest after Cyber Monday (freeze week).

## Problem
Deals emails are per country (offers and shops are US, UK, DE …), but MailerLite only knows the country of **quiz sign-ups** (`quiz_country`, from Cloudflare at sign-up, `../theme/MAILERLITE-CAPTURE-BRIEF.md`). Everyone else on the ~35k active list has no reliable country. The "~20k in the US" figure is Ruggero's (11 Nov); its source is not checked: first see whether MailerLite shows a location field of its own and how it is filled.

## Options (quick → best)
1. **Email domain** (no build, rough): `.de`, `.co.uk`, `.fr`, `.it` … and local providers (web.de, gmx, t-online, orange.fr, btinternet, libero, free.fr …) → non-US country into a MailerLite field. Gmail/Outlook/Yahoo stay unknown.
2. **Locate on click** (best, small build): email links go through a short redirect on our site (e.g. `/go/e/<nn>?s=<subscriber id>`), which reads the Cloudflare country, writes it to the subscriber in MailerLite through the API (only when unknown or different 2+ times), logs the click, then sends the reader on to the shop link with its sub-ID. Every click of every email fills it in. Needs: endpoint in the theme (staging → "deploy to live"), MailerLite subscriber-ID merge tag in links, a signed parameter so nobody can change other people's data, no IP stored.
3. **Ask** (explicit, same endpoint): a "Where do you shop? US · UK · EU · Canada · Australia" row in an email, one click each; overrides option 2 (VPN, travel).

Proposed order: 1, then 2 + 3 together. Then send each country its own deals email (UK/DE/IT Nike promotions are already in `rsg.deals_email_offer`).
