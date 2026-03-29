#!/usr/bin/env python3
"""Backward-compatible entry: builds executive summary .docx (and full CEO package).

For all three files (תקציר, מפת אתר, קובץ החלטות) run:
  python3 scripts/build_eyal_ceo_deliverables.py
"""
import sys
from pathlib import Path

# Allow imports when run from repo root
sys.path.insert(0, str(Path(__file__).resolve().parent))

from build_eyal_ceo_deliverables import build_executive_summary_docx, main as build_all


def main():
    build_executive_summary_docx()


if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] == "--all":
        build_all()
    else:
        main()
        print("(הרץ עם --all לייצור כל חבילת ההגשות ל-to-eyal/)")
