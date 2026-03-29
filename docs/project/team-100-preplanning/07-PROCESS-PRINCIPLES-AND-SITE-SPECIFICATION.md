# עקרונות תהליך + אפיון מבנה האתר — צוות 100 (v2)

**גרסה:** 2.0  
**תאריך:** 2026-03-29  
**קהל:** אייל עמית (אישור), נימרוד (ביצוע)  
**סטטוס:** מסמך עבודה — מפת האתר המלאה ב-[`SITEMAP-NEW-SITE-v2-DRAFT.md`](./SITEMAP-NEW-SITE-v2-DRAFT.md) (טיוטה עד חתימת אייל)

---

## 1. מקורות אמת

רמה 1: החלטות אייל. רמה 2: מסמכי צוות 100 שסונכרנו. רמה 3: דוח מחקר ראשוני — עזר בלבד. פירוט: [`RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md`](./RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md).

---

## 2. עקרונות התהליך המוצע (שערים)

אין מעבר לשלב הבא בלי אישור מפורש של אייל (או מיופה כוח) על השלב הקודם.

| שער | תוצר | תכלית |
|-----|------|--------|
| **א** | מפת אתר + IA (עץ) מאושרים | הסכמה על **מה קיים** באתר החדש מבחינת עמודים ואזורים — ראו [`SITEMAP-NEW-SITE-v2-DRAFT.md`](./SITEMAP-NEW-SITE-v2-DRAFT.md). |
| **ב** | אפיון לפי **תבניות עמוד** (לא מוקאפ לכל URL) | לכל סוג עמוד: רכיבים חובה, CTA, Schema, בעל תוכן — ראו [`PAGE-SPECS-TEMPLATE.md`](./PAGE-SPECS-TEMPLATE.md). |
| **ג** | **כיוון ויזואלי** מאושר | בלי מוקאפ מלא לכל המסכים (overhead); כן חובה לפני פיתוח: לוח השראה + 5–7 שקופיות / מסמך עיצוב חד-עמודי (צבע, טיפוגרפיה, ריווח, צילום מול אילוסטרציה) + רשימת רכיבי UI. |
| **ד** | מימוש (WordPress רזה) | בנייה לפי האפיון והכיוון המאושרים. |

```mermaid
flowchart LR
  gateA [gateA_sitemap_IA]
  gateB [gateB_page_templates]
  gateC [gateC_visual_direction]
  gateD [gateD_build]
  gateA --> gateB
  gateB --> gateC
  gateC --> gateD
```

**אחרי אישור מפת האתר:** ממלאים שורה או טבלה לכל עמוד לפי [`PAGE-SPECS-TEMPLATE.md`](./PAGE-SPECS-TEMPLATE.md) (או CSV מבוסס אותה שורה).

---

## 3. מהות האתר (תזכורת)

- אתר **אחד**, דומיין אחד — **הפרדת UX** בין:
  - **מרכז:** סטודיו, נשימה, דיג'רידו, שירותים, המרה.
  - **משני:** הוצאה וספרים; מופעים וארכיון מותגי.
  - **צמיחה:** בלוג (אופטימלי, החייאה — לפי אייל).
  - **גלובלי:** עמוד נחיתה באנגלית.
- **ללא** עגלה וסליקה פנימית — חשבונית ירוקה וקישורים חיצוניים — [`GREEN-INVOICE-LINK-MAP.md`](./GREEN-INVOICE-LINK-MAP.md).
- **עמודי QR:** נשמרים ב-permalink הקיים; לא חלק מהמפה השיווקית — [`QR-URL-POLICY.md`](./QR-URL-POLICY.md).

---

## 4. מבנה UX ברמת מערכת (תקציר)

- **תפריט ראשי:** דגש סטודיו — בית, השיטה (או שירותים מאוחדים), אייל, בלוג, צור קשר, EN.
- **תפריט / אזור משני:** ספרים והוצאה; הופעות וארכיון (ניסוח סופי לאישור אייל).
- **פוטר:** נגישות, תקנון, פייסבוק, טלפון — ראו [`04-IA-SEO-SOCIAL-REQUIREMENTS.md`](./04-IA-SEO-SOCIAL-REQUIREMENTS.md).

פירוט wireframes טקסטואלי: [`IA-WIREFRAMES-AND-EN-LANDING.md`](./IA-WIREFRAMES-AND-EN-LANDING.md).

---

## 5. מפת אתר מלאה (מצביע רשמי)

העץ הממוספר והמעודכן נמצא בנפרד כדי לאפשר אישור גרסה (`DRAFT` → `APPROVED`):

→ **[`SITEMAP-NEW-SITE-v2-DRAFT.md`](./SITEMAP-NEW-SITE-v2-DRAFT.md)**

לאחר שינוי סטטוס לאישור: לעדכן שורה בראש אותו קובץ ולקשר כאן.

---

## 6. סינון תוכן קיים

טבלת Keep / Merge / Drop מול מאגר העמודים — [`CONTENT-DECISIONS-KEEP-MERGE-DROP-v2.md`](./CONTENT-DECISIONS-KEEP-MERGE-DROP-v2.md) + [`CONTENT-SSOT-INVENTORY.csv`](./CONTENT-SSOT-INVENTORY.csv).

---

## 7. רכיבי ממשק לכיוון ויזואלי (צ'קליסט לשער ג')

- Hero (בית): כותרת, תת-כותרת, CTA ראשי אחד, CTA משני אופציונלי.
- כרטיסי שירות (רשת או קרוסלה קלה — העדפה למינימום JS).
- בלוק "אירוע הבא" (תאריך, טקסט קצר, קישור חיצוני).
- תבנית דף שירות: H1, פסקאות, FAQ, CTA דביק במובייל (אופציונלי).
- פוטר מלא.
- טפסים: קצר באתר + קישור לטפסים מפורטים חיצוניים.

---

## 8. קישורים

- [`RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md`](./RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md)
- [`EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md`](./EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md)
- [`06-IMPLEMENTATION-MIGRATION-PACK.md`](./06-IMPLEMENTATION-MIGRATION-PACK.md)
- [`../LAUNCH-CHECKLIST-2026.md`](../LAUNCH-CHECKLIST-2026.md)
