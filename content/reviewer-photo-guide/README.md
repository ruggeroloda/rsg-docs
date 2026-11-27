# Reviewer photo guide ("Photo shot list")

_Started 27 Nov 2026 (Ruggero + Claude). Replaces the Google Doc "Shoe Pictures for Running Shoes Guru" once live._

## State (27 Nov 2026)
- **Staging only:** https://jfx92wmcr3-staging.onrocket.site/ex/photo-shot-list/ = "Noindex Page" post 50922 (post type `noindex`, template `template-clean.php`, meta `rsg_ads=all`). Checked: `noindex, follow`, not in the sitemap, body class `adthrive-disable-all`, images answer 200. **Not looked at rendered** (the Chrome screenshot timed out three times): Ruggero or the next session checks desktop + phone.
- Not on live. Needs Ruggero's "deploy to live".
- No theme change: the page is content only (one small `<style>` block at the top of the content for the photo grid).

## What is in it
| Section | Rule | Pictures |
|---|---|---|
| The set (1–11) | all 11, day one | Winflo 11 review photos (live media library, `/wp-content/uploads/2024/05/…-633x475.jpeg`) |
| Details | at least three, the reviewer finds them, one line each | 3 Winflo examples (`psl-detail-midsole.jpg` is a crop) |
| In use (12–16) | at least three, landscape + portrait | sketches |
| Beauty | free | 5 sketches (ideas taken from Believe in the Run photos; their photos are not used) |
| After testing (17–21) | all five, same angle as day one | sketches |
| Mistakes to avoid | | sketches |

Ruggero's calls (27 Nov): no scale shot; backgrounds welcome, outdoors preferred; no old-vs-new shot; more photos than needed so he can choose; originals unedited; details are forced but free; unlisted page on RSG instead of a Google Doc.

## Files
- `photo-shot-list.html`: the page content as created on staging (image URLs are relative: `/wp-content/uploads/2026/11/psl-*.png`).
- `img/`: 20 sketches + the midsole crop (staging media IDs 50901–50921).
- After creation the page in WordPress is the source: Ruggero can edit it in wp-admin (Noindex Pages).

## Open
1. Ruggero re-shoots the Beauty examples himself (said 28 Nov): replace the five sketches.
2. In use and After testing have no real examples yet: first new review supplies them.
3. Shot 9 (outsole grip, close) uses the "outsole from side" photo: not a real close-up.
4. The 30-second handling video from the old Google Doc is not in the page (video shot list is separate: `../VIDEO-BROLL-SHOT-LIST-2026-11-19.md`; pilot subset of 11 clips proposed 27 Nov in chat, not saved).

## Go live (on "deploy to live")
From the Mac shell, live webroot `~/public_html`: `wp media import ~/rsg_tmp/psl/img/* --user=1`, then `wp post create ~/rsg_tmp/psl/photo-shot-list.html --post_type=noindex --post_title="Photo shot list" --post_name=photo-shot-list --post_status=publish --user=1 --porcelain`, `wp post meta set <ID> rsg_ads all`. If the upload month is no longer 2026/11, fix the `psl-*` paths in the content. If the staging page was edited in wp-admin, export its content first (`wp post get 50922 --field=post_content`). Then a row in `rsg.change_log`.
