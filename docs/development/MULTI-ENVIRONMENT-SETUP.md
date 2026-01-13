# הגדרות לסביבות מרובות - WordPress URLs
**תאריך:** 2026-01-14  
**מטרה:** תמיכה ב-3 סביבות שונות עם URLs שונים

---

## 🌍 הסביבות השונות

### 1. Development (פיתוח מקומי)
- **URL:** `http://localhost:9090`
- **פורט:** 9090 (חובה!)
- **שימוש:** פיתוח ובדיקות מקומיות

### 2. Staging/Testing (בדיקות בפרודקשן)
- **URL:** `http://eyalamit-co-il-2026.s887.upress.link/`
- **פורט:** אין (ברירת מחדל 80/443)
- **שימוש:** בדיקות לפני פריסה לייצור

### 3. Production (ייצור)
- **URL:** `https://eyalamit.co.il`
- **פורט:** אין (ברירת מחדל 443)
- **שימוש:** אתר ציבורי

---

## 🔧 פתרון: הגדרות דינמיות ב-wp-config.php

### הוספת Constants ל-wp-config.php

```php
/**
 * WordPress URLs - Multi-Environment Support
 * 
 * הגדרות אוטומטיות לפי סביבה:
 * - Development: http://localhost:9090
 * - Staging: http://eyalamit-co-il-2026.s887.upress.link
 * - Production: https://eyalamit.co.il
 */

// זיהוי סביבה לפי HTTP_HOST
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:9090';

// הגדרת URLs לפי סביבה
if (strpos($http_host, 'localhost') !== false || strpos($http_host, '127.0.0.1') !== false) {
    // Development
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $port = isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : ':9090';
    $wp_home = $protocol . '://' . $http_host;
    $wp_siteurl = $wp_home;
} elseif (strpos($http_host, 'eyalamit-co-il-2026.s887.upress.link') !== false) {
    // Staging
    $wp_home = 'http://eyalamit-co-il-2026.s887.upress.link';
    $wp_siteurl = $wp_home;
} elseif (strpos($http_host, 'eyalamit.co.il') !== false) {
    // Production
    $wp_home = 'https://eyalamit.co.il';
    $wp_siteurl = $wp_home;
} else {
    // Fallback - Development
    $wp_home = 'http://localhost:9090';
    $wp_siteurl = $wp_home;
}

// הגדרת Constants (רק אם לא הוגדרו כבר)
if (!defined('WP_HOME')) {
    define('WP_HOME', $wp_home);
}
if (!defined('WP_SITEURL')) {
    define('WP_SITEURL', $wp_siteurl);
}
```

---

## 📝 הוראות יישום

### שלב 1: הוספת הקוד ל-wp-config.php

1. פתח את `wp-config.php`
2. הוסף את הקוד **לפני** השורה `/* That's all, stop editing! Happy publishing. */`
3. שמור את הקובץ

### שלב 2: עדכון URLs ב-Database (אם נדרש)

**לסביבת Development:**
```bash
# עדכון URLs ב-DB (אם יש URLs ישנים)
docker compose exec wordpress wp search-replace 'https://www.eyalamit.co.il' 'http://localhost:9090' --all-tables --allow-root
docker compose exec wordpress wp search-replace 'http://www.eyalamit.co.il' 'http://localhost:9090' --all-tables --allow-root
```

**לסביבת Staging:**
```bash
# עדכון URLs ב-DB
wp search-replace 'http://localhost:9090' 'http://eyalamit-co-il-2026.s887.upress.link' --all-tables
wp search-replace 'https://eyalamit.co.il' 'http://eyalamit-co-il-2026.s887.upress.link' --all-tables
```

**לסביבת Production:**
```bash
# עדכון URLs ב-DB
wp search-replace 'http://eyalamit-co-il-2026.s887.upress.link' 'https://eyalamit.co.il' --all-tables
wp search-replace 'http://localhost:9090' 'https://eyalamit.co.il' --all-tables
```

### שלב 3: עדכון Permalinks

```bash
# בכל סביבה, אחרי עדכון URLs:
wp rewrite flush
```

---

## ✅ בדיקות

### בדיקת הגדרות נוכחיות:
```bash
# בדיקת WP_HOME
docker compose exec wordpress wp option get home --allow-root

# בדיקת WP_SITEURL
docker compose exec wordpress wp option get siteurl --allow-root
```

### בדיקת redirects:
```bash
# בדיקת redirect
curl -I http://localhost:9090/Blog
# אמור להחזיר 200 OK או redirect נכון (לא ל-localhost ללא פורט)
```

---

## 🚨 הערות חשובות

1. **Constants ב-wp-config.php גוברים על הגדרות DB:**
   - אם מוגדר `WP_HOME` או `WP_SITEURL` ב-wp-config.php, ההגדרות ב-DB מתעלמות
   - זה הפתרון הנכון לסביבות מרובות

2. **בסביבת Development:**
   - הפורט (9090) נדרש
   - ה-URLs יכללו את הפורט אוטומטית

3. **בסביבות Staging/Production:**
   - אין צורך בפורט (ברירת מחדל 80/443)
   - ה-URLs לא יכללו פורט

4. **עדכון URLs ב-DB:**
   - חשוב לעדכן את ה-URLs ב-DB בכל מעבר בין סביבות
   - להשתמש ב-`wp search-replace` בזהירות (גיבוי לפני!)

---

**נוצר על ידי:** צוות 3 (Gatekeeper)  
**תאריך:** 2026-01-14
