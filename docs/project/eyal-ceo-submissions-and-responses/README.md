# הגשות ותשובות — CEO אייל עמית

תיקייה ייעודית לכל מה ש**יוצא לאייל** ולכל מה ש**חוזר ממנו** (אישור, חתימה, הערות).  
מטרה: ארכיון אחד, ברור לחיפוש ולצוותים — בלי לערבב עם טיוטות מחקר או מקורות Markdown פנימיים.

## מבנה

| תיקייה | תפקיד |
|--------|--------|
| [`to-eyal/`](./to-eyal/) | קבצים **שהוגשו או מוכנים להגשה** לאייל — **רק `.docx` או `.pdf`** (ראו SSOT / AGENTS). |
| [`from-eyal/`](./from-eyal/) | **תשובות מאייל**: מסמך חתום סרוק, PDF מייל, צילום מסך מאושר, וכו' — לפי מה שמתקבל בפועל. |

## שמות קבצים (מומלץ)

פורמט עקבי ל־Git ולמיון:

```text
YYYY-MM-DD--short-topic--vN.ext
```

דוגמאות:

- `2026-03-29--executive-summary-approval--v1.docx`
- `2026-04-01--sitemap-draft--v1.pdf`
- `2026-04-05--executive-summary--signed-scan.pdf` (ב־`from-eyal/`)

אם יש כמה קבצים לאותה הגשה: אותו קידומת תאריך + סיומת תיאורית (`--appendix-notes.pdf`).

## קשר לצוות 100

- **מקור עריכה פנימי (Markdown):** נשאר ב־[`team-100-preplanning/`](../team-100-preplanning/) (למשל `EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md`).
- **ייצור Word (חבילה מלאה):** [`scripts/build_eyal_ceo_deliverables.py`](../../../scripts/build_eyal_ceo_deliverables.py) — יוצר ב־`to-eyal/` את תקציר המנהלים, מפת האתר (מ־`SITEMAP-NEW-SITE-v2-DRAFT.md`) וקובץ ההחלטות; משכפל תקציר גם ל־`team-100-preplanning/EYAL-EXECUTIVE-SUMMARY-FOR-EYAL.docx`.  
  פקודה: `python3 scripts/build_eyal_ceo_deliverables.py` מתוך שורש `eyalamit.co.il`.  
  **תקציר בלבד (תאימות לאחור):** [`scripts/build_eyal_approval_docx.py`](../../../scripts/build_eyal_approval_docx.py) או `python3 scripts/build_eyal_approval_docx.py --all`.
- **PDF מ-Word:** [`team-100-preplanning/README-PDF-FROM-WORD.txt`](../team-100-preplanning/README-PDF-FROM-WORD.txt).

## אינדקס (מלאו ידנית אחרי כל הגשה / תשובה)

| תאריך | כיוון | נושא | קובץ | הערות |
|--------|--------|------|------|--------|
| 2026-03-29 | to-eyal | תקציר מנהלים + טופס אישור | [`to-eyal/2026-03-29--executive-summary--v1.docx`](./to-eyal/2026-03-29--executive-summary--v1.docx) | מסקריפט `build_eyal_ceo_deliverables.py` |
| 2026-03-29 | to-eyal | מפת אתר טיוטה v2 | [`to-eyal/2026-03-29--site-map-draft-v2--v1.docx`](./to-eyal/2026-03-29--site-map-draft-v2--v1.docx) | מבוסס `SITEMAP-NEW-SITE-v2-DRAFT.md` |
| 2026-03-29 | to-eyal | קובץ החלטות (שער 0 + עקרונות תוכן) | [`to-eyal/2026-03-29--decisions-for-approval--v1.docx`](./to-eyal/2026-03-29--decisions-for-approval--v1.docx) | סיכום לאישור; פירוט במאגר |

---

**סמכות:** תיקייה זו היא **ארכיון רשמי** של חומר שמול ה-CEO; אין להגיש לאייל קבצי `.md` — ראו `docs/sop/SSOT.md` גרסה 12.2+.
