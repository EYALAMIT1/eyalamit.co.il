# 🔍 תוכנית תיקון שגיאות סביבת בדיקות
**תאריך:** 2026-01-14
**סביבה:** http://eyalamit-co-il-2026.s887.upress.link
**סטטוס:** 🟡 NEEDS_DEBUGGING

---

## 📋 סקירה כללית

סביבת הבדיקות פועלת אך עם שגיאות והגדרות סגנון חסרות. נדרש תיקון שיטתי לפני המשך לפריסה או תוספים פרימיום.

---

## 🔍 שלב 1: זיהוי הבעיות

### 1.1 בדיקת שגיאות Console
**כלי:** Chrome DevTools → Console
**מה לבדוק:**
- JavaScript errors
- CSS loading errors
- Network errors
- 404 errors on assets

### 1.2 בדיקת קבצים חסרים
**בדיקות:**
```bash
# בדוק אם theme files קיימים
ls -la wp-content/themes/bridge/
ls -la wp-content/themes/bridge-child/

# בדוק permissions
find wp-content/ -type f -name "*.css" | head -5
find wp-content/ -type f -name "*.js" | head -5
```

### 1.3 בדיקת בסיס נתונים
**ב-phpMyAdmin:**
```sql
-- בדוק theme options
SELECT * FROM wp_options WHERE option_name LIKE '%bridge%' LIMIT 10;

-- בדוק active theme
SELECT * FROM wp_options WHERE option_name = 'template';
SELECT * FROM wp_options WHERE option_name = 'stylesheet';

-- בדוק plugin status
SELECT * FROM wp_options WHERE option_name = 'active_plugins';
```

### 1.4 השוואת סביבה מקומית
**השווה:**
- Theme version
- Plugin versions
- wp-config.php settings
- .htaccess rules

---

## 🛠️ שלב 2: תיקון בעיות נפוצות

### 2.1 תיקון Theme Files
**אם theme files חסרים:**
1. העלה מחדש את `wp-content/themes/bridge/`
2. העלה מחדש את `wp-content/themes/bridge-child/`
3. בדוק permissions: 755 לתיקיות, 644 לקבצים

### 2.2 תיקון Permalinks
**ב-WordPress Admin:**
1. Settings → Permalinks
2. שמור שינויים (גם אם לא שינית כלום)
3. זה ירענן את .htaccess

### 2.3 תיקון Theme Options
**אם הגדרות theme חסרות:**
```sql
-- יבוא theme options מהסביבה המקומית
-- או reset theme settings דרך admin
```

### 2.4 תיקון Plugin Configurations
**בדוק כל plugin:**
- WooCommerce: Settings → Advanced → Reset
- Elementor: Tools → General → Regenerate CSS
- Yoast SEO: General → Features

---

## 📊 שלב 3: בדיקות שיטתיות

### 3.1 בדיקת דפים מרכזיים
- [ ] דף בית: `/`
- [ ] דף אודות: `/about/`
- [ ] דף צור קשר: `/צור-קשר/`
- [ ] דף חנות: `/shop/` (WooCommerce)
- [ ] דף מוצר: מוצר יחיד

### 3.2 בדיקת פונקציונליות
- [ ] תפריט ניווט
- [ ] טפסי צור קשר
- [ ] חיפוש
- [ ] WooCommerce cart/checkout
- [ ] Elementor editor

### 3.3 בדיקת ביצועים
- [ ] זמן טעינה
- [ ] CSS/JS loading
- [ ] Images loading
- [ ] Mobile responsiveness

---

## 🔧 כלי אבחון

### WordPress Built-in
- **Site Health:** `/wp-admin/site-health.php`
- **Debug Mode:** הוסף ל-wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Browser Tools
- **Network Tab:** לבדוק 404 errors
- **Sources Tab:** לוודא CSS/JS נטענים
- **Application Tab:** לבדוק localStorage/sessionStorage

### Server Tools
- **Error Logs:** ב-cPanel → Errors
- **File Manager:** בדוק הרשאות קבצים
- **phpMyAdmin:** בדוק טבלאות corrupted

---

## 📋 רשימת בדיקות

### קריטי
- [ ] אין שגיאות 500/404
- [ ] Theme נטען נכון
- [ ] Admin panel נגיש
- [ ] קבצי media נטענים

### חשוב
- [ ] WooCommerce עובד
- [ ] Elementor עובד
- [ ] טפסים עובדים
- [ ] SEO plugins עובדים

### משני
- [ ] ביצועים טובים
- [ ] Mobile responsive
- [ ] Cache עובד

---

## 🎯 קריטריון הצלחה

סביבת הבדיקות תיחשב מוכנה כש:
- ✅ אין שגיאות קריטיות
- ✅ כל הדפים הראשיים נטענים
- ✅ Theme נראה כמו בסביבה המקומית
- ✅ כל הפונקציונליות הבסיסית עובדת
- ✅ Admin panel נגיש ופועל

---

## 📞 תמיכה

אם יש בעיות מורכבות:
1. צור קשר עם uPress support
2. בדוק לוגים ב-cPanel
3. השווה עם הסביבה המקומית

---

**נוצר על ידי:** צוות 3 (Gatekeeper)
**תאריך:** 2026-01-14