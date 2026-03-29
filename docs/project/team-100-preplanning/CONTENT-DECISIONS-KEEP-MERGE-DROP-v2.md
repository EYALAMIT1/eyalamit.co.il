# החלטות תוכן: Keep / Merge / Drop — צוות 100 (v2)

**גרסה:** 2.0  
**תאריך:** 2026-03-29  
**מקורות אמת:** ראשית אייל; סנכרון מחקר = עזר בלבד — [`RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md`](./RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md)

---

## 1. מטרה

למפות כל עמוד/פוסט קיים (או קבוצות) להחלטה אחת, לפני מיגרציה. הבסיס לרשימה המלאה: [`CONTENT-SSOT-INVENTORY.csv`](./CONTENT-SSOT-INVENTORY.csv) (81 עמודים + 54 פוסטים).

---

## 2. עמודות מומלצות לטבלה (Excel / CSV)

| עמודה | תיאור |
|--------|--------|
| `wp_id` | מזהה מה-SSOT |
| `type` | page / post |
| `title` | כותרת נוכחית |
| `current_url` | או slug |
| `decision` | Keep / Merge / Drop / External |
| `target_note` | אם Merge — לשם איזה עמוד; אם External — לאן |
| `redirect_301` | כן/לא + הערה (**לא** ל-QR לפי מדיניות) |
| `owner` | אייל / נימרוד |
| `notes` | |

---

## 3. מדיניות מחייבת (לא לשכוח)

- **QR:** `Keep` לנתיב — **אין** שינוי slug, **אין** 301 (אלא אם אייל מחליט אחרת בכתב) — [`QR-URL-POLICY.md`](./QR-URL-POLICY.md).
- **חנות Woo:** `Drop` ממבנה האתר החדש + `301` ליעד שאייל מאשר — [`03-SCOPE-MATRIX.md`](./03-SCOPE-MATRIX.md).
- **בלוג:** קודם כל **כוונת אייל** (החייאה, אופטימיזציה); סינון "רק חזק" מהדוח = המלצה משנית — [`BLOG-REVIVAL-PLAN.md`](../BLOG-REVIVAL-PLAN.md).

---

## 4. מקורות עזר (לא מחליפים JSON מדויק)

- [`../../sitemap/SITEMAP-v1.0-2026-01-14.md`](../../sitemap/SITEMAP-v1.0-2026-01-14.md)
- [`../../testing/reports/sitemap-content-breakdown.md`](../../testing/reports/sitemap-content-breakdown.md)
- JSON מדויק: [`../../sitemap/ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json`](../../sitemap/ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json)

אם יש סתירה בין מסמך ישן ל-JSON — **ה-JSON** משמש לאמת רשימות.

---

## 5. טבלת טיוטה (דוגמה — למחיקה אחרי מילוי אמיתי)

| wp_id | type | decision | הערה |
|-------|------|----------|------|
| — | page | Keep | דוגמה בלבד |
| — | post | Merge | למזג לעמוד שירות / לבלוג מאוחד |

**המשימה:** להחליף את השורות בנתונים אמיתיים מה-SSOT, שורה אחר שורה או לפי קבוצות (למשל כל עמודי `qr*` = Keep URL).

---

## 6. קישורים

- [`SITEMAP-NEW-SITE-v2-DRAFT.md`](./SITEMAP-NEW-SITE-v2-DRAFT.md)
- [`PAGE-SPECS-TEMPLATE.md`](./PAGE-SPECS-TEMPLATE.md)
