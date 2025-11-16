# פתרון שגיאת עדכון פלאגינים: "Unable to locate WordPress content directory"

**בעיה:** בעת ניסיון עדכון פלאגינים דרך Admin, מקבלים שגיאה:
"Unable to locate WordPress content directory (wp-content)."

**סיבה:** WordPress לא מזהה נכון את נתיב תיקיית wp-content בסביבת Docker.

---

## פתרון מהיר

### דרך 1: הוספת הגדרות ל-wp-config.php (מומלץ)

**מה לעשות:**
1. פתח את הקובץ: `eyalamit.co.il_bm1763033821dm/wp-config.php`
2. הוסף את השורות הבאות **לפני** השורה `/* That's all, stop editing! */`:

```php
// הגדרות נתיבים ל-wp-content (פתרון בעיית עדכון פלאגינים)
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_CONTENT_URL', 'http://localhost:8080/wp-content');
```

3. שמור את הקובץ
4. נסה לעדכן את הפלאגין שוב

---

### דרך 2: עדכון דרך WP-CLI (חלופה)

**אם דרך 1 לא עובדת, אפשר לעדכן דרך WP-CLI:**

```bash
docker exec newwebsiteainov2025-wordpress-1 wp plugin update js_composer --allow-root --path=/var/www/html
```

**לעדכון כל הפלאגינים:**
```bash
docker exec newwebsiteainov2025-wordpress-1 wp plugin update --all --allow-root --path=/var/www/html
```

**אבל:** פלאגינים פרימיום (כמו Visual Composer) לא יכולים להתעדכן דרך WP-CLI - צריך דרך Admin או Envato Market.

---

## פתרון מפורט

### שלב 1: הוספת הגדרות ל-wp-config.php

**לפני השורה:**
```php
/* That's all, stop editing! Happy blogging. */
```

**הוסף:**
```php
// הגדרות נתיבים ל-wp-content (פתרון בעיית עדכון פלאגינים ב-Docker)
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_CONTENT_URL', 'http://localhost:8080/wp-content');
```

**הקובץ אמור להיראות כך:**
```php
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// הגדרות נתיבים ל-wp-content (פתרון בעיית עדכון פלאגינים ב-Docker)
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_CONTENT_URL', 'http://localhost:8080/wp-content');

/* That's all, stop editing! Happy blogging. */
```

---

### שלב 2: בדיקה

1. **שמור את הקובץ**
2. **רענן את דף ה-Admin** (F5)
3. **נסה לעדכן את הפלאגין שוב**

---

## אם עדיין לא עובד

### בדיקה 1: בדיקת נתיבים במיכל

**בדוק אם תיקיית wp-content קיימת:**
```bash
docker exec newwebsiteainov2025-wordpress-1 ls -la /var/www/html/wp-content
```

**אם יש שגיאה** - יש בעיה ב-volume mapping ב-Docker.

**פתרון:** בדוק את `docker-compose.yml`:
```yaml
volumes:
  - ./eyalamit.co.il_bm1763033821dm:/var/www/html
```

ודא שהנתיב נכון.

---

### בדיקה 2: בדיקת הרשאות

**בדוק הרשאות:**
```bash
docker exec newwebsiteainov2025-wordpress-1 ls -la /var/www/html/wp-content/plugins
```

**אם אין הרשאות כתיבה** - צריך לתקן:
```bash
docker exec newwebsiteainov2025-wordpress-1 chown -R www-data:www-data /var/www/html/wp-content
docker exec newwebsiteainov2025-wordpress-1 chmod -R 755 /var/www/html/wp-content
```

---

### בדיקה 3: עדכון דרך Envato Market (לפלאגינים פרימיום)

**אם Visual Composer לא מתעדכן דרך Admin:**

1. **עבור ל:** Admin → Envato Market → Plugins
2. **בדוק אם יש עדכון** דרך Envato Market
3. **אם יש** - עדכן דרך Envato Market (לא דרך Admin → Updates)

**או:**
1. **הורד את העדכון** מהאתר של Envato
2. **העלה ידנית** דרך FTP/cPanel
3. **החלף את התיקייה** של הפלאגין

---

## פתרון זמני - עדכון ידני

### אם לא עובד דרך Admin:

#### לקבצי תרגום (translations):
- **לא קריטי** - קבצי תרגום לא משפיעים על הפונקציונליות
- אפשר להתעלם מהשגיאה

#### ל-Visual Composer:
- **זה יותר חשוב** - כדאי לעדכן

**אפשרויות:**
1. **דרך Envato Market** (אם מותקן)
2. **הורדה ידנית** מהאתר של Envato
3. **העלאה ידנית** דרך FTP/cPanel

---

## סיכום - מה לעשות עכשיו

### שלב 1: הוספת הגדרות ל-wp-config.php (2 דקות)
1. [ ] פתח `eyalamit.co.il_bm1763033821dm/wp-config.php`
2. [ ] הוסף את השורות (ראה למעלה)
3. [ ] שמור את הקובץ

### שלב 2: בדיקה (1 דקה)
1. [ ] רענן את דף ה-Admin (F5)
2. [ ] נסה לעדכן את הפלאגין שוב

### שלב 3: אם עדיין לא עובד
1. [ ] בדוק דרך Envato Market
2. [ ] או עדכן ידנית (הורדה והעלאה)

---

**ראה גם:** `HOW-TO-CHECK-PLUGINS.md` - מדריך בדיקת פלאגינים

