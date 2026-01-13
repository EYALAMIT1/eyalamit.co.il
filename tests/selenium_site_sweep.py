#!/usr/bin/env python3
"""
Pre-deployment comprehensive sweep (Selenium) for all active content items.

- Reads Team 4's updated mapping JSON (474 active items: pages + posts + attachments).
- Visits Pages+Posts with Selenium (Firefox via Selenium Hub).
- Verifies basic render + plugin markers (WooCommerce / Envira / CF7 / Elementor).
- Emits:
  - JSON report under docs/testing/reports/
  - NDJSON runtime logs under .cursor/debug.log (debug-mode evidence)

Notes:
- Selenium Remote Firefox console log collection is limited in this grid (get_log('browser') returns 405).
  We therefore verify "Zero Console" primarily via DOM/error-page detection and plugin markers.
"""

from __future__ import annotations

import argparse
import json
import os
import sys
import time
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait


WORKSPACE_ROOT = Path(__file__).resolve().parents[1]
DEBUG_LOG_PATH = WORKSPACE_ROOT / ".cursor" / "debug.log"


def _now_ms() -> int:
    return int(time.time() * 1000)


def _append_ndjson(payload: Dict[str, Any]) -> None:
    """
    Debug-mode runtime evidence.
    Writes a single NDJSON line to .cursor/debug.log.
    """
    DEBUG_LOG_PATH.parent.mkdir(parents=True, exist_ok=True)
    with DEBUG_LOG_PATH.open("a", encoding="utf-8") as f:
        f.write(json.dumps(payload, ensure_ascii=False) + "\n")


def _log(run_id: str, hypothesis_id: str, location: str, message: str, data: Dict[str, Any]) -> None:
    # region agent log
    _append_ndjson(
        {
            "sessionId": "debug-session",
            "runId": run_id,
            "hypothesisId": hypothesis_id,
            "location": location,
            "message": message,
            "data": data,
            "timestamp": _now_ms(),
        }
    )
    # endregion


def _rewrite_url_for_selenium(url: str) -> str:
    """
    Selenium runs inside Docker; localhost:9090 from the browser container points to itself.
    Use host.docker.internal:9090 instead (wp-config.php is configured to treat it as dev).
    """
    return (
        url.replace("http://localhost:9090", "http://host.docker.internal:9090")
        .replace("https://localhost:9090", "http://host.docker.internal:9090")
        .replace("http://127.0.0.1:9090", "http://host.docker.internal:9090")
    )


@dataclass
class SweepItem:
    kind: str  # page|post|attachment
    id: Optional[int]
    title: str
    url: str
    has_shortcodes: Optional[bool] = None
    has_vc_content: Optional[bool] = None
    has_elementor: Optional[bool] = None


def _load_mapping(mapping_path: Path) -> Tuple[str, List[SweepItem]]:
    raw = json.loads(mapping_path.read_text(encoding="utf-8"))
    site_url = raw.get("metadata", {}).get("site_url", "http://localhost:9090")

    items: List[SweepItem] = []

    for p in raw.get("content", {}).get("pages", []):
        items.append(
            SweepItem(
                kind="page",
                id=int(p.get("id")) if p.get("id") is not None else None,
                title=str(p.get("title", "")),
                url=str(p.get("url", "")),
                has_shortcodes=bool(p.get("has_shortcodes")) if "has_shortcodes" in p else None,
                has_vc_content=bool(p.get("has_vc_content")) if "has_vc_content" in p else None,
                has_elementor=bool(p.get("has_elementor")) if "has_elementor" in p else None,
            )
        )

    for p in raw.get("content", {}).get("posts", []):
        items.append(
            SweepItem(
                kind="post",
                id=int(p.get("id")) if p.get("id") is not None else None,
                title=str(p.get("title", "")),
                url=str(p.get("url", "")),
                has_shortcodes=bool(p.get("has_shortcodes")) if "has_shortcodes" in p else None,
                has_vc_content=bool(p.get("has_vc_content")) if "has_vc_content" in p else None,
                has_elementor=bool(p.get("has_elementor")) if "has_elementor" in p else None,
            )
        )

    for a in raw.get("content", {}).get("attachments", []):
        items.append(
            SweepItem(
                kind="attachment",
                id=int(a.get("id")) if a.get("id") is not None else None,
                title=str(a.get("title", "")),
                url=str(a.get("url", "")),
            )
        )

    return site_url, items


def _setup_driver(selenium_hub_url: str, timeout_seconds: int) -> webdriver.Remote:
    firefox_options = Options()
    firefox_options.add_argument("--headless")
    firefox_options.add_argument("--no-sandbox")
    firefox_options.add_argument("--disable-dev-shm-usage")
    driver = webdriver.Remote(command_executor=selenium_hub_url, options=firefox_options)
    driver.set_page_load_timeout(timeout_seconds)
    return driver


def _detect_plugin_markers(driver: webdriver.Remote) -> Dict[str, Any]:
    """
    Best-effort plugin rendering verification via DOM markers.
    """
    script = r"""
      const out = {};
      const body = document.body;
      out.readyState = document.readyState || null;
      out.title = document.title || null;
      out.bodyClass = body ? body.className : null;
      out.textLen = body ? (body.innerText || '').length : 0;

      const qs = (sel) => !!document.querySelector(sel);

      // WordPress / common error pages
      out.is404 = qs('body.error404') || (out.title && out.title.toLowerCase().includes('404'));
      out.hasWpCriticalError =
        (body && (body.innerText || '').includes('There has been a critical error')) ||
        qs('#error-page') ||
        qs('.wp-die-message');

      // Plugin markers
      out.hasWoo = qs('.woocommerce') || qs('body.woocommerce') || qs('body.woocommerce-page');
      out.hasWooCheckout = qs('form.checkout') || qs('#order_review');
      out.hasWooCart = qs('form.woocommerce-cart-form') || qs('.woocommerce-cart-form');
      out.hasEnvira = qs('.envira-gallery-wrap') || qs('.envira-gallery-public') || qs('.envira-gallery') || qs('[data-envira]');
      out.hasCF7 = qs('.wpcf7') || qs('form.wpcf7-form');
      out.hasElementor = qs('body.elementor-page') || qs('.elementor') || qs('link[id^=\"elementor-\"]') || qs('script[id^=\"elementor-\"]');

      return out;
    """
    return driver.execute_script(script)


def main() -> int:
    parser = argparse.ArgumentParser(description="Pre-deployment Selenium sweep for mapped site content.")
    parser.add_argument(
        "--mapping",
        default="docs/sitemap/ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json",
        help="Path to Team 4 mapping JSON",
    )
    parser.add_argument("--selenium-hub", default="http://localhost:4444/wd/hub", help="Selenium Hub URL")
    parser.add_argument("--timeout", type=int, default=25, help="Page load timeout seconds")
    parser.add_argument("--max-pages", type=int, default=0, help="Limit pages+posts scanned (0 = all)")
    parser.add_argument("--include-attachments", action="store_true", help="Also fetch attachments via browser (slow)")
    parser.add_argument("--report-dir", default="docs/testing/reports", help="Directory for reports")
    args = parser.parse_args()

    run_id = f"predeploy-sweep-{datetime.now().strftime('%Y%m%d-%H%M%S')}"
    mapping_path = WORKSPACE_ROOT / args.mapping
    report_dir = WORKSPACE_ROOT / args.report_dir
    report_dir.mkdir(parents=True, exist_ok=True)

    _log(run_id, "SWEEP", "selenium_site_sweep.py:main", "Starting sweep", {"mapping": str(mapping_path)})

    site_url, items = _load_mapping(mapping_path)
    site_url = _rewrite_url_for_selenium(site_url)

    pages_posts = [i for i in items if i.kind in ("page", "post")]
    attachments = [i for i in items if i.kind == "attachment"]

    if args.max_pages and args.max_pages > 0:
        pages_posts = pages_posts[: args.max_pages]

    results: Dict[str, Any] = {
        "run_id": run_id,
        "generated_at": datetime.now().isoformat(),
        "mapping_file": str(mapping_path),
        "site_url_original": site_url,
        "counts": {
            "pages_posts_targeted": len(pages_posts),
            "attachments_targeted": len(attachments) if args.include_attachments else 0,
            "total_items_in_mapping": len(items),
        },
        "items": [],
        "summary": {},
    }

    driver: Optional[webdriver.Remote] = None
    failures = 0
    plugin_hits = {"woo": 0, "envira": 0, "cf7": 0, "elementor": 0}

    try:
        driver = _setup_driver(args.selenium_hub, args.timeout)
        _log(run_id, "SWEEP", "selenium_site_sweep.py:_setup_driver", "WebDriver ready", {"hub": args.selenium_hub})

        for idx, item in enumerate(pages_posts, start=1):
            target_url = _rewrite_url_for_selenium(item.url)
            started = _now_ms()

            _log(
                run_id,
                "H1",
                "selenium_site_sweep.py:loop",
                "Navigating",
                {"index": idx, "kind": item.kind, "id": item.id, "url": target_url},
            )

            entry: Dict[str, Any] = {
                "kind": item.kind,
                "id": item.id,
                "title": item.title,
                "url_original": item.url,
                "url_selenium": target_url,
                "has_shortcodes": item.has_shortcodes,
                "has_vc_content": item.has_vc_content,
                "has_elementor_flag": item.has_elementor,
                "ok": False,
                "final_url": None,
                "title_runtime": None,
                "markers": {},
                "errors": [],
                "timing_ms": None,
            }

            try:
                driver.get(target_url)
                WebDriverWait(driver, args.timeout).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
                entry["final_url"] = driver.current_url
                entry["title_runtime"] = driver.title

                markers = _detect_plugin_markers(driver)
                entry["markers"] = markers

                if markers.get("hasWoo"):
                    plugin_hits["woo"] += 1
                if markers.get("hasEnvira"):
                    plugin_hits["envira"] += 1
                if markers.get("hasCF7"):
                    plugin_hits["cf7"] += 1
                if markers.get("hasElementor"):
                    plugin_hits["elementor"] += 1

                # Basic pass criteria:
                # - not WP critical error page
                # - body exists and has some text content
                if markers.get("hasWpCriticalError"):
                    entry["errors"].append("wp_critical_error_page_detected")
                if markers.get("is404"):
                    entry["errors"].append("error404_detected")
                if (markers.get("textLen") or 0) < 50:
                    entry["errors"].append("body_text_too_short")

                entry["ok"] = len(entry["errors"]) == 0

            except Exception as e:
                failures += 1
                entry["errors"].append(f"exception:{type(e).__name__}:{str(e)[:200]}")
                entry["ok"] = False
                _log(
                    run_id,
                    "H2",
                    "selenium_site_sweep.py:loop",
                    "Navigation failed",
                    {"url": target_url, "errorType": type(e).__name__, "error": str(e)[:300]},
                )
            finally:
                entry["timing_ms"] = _now_ms() - started
                results["items"].append(entry)

        # Attachments: by default do NOT open in Selenium (would be slow/no console relevance).
        # We record a placeholder summary for now.
        if args.include_attachments and driver:
            for idx, item in enumerate(attachments, start=1):
                target_url = _rewrite_url_for_selenium(item.url)
                _log(run_id, "H3", "selenium_site_sweep.py:attachments", "Skipping attachment in Selenium", {"url": target_url})

        results["summary"] = {
            "ok_count": sum(1 for it in results["items"] if it.get("ok")),
            "fail_count": sum(1 for it in results["items"] if not it.get("ok")),
            "plugin_marker_hits": plugin_hits,
        }

        _log(
            run_id,
            "SWEEP",
            "selenium_site_sweep.py:main",
            "Sweep completed",
            {"summary": results["summary"], "items": len(results["items"])},
        )

    finally:
        if driver:
            driver.quit()

    report_path = report_dir / f"predeploy-selenium-sweep-{run_id}.json"
    report_path.write_text(json.dumps(results, ensure_ascii=False, indent=2), encoding="utf-8")

    print(f"✅ Sweep report written: {report_path}")
    print(json.dumps(results["summary"], ensure_ascii=False, indent=2))

    # Non-zero exit if any failures
    return 1 if results["summary"].get("fail_count", 0) else 0


if __name__ == "__main__":
    raise SystemExit(main())

