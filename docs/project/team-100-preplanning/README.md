# צוות 100 — תכנון מקדים אתר אייל עמית

**חבילת גרסה:** 2.0 (2026-03-29) — סנכרון מחקר ראשוני (עזר בלבד מול החלטות אייל), מפת אתר טיוטה, Keep/Merge/Drop, אפיון תהליך ועמודים לאייל.

**נעילת שלב (קונטקסט Cursor / SSOT):** ראו גם [`AGENTS.md`](../../../AGENTS.md) בשורש ה-repo ו-[`docs/sop/SSOT.md`](../../sop/SSOT.md) (גרסה 12.2+) — טבלת שלב נוכחי וקישורים למסמכי v2. **ארכיון הגשות ותשובות לאייל:** [`eyal-ceo-submissions-and-responses/`](../eyal-ceo-submissions-and-responses/README.md).

**הגשה לאייל (קבוע):** רק **Word (.docx) או PDF** — **לא** Markdown.

תיקייה זו מממשת את תהליך התכנון המקדים: שער מנהל, מיפוי תוכן, מטריצת היקף, דרישות IA/SEO/חברתי, החלטת פלטפורמה, וחבילת מיגרציה.

## מסמכי ליבה v2 (אפיון וסנכרון)

| קובץ | תיאור |
|------|--------|
| [RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md](./RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md) | היררכיית מקורות אמת; דוח מחקר = עזר; דוגמת QR |
| [07-PROCESS-PRINCIPLES-AND-SITE-SPECIFICATION.md](./07-PROCESS-PRINCIPLES-AND-SITE-SPECIFICATION.md) | עקרונות תהליך, שערים, UX, הפניה למפת אתר |
| [SITEMAP-NEW-SITE-v2-DRAFT.md](./SITEMAP-NEW-SITE-v2-DRAFT.md) | מפת אתר מלאה — **טיוטה לאישור אייל** |
| [CONTENT-DECISIONS-KEEP-MERGE-DROP-v2.md](./CONTENT-DECISIONS-KEEP-MERGE-DROP-v2.md) | טבלת החלטות תוכן מול SSOT |
| [PAGE-SPECS-TEMPLATE.md](./PAGE-SPECS-TEMPLATE.md) | תבנית אפיון לכל עמוד אחרי אישור המפה |

## דוחות מחקר (שורש workspace — מחוץ ל-repo)

נתיבים טיפוסיים על מכונת העבודה של נימרוד:

- `/Users/nimrod/Documents/Eyal Amit/CLIENT-DECISION-REPORT-EYALAMIT-2026-03-29.md`
- `/Users/nimrod/Documents/Eyal Amit/PRELIMINARY-PLANNING-EYALAMIT-2026-03-29.md`

אינם מחליפים החלטות אייל — ראו `RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md`.

## מסמכים

| קובץ | תיאור |
|------|--------|
| [EYAL-EXECUTIVE-SUMMARY-FOR-EYAL.docx](./EYAL-EXECUTIVE-SUMMARY-FOR-EYAL.docx) | **הקובץ היחיד להגשה לאייל** — Word (מיוצר מ-[`scripts/build_eyal_approval_docx.py`](../../../scripts/build_eyal_approval_docx.py)) |
| [README-PDF-FROM-WORD.txt](./README-PDF-FROM-WORD.txt) | איך לייצא **PDF** מ-Word (חלופה מותרת להגשה) |
| [EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md](./EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md) | מקור **פנימי** בלבד — תוכן זהה ל-docx; **אל תשלחו `.md` לאייל** |
| [LEGAL-ACCESSIBILITY-ISRAEL-SPEC.md](./LEGAL-ACCESSIBILITY-ISRAEL-SPEC.md) | נגישות מול חוק ישראל + צ'קליסט |
| [IA-WIREFRAMES-AND-EN-LANDING.md](./IA-WIREFRAMES-AND-EN-LANDING.md) | Wireframes לוגיים + עמוד EN |
| [GREEN-INVOICE-LINK-MAP.md](./GREEN-INVOICE-LINK-MAP.md) | מיפוי סליקה חשבונית ירוקה |
| [QR-URL-POLICY.md](./QR-URL-POLICY.md) | מדיניות שימור URL ל-QR |
| [QR-URL-INVENTORY.csv](./QR-URL-INVENTORY.csv) | רשימת עמודי QR למעקב |
| [01-GATE-ZERO-STRATEGY.md](./01-GATE-ZERO-STRATEGY.md) | אישור מהות האתר, KPIs, בחירת כיוון A/B/C |
| [02-CONTENT-SSOT-GUIDE.md](./02-CONTENT-SSOT-GUIDE.md) | איך למלא את גיליון התוכן |
| [CONTENT-SSOT-INVENTORY.csv](./CONTENT-SSOT-INVENTORY.csv) | 135 שורות: כל העמודים והפוסטים מהמיפוי המדויק |
| [03-SCOPE-MATRIX.md](./03-SCOPE-MATRIX.md) | סינון תוכן + תבנית 301 |
| [04-IA-SEO-SOCIAL-REQUIREMENTS.md](./04-IA-SEO-SOCIAL-REQUIREMENTS.md) | מבנה מידע, SEO, שיתוף, נגישות |
| [05-PLATFORM-DECISION.md](./05-PLATFORM-DECISION.md) | המלצה ורשימת תוספים מותרים |
| [06-IMPLEMENTATION-MIGRATION-PACK.md](./06-IMPLEMENTATION-MIGRATION-PACK.md) | מיגרציה, השקה, צ'קליסט, תחזוקה |

**מסמכים ברמת `docs/project/`:** [`BLOG-REVIVAL-PLAN.md`](../BLOG-REVIVAL-PLAN.md), [`LAUNCH-CHECKLIST-2026.md`](../LAUNCH-CHECKLIST-2026.md), [`ARCHIVE-LEGACY-MONOLITH-PATH.md`](../ARCHIVE-LEGACY-MONOLITH-PATH.md).  
**צעדי פיתוח WP:** [`LEAN-WP-NEXT-STEPS.md`](./LEAN-WP-NEXT-STEPS.md).

## מקור נתונים

ייצוא התוכן ב-CSV מבוסס על [`ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json`](../../sitemap/ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json).
