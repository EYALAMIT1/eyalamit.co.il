# תוכנית בדיקות מקיפה לפני ייצור

## סקירה כללית

סקריפט זה מבצע בדיקות אוטומטיות מקיפות על האתר לפני העלאה לייצור.

## איך להריץ את הבדיקות

### דרך 1: הרצה ישירה (Windows)
```bash
RUN-COMPREHENSIVE-CHECKS.bat
```

### דרך 2: הרצה ידנית
```bash
python comprehensive-site-check.py
```

## מה הסקריפט בודק?

### 1. Docker & Infrastructure
- בדיקת containers (wordpress, nginx, db, phpmyadmin)
- בדיקת health status
- בדיקת פורטים (8080, 8081)

### 2. WordPress Core
- גרסת WordPress (צפוי: 6.8.3)
- גרסת מסד נתונים (צפוי: 60421)
- הגדרות wp-config.php (WP_DEBUG, DISALLOW_FILE_EDIT, WP_MEMORY_LIMIT)
- קישוריות למסד נתונים
- גרסת PHP (צפוי: 8.2)

### 3. פלאגינים
- רשימת כל הפלאגינים הפעילים
- בדיקת גרסאות פלאגינים קריטיים (Google Site Kit, Yoast SEO, WooCommerce)
- בדיקת פלאגינים עם עדכונים זמינים
- בדיקת התנגשויות פלאגינים
- בדיקת פלאגין Cookie Consent Notice (פעיל וקובץ קיים)

### 4. הודעת הקוקיז (Cookie Notice)
- בדיקה שהפלאגין `cookie-consent-notice` פעיל
- בדיקה שקובץ הפלאגין קיים ובר תקינות
- בדיקה שהפונקציות הנדרשות קיימות
- בדיקה שהקוד JavaScript נטען
- בדיקה שהקוד CSS נטען
- בדיקה שה-HTML של ההודעה מופיע בדף

### 5. נגישות האתר
- נגישות דף הבית
- נגישות Admin
- נגישות REST API
- נגישות דפים חשובים

### 6. שגיאות
- סריקת לוגים של WordPress
- בדיקת debug.log
- בדיקת לוגים של Nginx
- זיהוי שגיאות PHP קריטיות

### 7. תאימות
- תאימות PHP עם WordPress 6.8.3
- תאימות מסד נתונים
- תאימות פלאגינים

### 8. ביצועים
- זמן טעינת דף
- גודל תגובה

### 9. אבטחה
- WP_DEBUG מושבת (להפקה)
- DISALLOW_FILE_EDIT מופעל

### 10. WooCommerce
- בדיקת טבלאות מסד נתונים
- בדיקת REST API

## קבצי דוח

לאחר ההרצה, נוצרים 3 קבצי דוח:

1. **COMPREHENSIVE-CHECK-REPORT-{timestamp}.md** - דוח Markdown
2. **COMPREHENSIVE-CHECK-REPORT-{timestamp}.json** - דוח JSON לניתוח ממוחשב
3. **COMPREHENSIVE-CHECK-REPORT-{timestamp}.html** - דוח HTML ויזואלי

## דרישות

- Python 3.6+
- Docker ו-Docker Compose מותקנים
- כל ה-containers רצים (`docker compose up -d`)
- חבילת `requests` מותקנת: `pip install requests`

## הערות

- הסקריפט מניח שהאתר רץ על `http://localhost:8080`
- נתיבי הקבצים מותאמים לפרויקט הנוכחי (`eyalamit.co.il_bm1763848352dm`)
- הבדיקות אורכות כ-5-10 דקות

## בדיקות ידניות נוספות

לאחר הבדיקות האוטומטיות, מומלץ לבצע בדיקות ידניות:

1. **Console Errors** - פתח F12 בדפדפן ובדוק Console
2. **Network Errors** - בדוק Network tab בדפדפן
3. **מראה ויזואלי** - ודא שהאתר נראה נכון
4. **Mobile/Responsive** - בדוק שהאתר נראה טוב במובייל
5. **פונקציונליות מלאה של הודעת קוקיז** - ודא שההודעה מופיעה, אפשר לבחור, ולאחר אישור היא נעלמת

