# Pre-Deployment Comprehensive Selenium Sweep Report (Pages+Posts)
**Date:** 2026-01-14  
**Team:** Team 2 (QA & Monitor)  
**Status:** 🔴 CRITICAL - All Posts return 404 (54/54 failed)

## Scope
- **Mapping source (Team 4):** `docs/sitemap/ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json`
- **Targeted for Selenium sweep:** **135 URLs**
  - **Pages:** 81
  - **Posts:** 54
  - **Attachments:** 319 (not fetched in Selenium in this run; see notes)

## Tooling
- **Runner:** `tests/selenium_site_sweep.py`
- **Selenium:** Remote Firefox via Selenium Hub (`http://localhost:4444/wd/hub`)
- **Runtime evidence (NDJSON):** `.cursor/debug.log`

## Results (Runtime Evidence)
- **Sweep report (JSON):** `docs/testing/reports/predeploy-selenium-sweep-predeploy-sweep-20260114-002340.json`
- **Runtime log evidence:** `.cursor/debug.log`
  - Shows 135 navigations and final summary with counts (see line containing `"Sweep completed"` for runId `predeploy-sweep-20260114-002340`).

### Summary
| Metric | Count |
|---|---:|
| Total scanned (pages+posts) | 135 |
| ✅ OK | 81 |
| ❌ Failed | 54 |

**Observation:** The pass/fail split matches exactly the mapping counts (81 pages OK, **54 posts failed**), indicating a systematic issue affecting all posts.

## Critical Issue: Posts (54/54) return 404

### Symptom
For every post URL from the mapping (typically under `/Blog/...`), WordPress performs a canonical redirect and then the final URL renders a 404.

**Example (post ID 67):**
- Mapping URL: `http://localhost:9090/Blog/18-.../`
- Selenium final URL: `http://host.docker.internal:9090/18-.../`
- Runtime marker: `bodyClass` contains `error404`
- Error recorded: `error404_detected`

### Root-cause evidence (database)
Posts have `post_name` stored as a **percent-encoded string** (e.g. `%d7%...`) rather than a normalized slug. Example:
- `wp_posts.ID=67` → `post_status=publish`, `post_type=post`, `guid=http://blog.muzza.co.il/?p=67`
- `post_name=18-%d7%94%d7%98...`

This strongly suggests an import/migration artifact: the URL path the server receives is decoded, but the stored slug is encoded, so WordPress cannot resolve the post by permalink → resulting 404.

## Plugin Rendering / Markers (Best-effort)
In this run, the current marker set reported **0 hits** for WooCommerce / Envira / CF7 / Elementor.

Important context:
- This sweep is primarily a **coverage + availability** scan (load + 404/critical error).
- Console capture via Selenium Grid is not reliable in this environment (Remote `get_log('browser')` returns HTTP 405 in earlier evidence).
- Plugin “marker hits” should be treated as **indicative only**, not definitive.

## Recommendations (Pre-Deploy Blocking)
1. **Fix post permalink resolution** (blocking):
   - Normalize `post_name` to proper UTF-8 slugs (decoded), or
   - Temporarily switch posts to `?p=ID` links, or
   - Add a robust redirect/mapping layer from encoded slugs to the real post.
2. **After post fix:** rerun `tests/selenium_site_sweep.py` and require:
   - `fail_count == 0` for pages+posts.
3. **Zero Console Errors requirement (strict):**
   - Run Playwright “zero console errors” on at least the critical flows:
     - `/shop/`, `/shop/cart/`, `/shop/checkout/`, `/shop/my-account/`
     - representative media/gallery pages
     - homepage and any key landing pages

## Notes
- Attachments were intentionally not fetched via Selenium in this run (319 items) to keep the sweep focused and fast. Attachments can be verified via HTTP HEAD/GET separately if required.

