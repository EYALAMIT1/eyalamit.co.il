# AGENTS — הקשר פרויקט eyalamit.co.il

מסמך זה מיועד לסוכני AI (Cursor וכו') ולבני אדם: **מקור מצוין לקונטקסט**, אך **המסמך המחייב לתפעול צוותים** הוא `docs/sop/SSOT.md` (Master SSOT).

## נתיב קוד מחייב

`/Users/nimrod/Documents/Eyal Amit/eyalamit.co.il`

## שלב נוכחי (נעילה ראשונית — 2026-03-29)

**תכנון אתר — צוות 100, מסמכי v2.** אין מעבר מלא ל-Build/השקה עד אישור אייל (תקציר מנהלים, מפת אתר טיוטה, מדיניות QR וכו').

## מסמכים ל-CEO אייל (קבוע)

**אסור** להגיש לאייל קבצי **Markdown (`.md`)**. כל מה שיוצא **מול אייל** (חתימה, אישור, קריאה רשמית) — **Word (.docx) או PDF בלבד**.  
Markdown ב-repo מיועד לצוות, Git וסוכני AI. תקציר לאישור: קובץ ללקוח = `EYAL-EXECUTIVE-SUMMARY-FOR-EYAL.docx` (או PDF); מקור עריכה פנימי = `EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md`. **ארכיון הגשות ותשובות מול אייל:** `docs/project/eyal-ceo-submissions-and-responses/`.

## יעדים עיקריים

- אתר WordPress ממוקד המרה, SEO ונגישות; ללא חנות Woo פנימית (סליקה חיצונית).
- שימור permalinks לעמודי **QR מודפסים** — לפי החלטות אייל ו-`docs/project/team-100-preplanning/QR-URL-POLICY.md`.
- מיגרציה מ-legacy לאלמנטור; קוד מותאם עם קידומת `ea_`; שינויים דרך child theme ו-mu-plugins בלבד.

## היררכיית מקורות אמת (בסתירה)

1. החלטות אייל (כולל אחרי חתימה על תקציר שיצא **ב-docx/PDF**, לא על קובץ `.md`).
2. מסמכי צוות 100 שסונכרנו עם אייל.
3. דוחות מחקר בשורש workspace — **עזר בלבד**, לא מחייבים.

פרטים: `docs/project/team-100-preplanning/RESEARCH-SYNC-AND-SOURCE-OF-TRUTH-v2.md`.

## תוכנית העבודה — אינדקס ומסמכי שלב

| מסמך | נתיב |
|------|------|
| אינדקס צוות 100 | `docs/project/team-100-preplanning/README.md` |
| ארכיון הגשות / תשובות מאייל | `docs/project/eyal-ceo-submissions-and-responses/README.md` |
| SSOT מנהלי + תפעול | `docs/sop/SSOT.md` |
| תקציר לאישור — **הגשה לאייל** | ייצור: `team-100-preplanning/EYAL-EXECUTIVE-SUMMARY-FOR-EYAL.docx` או PDF; **עותק להגשה בארכיון:** `eyal-ceo-submissions-and-responses/to-eyal/` |
| תקציר לאישור — מקור פנימי | `docs/project/team-100-preplanning/EYAL-EXECUTIVE-SUMMARY-FOR-APPROVAL.md` |
| מפת אתר חדש (טיוטה) | `docs/project/team-100-preplanning/SITEMAP-NEW-SITE-v2-DRAFT.md` |
| Keep/Merge/Drop | `docs/project/team-100-preplanning/CONTENT-DECISIONS-KEEP-MERGE-DROP-v2.md` |
| תהליך ואפיון | `docs/project/team-100-preplanning/07-PROCESS-PRINCIPLES-AND-SITE-SPECIFICATION.md` |
| מיגרציה והשקה | `docs/project/team-100-preplanning/06-IMPLEMENTATION-MIGRATION-PACK.md` |
| בלוג | `docs/project/BLOG-REVIVAL-PLAN.md` |

## דוחות מחקר (שורש `Eyal Amit/`, מחוץ ל-repo)

- `/Users/nimrod/Documents/Eyal Amit/CLIENT-DECISION-REPORT-EYALAMIT-2026-03-29.md`
- `/Users/nimrod/Documents/Eyal Amit/PRELIMINARY-PLANNING-EYALAMIT-2026-03-29.md`

## ענף Git נוכחי (מתעדכן ב-SSOT)

`feature/lean-wp-rebuild-2026`
