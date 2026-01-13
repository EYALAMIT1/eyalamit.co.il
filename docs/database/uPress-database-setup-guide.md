# 🗄️ מדריך: יצירת בסיס נתונים בסביבת uPress
**תאריך:** 2026-01-14
**סביבה:** http://eyalamit-co-il-2026.s887.upress.link (חדשה וריקה)

---

## 🎯 מטרה

יצירת בסיס נתונים חדש בסביבת הבדיקות של uPress וייבוא הנתונים מהסביבה המקומית.

---

## 📋 צעדי ההכנה

### שלב 1: התחברות ל-uPress
1. פתח דפדפן והתחבר ל: `https://upress.co.il`
2. התחבר עם פרטי הגישה שלך
3. עבור לניהול האתר: `eyalamit-co-il-2026.s887.upress.link`

---

## 🗄️ יצירת בסיס הנתונים

### שלב 2: יצירת בסיס נתונים חדש
1. בחלונית השליטה, לחץ על **"MySQL Databases"**
2. בקטע **"Create New Database"**:
     - **Database Name:** `sb0228693_stagin`
   - **Database Username:** `sb0228693_user`
   - **Password:** `Staging2026!` (עומד בדרישות: אות קטנה, גדולה, מספר, תו מיוחד)
   - לחץ **"Create Database"**

3. המערכת תציג הודעה:
   ```
   ✅ Database "sb0228693_staging" created successfully!
   ✅ User "sb0228693_user" created successfully!
   ```

---

## 📥 ייבוא הנתונים

### שלב 3: התחברות ל-phpMyAdmin
1. בחלונית השליטה, לחץ על **"phpMyAdmin"**
2. התחבר עם פרטי המשתמש שיצרת
3. ברשימת בסיסי הנתונים, לחץ על `sb0228693_stagin`

### שלב 4: ייבוא הקובץ
1. לחץ על הלשונית **"Import"** (יבוא)
2. בקטע **"File to import"**:
   - לחץ **"Choose File"**
   - בחר את הקובץ: `database_export_pre_deployment_20260114_002527.sql`
   - **חשוב:** וודא שהקובץ נמצא במחשב שלך

3. הגדרות היבוא:
   - **Format:** SQL (אוטומטי)
   - **Character set:** utf8
   - **Partial import:** לא מסומן
   - **Other options:** ברירת מחדל

4. לחץ **"Go"** (התחל את היבוא)

### שלב 5: המתנה ליבוא
- תהליך היבוא עלול לקחת כמה דקות
- אל תסגור את הדף בזמן היבוא
- תראה התקדמות: `Importing...`

### שלב 6: אימות היבוא
אחרי שהיבוא מסתיים, תראה:
```
✅ Import has been successfully finished
```

בדוק:
- מספר הטבלאות: 71 טבלאות
- מספר הרשומות: כ-25,000+ רשומות

---

## 🔧 עדכון הגדרות WordPress

### שלב 7: עדכון wp-config.php
בסביבת הבדיקות, צור/עדכן את `wp-config.php`:

```php
// Database settings for staging
define('DB_NAME', 'sb0228693_stagin');
define('DB_USER', 'sb0228693_user');
define('DB_PASSWORD', 'Staging2026!');
define('DB_HOST', 'localhost');

// Site URLs for staging
define('WP_HOME', 'http://eyalamit-co-il-2026.s887.upress.link');
define('WP_SITEURL', 'http://eyalamit-co-il-2026.s887.upress.link');
```

### שלב 8: עדכון URLs בבסיס הנתונים
ב-phpMyAdmin, הרץ את הסקריפט:

```sql
-- בחלונית SQL של phpMyAdmin:
SOURCE update-database-urls-staging.sql;
```

או הרץ ידנית:

```sql
UPDATE wp_options SET option_value = 'http://eyalamit-co-il-2026.s887.upress.link' WHERE option_name = 'siteurl';
UPDATE wp_options SET option_value = 'http://eyalamit-co-il-2026.s887.upress.link' WHERE option_name = 'home';

UPDATE wp_posts SET post_content = REPLACE(post_content, 'https://www.eyalamit.co.il', 'http://eyalamit-co-il-2026.s887.upress.link');
UPDATE wp_posts SET guid = REPLACE(guid, 'https://www.eyalamit.co.il', 'http://eyalamit-co-il-2026.s887.upress.link');
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'https://www.eyalamit.co.il', 'http://eyalamit-co-il-2026.s887.upress.link');
```

---

## ✅ בדיקות אחרונות

### שלב 9: בדיקת האתר
1. פתח דפדפן לכתובת: `http://eyalamit-co-il-2026.s887.upress.link`
2. בדוק:
   - האתר נטען ✅
   - אין שגיאות 500/404 ✅
   - Admin Panel נגיש ✅
   - התפריטים עובדים ✅

### שלב 10: בדיקת בסיס הנתונים
ב-phpMyAdmin, בדוק:
```sql
SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home');
-- צריך להחזיר: http://eyalamit-co-il-2026.s887.upress.link
```

---

## 🚨 פתרון בעיות

### אם היבוא נכשל:
1. בדוק גודל הקובץ (צריך להיות ~44MB)
2. בדוק הגדרות PHP memory limit ב-uPress
3. נסה לפצל את הקובץ אם הוא גדול מדי

### אם האתר לא נטען:
1. בדוק את wp-config.php
2. בדוק הרשאות קבצים (755 לתיקיות, 644 לקבצים)
3. בדוק .htaccess

### אם יש שגיאות DB:
1. בדוק פרטי החיבור ב-wp-config.php
2. בדוק שהמשתמש מורשה לגשת לבסיס הנתונים

---

## 📞 תמיכה

אם נתקל בבעיות:
1. צור קשר עם תמיכת uPress
2. בדוק לוגים ב-cPanel → Errors
3. בדוק phpMyAdmin → Query history

---

## 📋 רשימת בדיקות

- [ ] התחברתי ל-uPress
- [ ] יצרתי בסיס נתונים `eyalamit_staging`
- [ ] יצרתי משתמש `eyalamit_user`
- [ ] ייבאתי את הקובץ `database_export_pre_deployment_20260114_002527.sql`
- [ ] היבוא הסתיים בהצלחה
- [ ] עדכנתי wp-config.php
- [ ] הרצתי סקריפט עדכון URLs
- [ ] האתר נטען בסביבת הבדיקות
- [ ] Admin Panel עובד
- [ ] אין שגיאות קריטיות

---

**הוראות אלו יוצרות בסיס נתונים מלא ופועל בסביבת הבדיקות!** 🎉

**נוצר על ידי:** צוות 3 (Gatekeeper)
**תאריך:** 2026-01-14