# 🛠️ הודעה לצוות 1 (Development) - תיקון תקלות מפת אתר

**From:** צוות 3 (Gatekeeper - Docs & Git)  
**To:** צוות 1 (Development)  
**Subject:** תיקון תקלות מפת אתר - שאינן קבצים  
**Status:** 🟡 READY_TO_START  
**Task ID:** EA-V11-SITEMAP-ERRORS-FIX  
**עדיפות:** 🔴 גבוהה (קריטי לפריסה)

---

## 📋 קונטקסט ומטרות

### המטרה:
לתקן את כל התקלות במפת האתר שאינן קבצים (Attachments), כדי להבטיח ש-**100% מהעמודים פעילים** לפני פריסה לייצור.

### היקף הבעיה:
- **סה"כ תקלות:** 228 URLs עם בעיות
- **סוג בעיה:** כל התקלות הן redirects שמפנים לכתובת שגויה (`localhost:80` במקום `localhost:9090`)
- **קבצים:** לא נכללים - רק עמודים, קטגוריות, תגיות, Portfolio, וכו'

### פילוח התקלות:
- **Other:** 121 תקלות (53%)
- **Tag:** 48 תקלות (21%)
- **Portfolio:** 28 תקלות (12%)
- **Category:** 17 תקלות (7%)
- **Page:** 12 תקלות (5%)
- **Author:** 2 תקלות (1%)

### פילוח לפי First Path Segment:
- **Blog:** 148 תקלות
- **qr:** 50 תקלות
- **דיגרידו:** 11 תקלות
- **shop:** 5 תקלות
- ועוד...

---

## 🎯 המשימות שלך

### ✅ משימה 1.1: בדיקת הכללים הקיימים
**זמן משוער:** 30 דקות  
**עדיפות:** 🔴 גבוהה

**פעולות:**
1. לבדוק שהקובץ `wp-content/themes/bridge-child/functions-redirects.php` נטען נכון
   - לבדוק שהוא נטען דרך `functions.php`
   - לבדוק שאין שגיאות PHP

2. לבדוק שהכללים הכלליים עובדים:
   ```bash
   # בדיקת Blog redirect
   curl -I http://localhost:9090/Blog/test-post
   
   # בדיקת Shop redirect
   curl -I http://localhost:9090/shop
   
   # בדיקת QR redirect
   curl -I http://localhost:9090/qr
   ```

3. לבדוק שה-`wp-config.php` מגדיר נכון את ה-URLs לפי סביבה:
   - Development: `http://localhost:9090`
   - Staging: `http://eyalamit-co-il-2026.s887.upress.link`
   - Production: `https://eyalamit.co.il`

**תוצאה צפויה:**
- דוח בדיקה עם דוגמאות של URLs שנבדקו
- רשימת כללים שפועלים
- רשימת כללים שצריך לתקן/להוסיף

**דוח נדרש:**
- קובץ: `docs/testing/reports/sitemap-errors-task1.1-check-report.md`
- לכלול: דוגמאות של URLs שנבדקו, רשימת כללים שפועלים, רשימת כללים שצריך לתקן

---

### ✅ משימה 1.2: תיקון/הוספת כללים כלליים
**זמן משוער:** 1-2 שעות  
**עדיפות:** 🔴 גבוהה

**פעולות:**
1. לנתח את התקלות לפי `First_Path_Segment`:
   - לפתוח את `docs/testing/reports/sitemap-errors-non-attachments.json`
   - לזהות דפוסים חוזרים בנתיבים
   - לזהות קטגוריות/תגיות/Portfolio שצריכות כללים

2. להוסיף/לתקן כללים כלליים ב-`functions-redirects.php`:
   - **כללים לקטגוריות (Category)** - 17 תקלות
   - **כללים לתגיות (Tag)** - 48 תקלות
   - **כללים ל-Portfolio** - 28 תקלות
   - **כללים ל-Author pages** - 2 תקלות
   - **כללים ל-Other** - 121 תקלות (לנתח לפי First Path Segment)

3. לוודא שכל הכללים עובדים נכון:
   - בדיקת redirects עם `curl -I`
   - בדיקת URLs נכונים
   - בדיקת multi-environment support

**תוצאה צפויה:**
- כל הכללים הכלליים מוטמעים ועובדים
- כל ה-redirects מפנים לכתובת נכונה

**דוח נדרש:**
- קובץ: `docs/testing/reports/sitemap-errors-task1.2-fix-report.md`
- לכלול: רשימת כללים שנוספו/תוקנו, דוגמאות של URLs שנבדקו, הסבר על כל כלל חדש

---

### ✅ משימה 1.3: תיקון Multi-Environment Support
**זמן משוער:** 30 דקות  
**עדיפות:** 🔴 גבוהה

**פעולות:**
1. לבדוק שה-`wp-config.php` מגדיר נכון את ה-URLs:
   - Development: `http://localhost:9090`
   - Staging: `http://eyalamit-co-il-2026.s887.upress.link`
   - Production: `https://eyalamit.co.il`

2. לבדוק שה-`wp_redirect` filter עובד נכון:
   - תיקון `localhost` → `localhost:9090` ב-Development
   - תיקון URLs ב-Staging/Production

3. לבדוק שה-`home_url()` ו-`site_url()` מחזירים ערכים נכונים:
   ```php
   // בדיקה ב-Development
   echo home_url(); // צריך להחזיר: http://localhost:9090
   echo site_url(); // צריך להחזיר: http://localhost:9090
   ```

**תוצאה צפויה:**
- כל ה-URLs נכונים בכל סביבה
- כל ה-redirects מפנים לכתובת נכונה

**דוח נדרש:**
- קובץ: `docs/testing/reports/sitemap-errors-task1.3-env-report.md`
- לכלול: דוח בדיקה של כל סביבה, דוגמאות של URLs שנבדקו בכל סביבה

---

### ✅ משימה 1.4: בדיקה חוזרת של כל ה-URLs
**זמן משוער:** 1 שעה  
**עדיפות:** 🔴 גבוהה

**פעולות:**
1. להריץ את הסקריפט `scripts/validate_sitemap_pages.php` מחדש:
   ```bash
   php scripts/validate_sitemap_pages.php
   ```

2. לבדוק שכל ה-URLs שאינם קבצים עובדים:
   - סטטוס: `OK` (לא `ERROR`)
   - HTTP Code: `200` (לא `301` עם שגיאה)

3. לזהות תקלות שנותרו:
   - URLs שעדיין לא עובדים
   - דפוסים חדשים שצריך לטפל בהם

**תוצאה צפויה:**
- **100% מהעמודים שאינם קבצים עובדים** (0 תקלות)
- כל ה-redirects מפנים נכון

**דוח נדרש:**
- קובץ: `docs/testing/reports/sitemap-errors-task1.4-validation-report.md`
- לכלול: דוח בדיקה חוזרת עם תוצאות, רשימת URLs שעדיין לא עובדים (אם יש), תוכנית תיקון לתקלות שנותרו

---

## 📊 קריטריוני הצלחה

המשימה תיחשב מושלמת רק אם:

### ✅ קריטריון 1: כל העמודים עובדים
- **100% מהעמודים שאינם קבצים עובדים** (0 תקלות)
- כל ה-URLs מחזירים HTTP 200 או 301 נכון

### ✅ קריטריון 2: כל ה-Redirects נכונים
- כל ה-redirects מפנים לכתובת נכונה
- לא redirects ל-`localhost:80`
- כן redirects ל-`localhost:9090` (בסביבת Development)

### ✅ קריטריון 3: כללים כלליים עובדים
- כל הכללים הכלליים מוטמעים ועובדים
- לא redirects בודדים אלא כללים כלליים

### ✅ קריטריון 4: Multi-Environment Support
- האתר עובד נכון בכל סביבה
- כל ה-URLs נכונים בכל סביבה

---

## 📁 קבצים רלוונטיים

### קבצים לבדיקה/תיקון:
- `wp-content/themes/bridge-child/functions-redirects.php` - כללים כלליים להפניות
- `wp-content/themes/bridge-child/functions.php` - טעינת functions-redirects.php
- `wp-config.php` - Multi-Environment Support
- `scripts/validate_sitemap_pages.php` - סקריפט בדיקת תקינות

### קבצי נתונים:
- `docs/sitemap/SITEMAP-v1.0-2026-01-14.csv` - קובץ CSV עם כל הנתונים
- `docs/testing/reports/sitemap-errors-non-attachments.json` - ניתוח תקלות
- `docs/testing/reports/sitemap-validation-results.json` - תוצאות בדיקה

### קבצי תיעוד:
- `docs/communication/DISPATCH-SITEMAP-ERRORS-FIX.md` - מסמך Dispatch מפורט
- `docs/testing/reports/sitemap-errors-task-breakdown.md` - פירוט משימות

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

1. **קבצים לא נכללים:**
   - רק עמודים, קטגוריות, תגיות, וכו'
   - לא Attachments (תמונות, קבצים)

2. **כללים כלליים:**
   - לא redirects בודדים אלא כללים כלליים
   - קל לתחזוקה ועדכון

3. **Multi-Environment:**
   - האתר חייב לעבוד נכון בכל סביבה
   - URLs חייבים להיות נכונים בכל סביבה

4. **בדיקה:**
   - כל שינוי חייב להיבדק עם `curl -I`
   - כל שינוי חייב להיבדק עם הסקריפט `validate_sitemap_pages.php`

---

## 🚀 התחלה

**התחל במשימה 1.1: בדיקת הכללים הקיימים**

לאחר השלמת כל המשימות, שלח דוח סופי לצוות 3 (Gatekeeper) עם כל התוצאות.

---

**נוצר על ידי:** צוות 3 (Gatekeeper - Docs & Git)  
**תאריך:** 2026-01-14  
**Task ID:** EA-V11-SITEMAP-ERRORS-FIX  
**סטטוס:** 🟡 READY_TO_START
