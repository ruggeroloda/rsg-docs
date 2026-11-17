# Pixian background removal (replaces remove.bg) — deploy log

_2026-11-05. Status: **LIVE since 2026-11-05 ~19:55 CET.**_

## Why
- remove.bg's self-serve API stops on **1 Dec 2026, 09:00 CET**. The old hook had no error handling: after that date it would save remove.bg's error response as the `.png` and point the image at it (broken professional image).
- The remove.bg key was hard-coded in the theme (`includes/remove_bg.php` line 30), and the ClippingMagic login was in the commented-out code.
- The old remove.bg call didn't crop. The 2025 images were 1280×960 with the shoe fit to width and 1% padding (the old ClippingMagic settings).

## What changed (one file + wp-config)
- `rsg_2022/includes/remove_bg.php`: lines 1–210 replaced (the active remove.bg hook + the commented-out ClippingMagic block). Lines 211–end (comparison featured-image generator + old commented-out block) are **byte-identical**.
  - Named function `rsg_pixian_cutout_on_save()` on `acf/save_post` priority 5, same trigger as before (`professional_image` / `field_5d9574dae3414` changes; PNG uploads skipped).
  - Sends the local file to `POST https://api.pixian.ai/api/v2/remove-background` with `result.crop_to_foreground=true`, `result.margin=1%`, `result.target_size=1280 960`, `result.vertical_alignment=middle`, `output.format=png` → transparent 1280×960 PNG, same output as the old ClippingMagic setup.
  - Only accepts HTTP 2xx + real PNG bytes. On any failure: attachment untouched, `RSG Pixian: …` written to the PHP error log.
  - Hard-coded remove.bg and ClippingMagic credentials removed.
- `wp-config.php` (staging only so far): `RSG_PIXIAN_API_ID`, `RSG_PIXIAN_API_SECRET`, `RSG_PIXIAN_TEST` (true on staging = free watermarked results). Values come from `_private/pixian.txt`.

## Staging verification (5 Nov)
- `ea-php83 -l` and `ea-php74 -l`: no syntax errors.
- Real image (Kayano 32 original JPEG, test attachment on post 49463): 1.4 s, attachment now `image/png`, 1280×960 with alpha. Output identical to the free API test reviewed earlier.
- Failure path (garbage file with JPEG header): Pixian HTTP 400 code 1006, attachment **untouched**, no PNG written, error logged.
- Test attachments and files deleted afterwards.

## Backups (server)
`~/rsg_backups_20261105_pixian/`: `live-remove_bg.php`, `live-wp-config.php`, `staging-remove_bg.php`, `staging-wp-config.php`, `remove_bg.new.php` (the new file).

## Go-live (done 5 Nov)
- Pixian pack bought: `paygo_500m_250`, 250 credits, use before **2028-11-05** (buy again before then to keep credits usable).
- Staging: `RSG_PIXIAN_TEST` removed → staging now makes real (paid) cut-outs too.
- Live: backups confirmed unchanged → constants added to live `wp-config.php` (no TEST flag) → `remove_bg.php` replaced (identical to staging) → lint 8.3/7.4 OK → homepage + Kayano 32 review 200.
- Paid live test (unattached test attachment, deleted afterwards): 1.6 s, 1280×960 transparent PNG, no watermark, nothing in the error log.
- No cache purge needed.
- **Still to do (Ruggero):** close the remove.bg account; check ClippingMagic has no active subscription.

## Original go-live plan
1. Ruggero buys the $5 Pixian pack. Check `GET https://api.pixian.ai/api/v2/account` → `state` active, credits 250.
2. Live `wp-config.php`: add `RSG_PIXIAN_API_ID` / `RSG_PIXIAN_API_SECRET` (**no** `RSG_PIXIAN_TEST`). Lint.
3. Confirm live `remove_bg.php` still equals `live-remove_bg.php` (backup), then copy `remove_bg.new.php` to live. Lint 8.3 + 7.4.
4. Verify with one test attachment exactly as on staging (costs ~0.15 credits), then delete it.
5. No cache purge needed: admin-only hook, no frontend change.
6. Close remove.bg (its key dies on 1 Dec anyway) and check ClippingMagic has no active subscription. Update `costs/COSTS.md`.

## Rollback
`cp ~/rsg_backups_20261105_pixian/live-remove_bg.php ~/public_html/wp-content/themes/rsg_2022/includes/remove_bg.php` (before 1 Dec only; after that the old file breaks images).
