# 🚀 פריסת בסיס נתונים - מדריך מהיר
**תאריך:** 2026-01-14

---

## 📋 מה צריך לעשות (סדר פעולות)

### 1. הכנה מקומית ✅ (כבר בוצע)
- [x] גיבוי בסיס נתונים: `backups/database_export_pre_deployment_20260114_002527.sql`
- [x] הכנת סקריפטים: `scripts/update-database-urls-staging.sql`

### 2. יצירת בסיס נתונים בסביבת בדיקות
1. התחבר ל-uPress: https://upress.co.il
2. עבור ל-**"MySQL Databases"**
3. צור בסיס נתונים: `sb0228693_staging`
4. צור משתמש: `sb0228693_user` (עם סיסמה: `Staging2026!`)
5. עבור ל-**phpMyAdmin**

### 3. ייבוא הנתונים
1. ב-phpMyAdmin, בחר `sb0228693_stagin`
2. לחץ **"Import"**
3. בחר קובץ: `backups/database_export_pre_deployment_20260114_002527.sql`
4. לחץ **"Go"**

### 4. העלאת קבצי WordPress
1. התחבר ל-FTP:
   - Host: ftp.s887.upress.link
   - Username: user@eyalamit-co-il-2026.s887.upress.link
   - Password: Staging2026!
   - Port: 21
2. העלה את `wp-core-files.tar.gz` לתיקיית השורש
3. חלץ את הקובץ דרך File Manager של uPress

### 5. יצירת wp-config.php
1. צור קובץ `wp-config.php` בתיקיית השורש
2. העתק את התוכן:
```php
<?php
define('DB_NAME', 'sb0228693_staging');
define('DB_USER', 'sb0228693_user');
define('DB_PASSWORD', 'Staging2026!');
define('DB_HOST', 'localhost');

define('WP_HOME', 'http://eyalamit-co-il-2026.s887.upress.link');
define('WP_SITEURL', 'http://eyalamit-co-il-2026.s887.upress.link');

define('WP_DEBUG', false);

// ... שאר הגדרות ברירת המחדל של WordPress ...
```

### 7. עדכון URLs
1. ב-phpMyAdmin, לחץ **"SQL"**
2. העתק והדבק את תוכן: `scripts/update-database-urls-staging.sql`
3. לחץ **"Go"**

### 8. בדיקה
1. פתח: http://eyalamit-co-il-2026.s887.upress.link
2. בדוק שהאתר עובד
3. בדוק Admin Panel

---

## 🔐 פרטי גישה ל-uPress

### FTP Access:
- **Host:** ftp.s887.upress.link
- **Username:** user@eyalamit-co-il-2026.s887.upress.link
- **Password:** Staging2026!
- **Port:** 21

### Database Access:
- **Database:** sb0228693_staging
- **Username:** sb0228693_user
- **Password:** Staging2026!
- **Host:** localhost

### Site URL:
- **Staging:** http://eyalamit-co-il-2026.s887.upress.link

---

## 📁 קבצים חשובים

| קובץ | מטרה | מיקום |
|-------|-------|--------|
| `database_export_pre_deployment_20260114_002527.sql` | גיבוי בסיס נתונים | `backups/` |
| `update-database-urls-staging.sql` | עדכון URLs | `scripts/` |
| `wp-core-files.tar.gz` | קבצי WordPress להעלאה | root |
| `uPress-database-setup-guide.md` | מדריך מפורט | `docs/database/` |
| `deployment-database-plan.md` | תוכנית מלאה | `docs/database/` |

---

## ⚠️ נקודות חשובות

1. **סביבת הבדיקות ריקה** - צריך ליצור בסיס נתונים חדש
2. **גיבוי תמיד קודם** - במיוחד כשעוברים לייצור
3. **בדוק אחרי כל שלב** - אל תמשיך לפני שאתה בטוח שהשלב הקודם עבד
4. **יש תוכנית חזרה** - אם משהו משתבש, אפשר לחזור לגיבוי

---

## 📞 תמיכה

אם יש בעיות:
- בדוק לוגים ב-cPanel → "Errors"
- צור קשר עם תמיכת uPress
- בדוק את המדריך המלא: `docs/database/uPress-database-setup-guide.md`

---

## ✅ רשימת בדיקות

- [ ] התחברתי ל-uPress
- [ ] יצרתי בסיס נתונים `eyalamit_staging`
- [ ] יצרתי משתמש `eyalamit_user`
- [ ] ייבאתי את קובץ הגיבוי
- [ ] הרצתי סקריפט עדכון URLs
- [ ] האתר עובד בסביבת הבדיקות
- [ ] Admin Panel נגיש
- [ ] אין שגיאות קריטיות

---

**מוכן לפריסה!** 🎯