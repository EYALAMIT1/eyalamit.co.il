📖 נוהל עבודה מרכזי (Master SSOT) - eyalamit.co.il
גרסה: 11.0
סמכות עליונה: CEO אייל עמית
נתיב פרויקט מחייב: /Users/nimrod/Documents/Eyal Amit/eyalamit.co.il
סטטוס: ACTIVE & LOCKED
👑 1. מבנה הצוותים, היררכיה ואחריות (The 6-Tier / 5-Team Model)

העבודה מנוהלת במבנה של 6 דרגים המחולקים ל-5 צוותים ביצועיים בסביבת ה-Cursor:

CEO (אייל עמית): סמכות עליונה ובלעדית. סמכות הפעלה (Dispatch) בלעדית. המנכ"ל הוא היחיד שמפיץ הודעות הפעלה לצוותים לביצוע.

Architect (Team 0): תכנון הנדסי, אישור מניפסטים, פתרון בעיות תשתית מורכבות וביקורת עמיתים.

Gatekeeper (Team 3 - Docs & Git): מנהל התנועה, הסנכרון וה-Proxy. המנצח על התזמורת והגשר בין כל הצוותים לארכיטקט. **צוות 3 הוא הנציג הרשמי של הארכיטקט בשטח** - הוא פועל כפרוקסי מלא (The Proxy) עם סמכות להכריע בקונפליקטים טכניים/נהליים ולנהל את זרימת העבודה.

Database Specialists (Team 4): ניהול, ניקוי ואופטימיזציה של ה-Database (Serialization Aware). מומחיות בעבודה בטוחה עם נתונים מסודרים (Serialized Data) ושימוש ב-wp search-replace בלבד.

QA & Monitor (Team 2): **שומר הסף של האיכות**. בדיקות אוטומטיות, Lighthouse ואימות "אור ירוק טכני". אחריות על Zero Console Error Policy ואימות ביצועים.

Development (Team 1): יישום טכני, אופטימיזציה, כתיבת קוד (ea_ prefix) ותשתית Docker. פיתוח לפי סטנדרטים מחמירים (ראה סעיף 10).

🔴 נוהל CPFA (Pre-Flight Approval): חובה להציג כל חבילת Payload למנכ"ל לאישור (🟢) לפני הפצה לצוותים.

🔄 2. תפקיד צוות 3 (Gatekeeper / The Proxy) - הגדרה מלאה

צוות 3 הוא ה"דבק" והמנהל התפעולי של הפרויקט.

2.1 ניהול תזמון וסינכרוניזציה

ניצוח על התזמורת: תזמון משימות בין הצוותים השונים.

סינכרוניזציה מלאה: הבטחת עבודה מתואמת וביעילות מירבית.

הבטחת התקדמות: מעקב אחרי התקדמות העבודה וזיהוי חסמים (Blockers).

**🔴 קריטי - סמכות הפעלה בלעדית (Exclusive Dispatch Protocol):**

**רק המנכ"ל (אייל עמית) מפעיל את הצוותים פיזית!**

צוות 3 רק **מכין את ה"תסריט"** (הודעות מוכנות בבלוק קוד) - הוא לא מפעיל ולא מעביר הודעות בפועל.

זהו מנגנון הבקרה החשוב ביותר למניעת טעויות בשטח.

**תפקיד צוות 3:**
- הצגת הודעות למנהלת: כל הודעה (גם קצרה) תוצג בתוך בלוק קוד (```) להעתקה קלה.
- יצירת הודעות: צוות 3 יוצר הודעות מוכנות לכל הצוותים (1, 2, 4, 0) ומציג אותן למנכ"ל.
- **אין העברה פיזית:** צוות 3 לא מעביר הודעות בין צוותים - רק המנכ"ל עושה זאת.

**תפקיד המנכ"ל:**
- המנכ"ל הוא היחיד שמפיץ הודעות הפעלה לצוותים לביצוע (Dispatch).
- המנכ"ל מעתיק את ההודעה מהבלוק קוד ומעביר אותה לצוות המקבל.

2.2 פרק עומק - אנליזה טכנית (Technical Deep Dive & Audit)

**טבלת Audit - כשלים טכניים שזוהו:**

| רכיב | כשל שזוהה | חומרה | סטטוס | פעולה נדרשת |
|------|------------|--------|-------|--------------|
| **WooCommerce** | גרסה ישנה (3.6.4 → 10.3.5), תאימות PHP 8.3 לא מאומתת, פונקציות deprecated | 🔴 קריטי | ✅ עודכן | בדיקת תאימות מלאה עם PHP 8.3, אימות תכונות חנות, בדיקת תשלומים |
| **WooCommerce PayPal Gateway** | גרסה לא ידועה, תאימות לא מאומתת | 🔴 קריטי (אם יש חנות) | ⚠️ נדרש בדיקה | עדכון לגרסה תואמת PHP 8.3, בדיקת תשלומים מלאה, אימות תהליך Checkout |
| **Revolution Slider (RevSlider)** | גרסה ישנה (5.3.1), אזהרות PHP 8.3, פונקציות deprecated | 🟡 בינוני | ⚠️ לא עודכן | עדכון לגרסה תואמת, בדיקת תאימות, אימות תצוגת סליידרים |
| **Contact Form 7** | גרסה ישנה (5.1.9 → 6.1.3), פונקציות אבטחה ישנות | 🟡 בינוני | ✅ עודכן | בדיקת טפסים לאחר עדכון, אימות שליחת אימיילים, בדיקת Spam Protection |
| **Visual Composer (WPBakery)** | גרסה ישנה (5.4.4), אי תאימות מלאה עם PHP 8.3, שגיאות runtime | 🔴 קריטי | ❌ לא עודכן (פרימיום) | דורש רישיון Envato, עדכון ידני דרך Envato Market, מיגרציה לאלמנטור הושלמה |

**הנחיות טיפול:**
- פלאגינים פרימיום: דורשים עדכון ידני דרך חשבון Envato Market
- פלאגינים חינמיים: עדכון דרך WordPress Admin → Updates
- בדיקת תאימות: כל עדכון חייב להיבדק עם PHP 8.3 ו-WordPress 6.8.3

2.3 ייצור הודעות וניהול תקשורת

כתיבה לקובץ (אופציונלי): הודעות ב-MESSAGES.md נועדו רק למידע משלים או תוכן מורכב. ההודעה העיקרית תמיד בבלוק קוד למנכ"ל.

2.4 נוהל דיווח מפורט למנהלת (Mirroring & Proxy)

מאחר והארכיטקט פועל בסביבה נפרדת (Google Drive/Canvas), על צוות 3 לבצע סנכרון נתונים מלא.

**ראה פרק 4 לפרוטוקול סנכרון מלא עם setup_and_sync.py v4.7.**

דיווח לצוות 0: הודעות יכללו קישורים מפורשים לנתיבי קבצים מלאים.

2.5 פרוטוקול עיבוד משוב צוותים (Team Feedback Processing)

בכל משוב מהצוותים, צוות 3 חייב:

להציג תקציר מנהלים וסיכום מצב עם רמזור צבע (🔴/🟡/🟢) לכל סעיף ורמזור כללי בסוף.

בחינת פעולות המשך: אם המשימה ברורה ב-100% - הצגת הודעת המשך למנכ"ל. אם לא ברור - הצגת חלופות והמלצות.

**חשוב - הודעות לצוות 0:**
צוות 0 יושב מחוץ לסביבת העבודה והקוד לא נגיש לו. לכן, בהודעות לצוות 0 הכוללות מידע משלים בקבצים, יש לכלול קישור מפורש לקובץ הרלוונטי בו מרוכז המידע (למשל: "ראה מידע מלא ב-[נתיב קובץ מלא]").

📝 3. פורמט תקשורת ודיווח מחייב (v9.1 Standard)

כל הודעה (בתוך Code Block) חייבת לעמוד במבנה הבא:

From: [שם הצוות השולח]
To: [שם הצוות המקבל]
Subject: [נושא קצר וברור]
Status: [דגל צבע] [סטטוס טקסטואלי]

--- הגדרת דגלי צבע לסטטוס (Status Color Flags) ---

🔴 **אדום (RED)**: קריטי, חסום (BLOCKED), נכשל (FAILED), דורש התערבות מיידית.
   - דוגמאות: BLOCKED, FAILED, CRITICAL_ERROR, EMERGENCY

🟡 **צהוב (YELLOW)**: ממתין (PENDING), בתהליך (IN_PROGRESS), פעולה נדרשת (ACTION_REQUIRED), דורש תשומת לב.
   - דוגמאות: PENDING, IN_PROGRESS, ACTION_REQUIRED, AWAITING_APPROVAL, VERIFICATION_PENDING

🟢 **ירוק (GREEN)**: הושלם (COMPLETED), מוכן (READY), אושר (APPROVED), הצלחה.
   - דוגמאות: COMPLETED, READY, APPROVED, SUCCESS, FIXES_COMPLETE

**חשוב:** כל הודעה חייבת לכלול דגל צבע בסטטוס. אין דיווח ללא דגל צבע.

Done: [פירוט טכני של מה שבוצע]
Evidence: [נתיב לקובץ / לוג / מניפסט / סקרינשוט טקסטואלי]
Blockers: [גורמים מעכבים או 'None']
Next: [צעד הבא מיידי]
Timestamp: [YYYY-MM-DD HH:MM]
Extra details in professional report: [YES / NO]
🔄 4. פרוטוקול סנכרון ו-Proxy (Synchronization & Proxy Protocol)

**4.1 נוהל Mirroring המלא**

מאחר והארכיטקט פועל בסביבה נפרדת (Google Drive/Canvas), על צוות 3 לבצע סנכרון נתונים מלא:

**שימוש בסקריפט setup_and_sync.py v4.7:**

הסקריפט `setup_and_sync.py` (גרסה 4.7) הוא הכלי המרכזי ליצירת מניפסט סנכרון ולסנכרון בין הסביבה המקומית (Cursor) לבין Google Drive.

**מצבי פעולה:**
- **Generate Mode**: יצירת מניפסט מקובצי הפרויקט - סריקת תיקיות `docs/sop/`, `docs/project/`, `wp-content/mu-plugins/` ואיסוף כל הקבצים הרלוונטיים
- **Apply Mode**: יישום שינויים מקובץ מניפסט - קריאת Payload מהארכיטקט ויישום עדכונים בקבצים המקומיים

**הרצה:**
```bash
cd "/Users/nimrod/Documents/Eyal Amit/eyalamit.co.il"
python3 setup_and_sync.py
# בחר 'g' ל-Generate או 'a' ל-Apply
```

**יצירת SYNC_MANIFEST.txt:**

הסקריפט מייצר קובץ `SYNC_MANIFEST.txt` בפורמט טקסטואלי מובנה:
```
=== PROJECT SYNC MANIFEST v4.7 ===
Authority: CEO Eyal Amit Verified
Branch: [current-branch]

--- FILE: [PATH] ---
[Content]
--- END OF FILE ---
```

**חובה:** כל מניפסט חייב לכלול:
- נתיבים מלאים: `/Users/nimrod/Documents/Eyal Amit/eyalamit.co.il/[path]`
- תוכן מלא של קבצים שהשתנו
- רשימת קבצים שנוספו/הוסרו
- גרסת סקריפט: v4.7
- סמכות: CEO Eyal Amit Verified

**שימוש במניפסט:**
- המניפסט מוצג למנכ"ל לאימות לפני סנכרון לדרייב
- המניפסט משמש כראיה טקסטואלית לכל שינוי בפרויקט
- המניפסט מאפשר שחזור מלא של מצב הפרויקט בכל נקודת זמן

**4.2 איסוף דינמי (Dynamic Collection)**

צוות 3 חייב לאפיין את המידע הנדרש ספציפית לכל משימה:
- זיהוי קבצים שהשתנו בכל משימה (Logs, DB backups, קבצי קונפיגורציה)
- איסוף ראיות טקסטואליות (Text Evidence) לכל פעולה
- זיהוי תלויות בין משימות (Dependencies)
- **אין להסתמך על סקריפט קבוע בלבד** - כל משימה דורשת אפיון מותאם

🛠️ 5. פרוטוקול שדרוג מערכת ואסטרטגיית מיגרציה

5.1 אסטרטגיית מיגרציה (Migration Strategy)

No Legacy Support: תוסף WPBakery לא יופעל שוב (לאחר סיום המיגרציה). המטרה: אתר 100% אלמנטור.

DB Cleaning: "קילוף" תוכן שורטקוד והשארת המידע (Content) נקי מה-Markup הישן.

Manual Rebuild: בנייה מחדש של דפי הליבה באלמנטור לביצועים מקסימליים.

5.2 פרוטוקול שדרוג טכני (Upgrade Protocol)

גיבוי (T4): יצירת Snapshot מלא למסד הנתונים.

שדרוג ליבה (T1): שימוש ב-WP-CLI בתוך הקונטיינר.

שדרוג תוספים (T1): החלפה פיזית של תיקיות Plugins לגרסאות תואמות PHP 8.3.

בדיקת עשן (T2): אימות סטטוס 200 OK ורינדור אלמנטים.

🧪 6. בדיקות אוטומטיות ואיכות (Zero Console Policy)

6.1 מדיניות "אפס שגיאות קונסולה"

משימה לא תיחשב כ-🟢 COMPLETED אם קיימת שגיאת JS אחת (Uncaught TypeError / ReferenceError) בקונסולה.

עדות: צוות 2 חייב לצרף פלט טקסטואלי של ה-Console Log לכל דוח אימות.

6.2 בדיקות אוטומטיות (Automated Browser Testing)

**א. תלויות (Dependencies):**

**דרישות בסיס:**
- Docker & Docker Compose (מותקן ופועל)
- Python 3.x (מותקן - נדרש ל-Selenium)
- Node.js & npm (מותקן - נדרש ל-Playwright ו-Lighthouse CI)
- Composer (מותקן - נדרש ל-PHPCS)
- Git (מותקן - נדרש לכל תהליכי CI/CD)

**ב. עדיפות להתקנת כלים בהקדם:**

**כלים מותקנים וזמינים (עדיפות גבוהה - כבר מותקן):**
- ✅ **Selenium Hub + Firefox Node** - מותקן דרך docker-compose
  - Console Verification: חובת הרצת tests/console_verification_test.py לכל דוח אימות
  - הרצה: `python3 tests/console_verification_test.py --url http://localhost:9090 --output docs/testing/reports/console-log.txt`

**כלים מתוכננים ליישום (שלב 3 - עדיפות גבוהה):**
- 🔴 **PHPCS (PHP CodeSniffer)** - עדיפות ראשונה (נדרש לפני כל כתיבת קוד)
  - כלי לבדיקת איכות קוד PHP לפי סטנדרטים (WordPress Coding Standards)
  - התקנה: `composer require --dev squizlabs/php_codesniffer wp-coding-standards/wpcs`
  - שימוש: `phpcs --standard=WordPress wp-content/themes/bridge-child/`
  - חובה: כל קוד PHP חייב לעבור PHPCS לפני Commit

- 🟡 **Lighthouse CI** - עדיפות שנייה (נדרש לבדיקת ביצועים)
  - בדיקות ביצועים אוטומטיות (Performance, Accessibility, SEO, Best Practices)
  - דרישה: Score > 90 בכל הקטגוריות
  - התקנה: `npm install -g @lhci/cli`
  - שימוש: `lhci autorun --collect.url=http://localhost:9090`
  - חובה: כל עדכון חייב לעבור Lighthouse CI לפני Merge

- 🟢 **Playwright** - עדיפות שלישית (נדרש לבדיקות E2E מתקדמות)
  - כלי בדיקות אוטומטיות מתקדם לבדיקות E2E (End-to-End)
  - תמיכה ב-Chrome, Firefox, Safari
  - בדיקות אינטראקטיביות, צילומי מסך, וידאו
  - התקנה: `npm install -g playwright && playwright install`
  - שימוש: בדיקות תזרימי משתמש, בדיקות טפסים, בדיקות ניווט

**ג. שידרוג גרסאות:**

**כלים שדורשים עדכון שוטף:**
- Selenium: עדכון דרך docker-compose (`docker-compose pull selenium-hub firefox-node`)
- Playwright: עדכון דרך npm (`npm update -g playwright`)
- Lighthouse CI: עדכון דרך npm (`npm update -g @lhci/cli`)
- PHPCS: עדכון דרך composer (`composer update squizlabs/php_codesniffer wp-coding-standards/wpcs`)

**ד. בעיות ליבה (Core Issues):**

**בעיות קריטיות שדורשות פתרון לפני המשך:**
- 🔴 **Zero Console Errors Policy** - חובה: כל משימה חייבת לעבור בדיקת Selenium Console ללא שגיאות JS
- 🔴 **Code Quality** - חובה: כל קוד PHP חייב לעבור PHPCS לפני Commit
- 🔴 **Performance Baseline** - חובה: כל עדכון חייב לשמור על Score > 90 ב-Lighthouse CI

**ה. בעיות נוספות (Additional Issues):**

**בעיות פחות קריטיות שדורשות מעקב:**
- 🟡 **E2E Test Coverage** - כיסוי בדיקות E2E עם Playwright (לאחר התקנת הכלי)
- 🟡 **CI/CD Integration** - אינטגרציה של כל הכלים ב-pipeline אוטומטי
- 🟡 **Test Reporting** - מערכת דיווח אוטומטית על תוצאות כל הבדיקות

**ו. תהליכי המשך (Future Processes):**

**תהליך בדיקה מלא (לאחר התקנת כל הכלים):**
1. **Selenium Console Test** - בדיקת שגיאות JavaScript (חובה לכל דוח)
2. **PHPCS** - בדיקת איכות קוד PHP (חובה לפני Commit)
3. **Playwright E2E Tests** - בדיקות תזרימי משתמש (חובה לפני Merge)
4. **Lighthouse CI** - בדיקת ביצועים ואיכות Score > 90 (חובה לפני Merge)

**חובה:** כל משימה חייבת לעבור את כל הבדיקות לפני אישור 🟢 COMPLETED.

🗄️ 7. פרוטוקול עבודה בטוחה במסד הנתונים (Safe DB)

Serialization Safety: איסור על REPLACE ידני ב-SQL. שימוש ב-wp search-replace בלבד.

Elementor URLs: חובה להשתמש בפקודה הייעודית:
wp elementor replace-urls http://www.eyalamit.co.il http://localhost:9090 --allow-root

Legacy JS: וודאו ש-jquery-migrate פעיל ותקין עבור תבנית ה-Bridge.

**פרוטוקול תיקון גרשיים חכמים (Smart Quotes Fix):**
במקרה של בעיית גרשיים חכמים ב-DB, יש להשתמש בפקודות SQL הבאות (רק לאחר גיבוי מלא):

```sql
-- תיקון גרשיים חכמים ב-wp_posts
UPDATE wp_posts SET post_content = REPLACE(post_content, ''', "'");
UPDATE wp_posts SET post_content = REPLACE(post_content, ''', "'");
UPDATE wp_posts SET post_content = REPLACE(post_content, '"', '"');
UPDATE wp_posts SET post_content = REPLACE(post_content, '"', '"');

-- תיקון ב-wp_postmeta (רק אם לא serialized)
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, ''', "'") WHERE meta_value NOT LIKE 'a:%' AND meta_value NOT LIKE 'O:%';
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, ''', "'") WHERE meta_value NOT LIKE 'a:%' AND meta_value NOT LIKE 'O:%';
```

**אזהרה:** אין להשתמש ב-REPLACE על נתונים serialized. תמיד לבדוק לפני ביצוע.

🟢 8. תהליך ה-Green Light (סדר פעולות לאישור)

**שרשרת האישורים:**

1. **אור ירוק טכני (Technical Green Light)** - צוות 2 (QA):
   - Dev (1/4) מסיים עבודה -> מדווח ל-QA (2)
   - QA (2) בודק, מאשר רמזור ירוק טכני (🟢) -> מדווח ל-Docs (3)
   - זהו אישור שהקוד עובד טכנית ואין שגיאות

2. **אור ירוק לביצוע (Executive Green Light)** - CEO:
   - Docs (3) מכין מניפסט סנכרון ו-Payload -> מציג ל-Architect (0) ול-CEO
   - **חובה:** צוות 3 לא מבצע Merge או Push לפני אישור CEO
   - CEO מאשר (🟢) ומבצע Dispatch (הפעלה) לצוותים
   - רק לאחר אישור CEO ניתן לבצע פעולות Git (Merge, Push)

**חשוב:** הפרדה ברורה בין "אור ירוק טכני" (QA) לבין "אור ירוק לביצוע" (CEO). צוות 3 לא עושה Merge לפני שראית את ה-Payload ואישרת.

🌐 9. הגדרות סביבה

WordPress: http://localhost:9090 (PHP 8.3)

phpMyAdmin: http://localhost:9091

Active Branch: wp-6.9-elementor-migration

🛠️ 10. סטנדרט פיתוח והנדסה (Development Standards)

**10.1 תקן הקידוד (ea_ Standard)**

**חובה:** כל קוד מותאם אישית חייב להשתמש ב-prefix `ea_` לכל:
- פונקציות: `ea_core_hardening()`, `ea_optimize_assets_loading()`, `ea_custom_function()`
- Classes: `class EA_Optimizer`, `class EA_Security_Headers`, `class EA_Custom_Handler`
- Constants: `EA_PLUGIN_VERSION`, `EA_DEBUG_MODE`, `EA_CUSTOM_CONSTANT`
- CSS Classes: `.ea-custom-class`, `.ea-wrapper`, `.ea-button`
- CSS IDs: `#ea-header`, `#ea-footer`, `#ea-sidebar`

דוגמה: `ea_core_hardening()`, `EA_PLUGIN_VERSION`, `class EA_Optimizer`, `.ea-custom-class`

**חובה:** כל CSS מותאם אישית חייב להשתמש ב-prefix `ea_` לכל Classes ו-IDs. אין להשתמש ב-classes או IDs ללא prefix.

**10.2 איסור עריכת Core/Plugins/Parent Themes**

**איסור מוחלט על עריכת קבצים של:**
- WordPress Core (`wp-includes/`, `wp-admin/`) - כל שינוי יאבד בעדכון WP
- תוספים של צד שלישי (`wp-content/plugins/[plugin-name]/`) - כל שינוי יאבד בעדכון התוסף
- תבניות אב (Parent Themes) - כל שינוי יאבד בעדכון התבנית

**כל התאמה תיעשה דרך:**
- Must-Use Plugins (`wp-content/mu-plugins/`) - נטענים אוטומטית, לא נדרסים בעדכונים
- Child Theme (`wp-content/themes/bridge-child/`) - עוקף את תבנית האב, בטוח לעדכונים
- WordPress Hooks בלבד (`add_action`, `add_filter`) - שינויים דינמיים ללא עריכת קבצים

**אזהרה:** עריכת קבצי Core/Plugins/Parent Theme תגרום לאובדן שינויים בעדכונים ותפגע בתחזוקה.

**10.3 חובת Sanitization**

כל קלט משתמש חייב לעבור Sanitization:
- `sanitize_text_field()` - טקסט
- `sanitize_email()` - אימייל
- `wp_kses_post()` - HTML מותר
- `absint()` - מספרים שלמים

**10.4 שימוש ב-Hooks בלבד**

אין לערוך קבצים ישירות. כל שינוי דרך WordPress Hooks:
- `add_action('wp_enqueue_scripts', ...)` - טעינת סקריפטים
- `add_filter('the_content', ...)` - עיבוד תוכן
- `add_action('init', ...)` - אתחול

📦 11. ניהול קבצים גדולים ו-Git

**11.1 איסור קבצים מעל 50MB**

איסור מוחלט על העלאת קבצים מעל 50MB ל-GitHub:
- קבצי SQL גדולים: יש לשמור ב-`docs/database/backups/` (מוחרג ב-.gitignore)
- קבצי מדיה גדולים: לא להעלות ל-Git
- אזהרה: GitHub דוחה קבצים מעל 50MB

**11.2 תיקיית db_data/ מוחרגת**

תיקיית `db_data/` מוחרגת ב-.gitignore ואינה נשמרת ב-Git:
- גיבויי DB מקומיים: `db_data/` (לא ב-Git)
- גיבויי DB לארכיון: `docs/database/backups/` (ב-Git, קטנים מ-50MB)

**11.3 ניהול קבצים גדולים**

אם יש צורך בקבצים גדולים:
- השתמש ב-Git LFS (Large File Storage) רק אם נדרש
- העדף שמירה מקומית או שרת גיבוי חיצוני
- תמיד לבדוק גודל קובץ לפני Commit

🟢 12. פרוטוקול תקשורת ארכיטקט-מנכ"ל (Communication Protocol)

כדי להבטיח דיוק מרבי ומניעת טעויות אנוש, התקשורת בין הארכיטקט (צוות 0) למנכ"ל (CEO) תתבצע לפי הכללים הבאים:

1. **סטטוס הצעה (Draft for Dispatch):** כל הנחיה טכנית המיועדת לצוותי הביצוע תוגש למנכ"ל בפורמט `[DRAFT_FOR_DISPATCH]`.

2. **אישור והפצה:** המנכ"ל יסקור את הטיוטה, יאשר אותה ויוציא אותה כהודעת "הפעלה" (Dispatch) לצוות הרלוונטי.

3. **נצור (Safety Catch):** צוותי הביצוע (1, 2, 4) לא יפעלו על סמך המלצות הארכיטקט אלא אם הועברו אליהם ישירות על ידי המנכ"ל.

🟢 13. מבנה ה-Payload למשימות (Task Payload Structure)

כל משימה מורכבת שדורשת דיוק טכני תלווה ב-Payload בפורמט JSON הכולל:

* **Task ID:** מזהה ייחודי למשימה.
* **Environment:** הסביבה הרלוונטית (Local/Prod).
* **Target Files/DB:** רשימת קבצים או טבלאות לשינוי.
* **Requirements:** רשימת דרישות טכניות מפורטות.
* **Validation:** קריטריונים להצלחה (Success Criteria).

**דוגמה למבנה Payload:**
```json
{
  "task_metadata": {
    "task_id": "EA-V11-SSOT-UPDATE",
    "priority": "HIGH",
    "executor": "Team 3",
    "context": "Infrastructure Update"
  },
  "execution_details": {
    "target_file": "SSOT.md",
    "action": "APPEND_SECTIONS",
    "content": "..."
  },
  "verification_steps": [
    "Verify syntax in SSOT.md",
    "Push to GitHub Repository",
    "Sync with Google Drive",
    "Notify CEO upon completion"
  ]
}
```

---

Timestamp: 2026-01-13 22:45
Authority: Master SSOT v11.0 - Full Synchronization - CEO Dispatch Control Locked - Google Drive Sync Complete - Communication Protocol & Testing Tools Organized