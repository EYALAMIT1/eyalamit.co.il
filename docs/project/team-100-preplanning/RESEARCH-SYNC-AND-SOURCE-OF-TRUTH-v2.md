# סנכרון מחקר ראשוני ומקורות אמת — צוות 100 (v2)

**גרסה:** 2.0  
**תאריך:** 2026-03-29  

---

## 1. היררכיית מקורות אמת (מחייב)

בכל סתירה או התלבטות — יש לפעול לפי הסדר הבא:

| רמה | מקור | משמעות |
|-----|------|--------|
| **1** | **החלטות אייל** (שיחות מאושרות; תקציר חתום שיצא **ב-docx/PDF** — לא ב-`.md`; מקור עריכה פנימי: [`EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md`](./EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md)) | **גובר תמיד** — המדויק והמעודכן ביותר לעומת סריקה ראשונית. |
| **2** | מסמכי צוות 100 שסונכרנו עם אייל | לדוגמה [`QR-URL-POLICY.md`](./QR-URL-POLICY.md), [`01-GATE-ZERO-STRATEGY.md`](./01-GATE-ZERO-STRATEGY.md) סעיף 7. |
| **3** | דוח מחקר ו-PRELIMINARY (קבצים בשורש workspace) | **חומר עזר בלבד** — מבוסס מידע ראשוני וסריקת האתר הקיים; רעיונות, השראה והשלמת פערים; **לא** הוראות מחייבות. |

**קבצי מחקר (מחוץ ל-repo או בשורש `Eyal Amit/`):**

- [`CLIENT-DECISION-REPORT-EYALAMIT-2026-03-29.md`](../../../../CLIENT-DECISION-REPORT-EYALAMIT-2026-03-29.md) — נתיב יחסי מתיקיית `team-100-preplanning`: העתק מלא נמצא אצל נימרוד בשורש תיקיית הפרויקט.
- [`PRELIMINARY-PLANNING-EYALAMIT-2026-03-29.md`](../../../../PRELIMINARY-PLANNING-EYALAMIT-2026-03-29.md) — אותו הדבר.

> **הערת נתיב:** אם הקישורים היחסיים לא פותחים מ-Cursor, השתמשו בנתיב המלא: `/Users/nimrod/Documents/Eyal Amit/CLIENT-DECISION-REPORT-EYALAMIT-2026-03-29.md` ו-`PRELIMINARY-PLANNING-EYALAMIT-2026-03-29.md`.

---

## 2. דוגמה לסתירה: עמודי QR

- הדוח וה-PRELIMINARY, כחומר ראשוני, הציעו שלא להעביר QR / להוציאם מהאתר החדש.
- **החלטת אייל** (רמה 1): שימור **כל** ה-URLים — מודפסים בספרים — ראו [`QR-URL-POLICY.md`](./QR-URL-POLICY.md).
- **מסקנה:** המלצת המחקר בנושא QR **בוטלה** מול הלקוח. עדיין ניתן להשאיר QR **מחוץ לניווט המרכזי** ול**מפת האתר השיווקית**, עם שימור permalinks.

---

## 3. תובנות מהמחקר — לאימוץ רק אם תואם לאייל

הנושאים הבאים מסכמים את [`PRELIMINARY-PLANNING`](../../../../PRELIMINARY-PLANNING-EYALAMIT-2026-03-29.md) והדוח; **אימוץ בתיעוד הפרויקט** רק כשאינו סותר רמה 1–2:

- אבחון "כמה אתרים בנכס אחד" — מנמק הקמה מחדש מול תיקון נקודתי (תואם [`ARCHIVE-LEGACY-MONOLITH-PATH.md`](../ARCHIVE-LEGACY-MONOLITH-PATH.md)).
- חדות מסר: מה אייל עושה היום, למי, הבדל שיעור / טיפול / סאונד הילינג, איך מתחילים — דרישות תוכן לבית ול"השיטה".
- תוכן מיושן בדף הבית — מדיניות תפעול (בלוק אירוע + ביקורת תאריכים).
- SEO כבסיס: כוונות חיפוש, cornerstone, FAQ, Schema, sitemap נקי, noindex סלקטיבי, תוכנית 301 (בכפוף ל-QR).
- UX והמרה: CTA ראשי + משני, sticky מובייל, hubים במקום עמודים דקים.
- ניסוח בריאותי — הימנעות מניסוח כהבטחה רפואית רחבה מדי.
- סדר עבודה: החלטות → Keep/Merge/Drop → sitemap → redirects → תוכן → כיוון ויזואלי → Build → QA.

---

## 4. טבלת סנכרון מהיר ("מה מיישמים בתכנון")

| נושא | מקור המחקר | סטטוס מול אייל |
|------|-------------|----------------|
| WordPress נקי, בלי Woo פנימי | דוח + PRELIMINARY | **מאושר** — חשבונית ירוקה |
| שימור URLי QR | דוח הציע להסיר | **גובר אייל** — שימור |
| הוצאה / מופעים באתר אחד ב-UX משני | דוח + אייל | **מאושר** |
| בלוג אופטימלי / החייאה | אייל | **גובר**; סינון תוכן מהדוח = המלצה משנית |

---

## 5. מקורות טכניים נוספים (עזר, לא מחליפים JSON מדויק)

- [`../../sitemap/SITEMAP-v1.0-2026-01-14.md`](../../sitemap/SITEMAP-v1.0-2026-01-14.md)
- [`../../testing/reports/sitemap-content-breakdown.md`](../../testing/reports/sitemap-content-breakdown.md)
- מיפוי DB מדויק: [`../../sitemap/ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json`](../../sitemap/ACCURATE-SITE-MAPPING-AFTER-ARCHIVE-2026-01-13_22-02-59.json)

במקרה סתירה בין מסמכי sitemap ישנים ל-JSON המדויק — **ה-JSON ומדיניות אייל גוברים**.

---

## 6. קישורים למסמכי v2 של התוכנית

- [`07-PROCESS-PRINCIPLES-AND-SITE-SPECIFICATION.md`](./07-PROCESS-PRINCIPLES-AND-SITE-SPECIFICATION.md)
- [`SITEMAP-NEW-SITE-v2-DRAFT.md`](./SITEMAP-NEW-SITE-v2-DRAFT.md)
- [`CONTENT-DECISIONS-KEEP-MERGE-DROP-v2.md`](./CONTENT-DECISIONS-KEEP-MERGE-DROP-v2.md)
- [`PAGE-SPECS-TEMPLATE.md`](./PAGE-SPECS-TEMPLATE.md)
