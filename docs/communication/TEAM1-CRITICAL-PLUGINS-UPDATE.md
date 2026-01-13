# 🛠️ הודעה לצוות 1 (Development) - עדכון תוספים קריטיים
**From:** צוות 3 (Gatekeeper - Docs & Git)  
**To:** צוות 1 (Development)  
**Subject:** עדכון דחוף - 3 תוספים קריטיים  
**Status:** 🟡 READY_TO_START  
**Task ID:** EA-V11-CRITICAL-PLUGINS-UPDATE  
**עדיפות:** 🔴 גבוהה מאוד (קריטי לפני פריסה)

---

## 📋 קונטקסט ומטרות

### המטרה:
לעדכן את 3 התוספים הקריטיים לגרסאות האחרונות לפני פריסה לייצור.

### התוספים לעדכון:
1. **Site Kit by Google** - 1.43.0 → 1.170.0 (127 גרסאות!)
2. **Yoast SEO** - 11.4 → 26.7 (15 גרסאות!)
3. **Elementor** - 3.25.10 → 3.34.1 (9 גרסאות)

---

## 🎯 המשימות שלך

### ✅ משימה 1.1: גיבוי לפני עדכון
**זמן משוער:** 15 דקות  
**עדיפות:** 🔴 קריטי

**פעולות:**
1. גיבוי מסד נתונים:
   ```bash
   docker compose exec db mysqldump -u eyalamit_user -puser_password eyalamit_db > backups/pre-plugin-update-$(date +%Y%m%d-%H%M%S).sql
   ```

2. גיבוי תיקיית plugins:
   ```bash
   tar -czf backups/wp-content-plugins-backup-$(date +%Y%m%d-%H%M%S).tar.gz wp-content/plugins/
   ```

3. רישום גרסאות נוכחיות:
   - Site Kit: 1.43.0
   - Yoast SEO: 11.4
   - Elementor: 3.25.10

**תוצאה צפויה:**
- גיבוי מסד נתונים נשמר
- גיבוי תיקיית plugins נשמר
- רשימת גרסאות נוכחיות מתועדת

**דוח נדרש:**
- קובץ: `docs/testing/reports/plugins-update-task1.1-backup-report.md`
- לכלול: נתיבי קבצי גיבוי, גרסאות נוכחיות, תאריך ושעה

---

### ✅ משימה 1.2: עדכון Site Kit by Google
**זמן משוער:** 30 דקות  
**עדיפות:** 🔴 קריטי

**פעולות:**
1. בדיקה לפני עדכון:
   - בדוק שהאתר עובד תקין
   - בדוק שאין שגיאות Console
   - בדוק שהפלאגין פעיל

2. עדכון הפלאגין:
   - דרך Admin → Plugins → Site Kit by Google → Update Now
   - או דרך WP-CLI:
     ```bash
     docker compose exec wordpress wp plugin update google-site-kit --allow-root
     ```

3. בדיקה אחרי עדכון:
   - בדוק שהאתר עובד תקין
   - בדוק שאין שגיאות Console
   - בדוק שהפלאגין פעיל
   - בדוק שהגרסה עודכנה ל-1.170.0

**תוצאה צפויה:**
- Site Kit עודכן ל-1.170.0
- האתר עובד תקין
- אין שגיאות Console

**דוח נדרש:**
- קובץ: `docs/testing/reports/plugins-update-task1.2-sitekit-report.md`
- לכלול: גרסה לפני, גרסה אחרי, בעיות שזוהו, תוצאות בדיקה

---

### ✅ משימה 1.3: עדכון Yoast SEO
**זמן משוער:** 30 דקות  
**עדיפות:** 🔴 קריטי

**פעולות:**
1. בדיקה לפני עדכון:
   - בדוק שהאתר עובד תקין
   - בדוק שאין שגיאות Console
   - בדוק שהפלאגין פעיל
   - בדוק שה-sitemap עובד (`http://localhost:9090/sitemap_index.xml`)

2. עדכון הפלאגין:
   - דרך Admin → Plugins → Yoast SEO → Update Now
   - או דרך WP-CLI:
     ```bash
     docker compose exec wordpress wp plugin update wordpress-seo --allow-root
     ```

3. בדיקה אחרי עדכון:
   - בדוק שהאתר עובד תקין
   - בדוק שאין שגיאות Console
   - בדוק שהפלאגין פעיל
   - בדוק שהגרסה עודכנה ל-26.7
   - בדוק שה-sitemap עובד (`http://localhost:9090/sitemap_index.xml`)

**תוצאה צפויה:**
- Yoast SEO עודכן ל-26.7
- האתר עובד תקין
- אין שגיאות Console
- Sitemap עובד תקין

**דוח נדרש:**
- קובץ: `docs/testing/reports/plugins-update-task1.3-yoast-report.md`
- לכלול: גרסה לפני, גרסה אחרי, בעיות שזוהו, תוצאות בדיקה, סטטוס sitemap

---

### ✅ משימה 1.4: עדכון Elementor
**זמן משוער:** 30 דקות  
**עדיפות:** 🔴 קריטי

**פעולות:**
1. בדיקה לפני עדכון:
   - בדוק שהאתר עובד תקין
   - בדוק שאין שגיאות Console
   - בדוק שהפלאגין פעיל
   - בדוק שדפים שנבנו ב-Elementor עובדים

2. עדכון הפלאגין:
   - דרך Admin → Plugins → Elementor → Update Now
   - או דרך WP-CLI:
     ```bash
     docker compose exec wordpress wp plugin update elementor --allow-root
     ```

3. בדיקה אחרי עדכון:
   - בדוק שהאתר עובד תקין
   - בדוק שאין שגיאות Console
   - בדוק שהפלאגין פעיל
   - בדוק שהגרסה עודכנה ל-3.34.1
   - בדוק שדפים שנבנו ב-Elementor עובדים
   - בדוק שהעורך Elementor נפתח תקין

**תוצאה צפויה:**
- Elementor עודכן ל-3.34.1
- האתר עובד תקין
- אין שגיאות Console
- דפים שנבנו ב-Elementor עובדים

**דוח נדרש:**
- קובץ: `docs/testing/reports/plugins-update-task1.4-elementor-report.md`
- לכלול: גרסה לפני, גרסה אחרי, בעיות שזוהו, תוצאות בדיקה, בדיקת דפים

---

### ✅ משימה 1.5: בדיקה מקיפה אחרי כל העדכונים
**זמן משוער:** 30 דקות  
**עדיפות:** 🔴 קריטי

**פעולות:**
1. בדיקת האתר הכללית:
   - בדוק שהאתר נטען תקין
   - בדוק שאין שגיאות Console
   - בדוק שאין שגיאות PHP
   - בדוק שאין שגיאות Database

2. בדיקת פונקציונליות:
   - בדוק דפים חשובים (ראשי, אודות, וכו')
   - בדוק דפים שנבנו ב-Elementor
   - בדוק שהתבנית Bridge עובדת תקין
   - בדוק שהתפריטים עובדים

3. בדיקת תוספים:
   - Site Kit: בדוק שהחיבור ל-Google Analytics עובד
   - Yoast SEO: בדוק שה-sitemap עובד, בדוק SEO settings
   - Elementor: בדוק שהעורך עובד, בדוק דפים שנבנו

**תוצאה צפויה:**
- כל העדכונים הושלמו בהצלחה
- האתר עובד תקין
- כל התוספים עובדים תקין
- אין שגיאות

**דוח נדרש:**
- קובץ: `docs/testing/reports/plugins-update-task1.5-final-check-report.md`
- לכלול: סיכום כל העדכונים, תוצאות בדיקות, בעיות שזוהו (אם יש)

---

## 📊 קריטריוני הצלחה

המשימה תיחשב מושלמת רק אם:

### ✅ קריטריון 1: כל התוספים עודכנו
- Site Kit: 1.170.0 ✅
- Yoast SEO: 26.7 ✅
- Elementor: 3.34.1 ✅

### ✅ קריטריון 2: האתר עובד תקין
- האתר נטען תקין
- אין שגיאות Console
- אין שגיאות PHP
- אין שגיאות Database

### ✅ קריטריון 3: כל התוספים עובדים
- Site Kit: פעיל ועובד
- Yoast SEO: פעיל ועובד, sitemap עובד
- Elementor: פעיל ועובד, דפים שנבנו עובדים

### ✅ קריטריון 4: Zero Console Errors
- 0 שגיאות JavaScript
- 0 שגיאות CORS
- 0 שגיאות רשת

---

## 📁 קבצים רלוונטיים

### קבצים לבדיקה:
- `wp-content/plugins/google-site-kit/` - Site Kit
- `wp-content/plugins/wordpress-seo/` - Yoast SEO
- `wp-content/plugins/elementor/` - Elementor

### קבצי גיבוי:
- `backups/pre-plugin-update-*.sql` - גיבוי מסד נתונים
- `backups/wp-content-plugins-backup-*.tar.gz` - גיבוי plugins

---

## 📝 דרישות דיווח

כל משימה חייבת לכלול דוח לפי הפורמט הבא:

```
From: צוות 1 (Development)
To: צוות 3 (Gatekeeper)
Subject: [שם המשימה] - [סטטוס]
Status: [PENDING/IN_PROGRESS/COMPLETED/FAILED/BLOCKED]
Done: [פירוט טכני של מה שבוצע]
Evidence: [נתיב לקובץ/לוג/דוח]
Blockers: [גורמים מעכבים או 'None']
Next: [צעד הבא מיידי]
Timestamp: [YYYY-MM-DD HH:MM]
Extra details in professional report: YES
```

---

## ⚠️ הערות חשובות

1. **גיבוי לפני כל עדכון:**
   - חובה לבצע גיבוי לפני כל עדכון
   - אם משהו משתבש, אפשר לחזור לגרסה הקודמת

2. **עדכון אחד בכל פעם:**
   - עדכן תוסף אחד בכל פעם
   - בדוק שהכל עובד לפני מעבר לתוסף הבא

3. **בדיקות מקיפות:**
   - בדוק את האתר אחרי כל עדכון
   - בדוק שאין שגיאות Console
   - בדוק שהפונקציונליות עובדת

4. **תיעוד:**
   - תיעד כל בעיה שזוהתה
   - תיעד כל פתרון שבוצע
   - תיעד את תוצאות הבדיקות

---

## 🚀 התחלה

**התחל במשימה 1.1: גיבוי לפני עדכון**

לאחר השלמת כל המשימות, שלח דוח סופי לצוות 3 (Gatekeeper) עם כל התוצאות.

---

**נוצר על ידי:** צוות 3 (Gatekeeper - Docs & Git)  
**תאריך:** 2026-01-14  
**Task ID:** EA-V11-CRITICAL-PLUGINS-UPDATE  
**סטטוס:** 🟡 READY_TO_START
