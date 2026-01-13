# 🔒 פרוטוקול פעולות DB בטוחות - Safe Database Operations Protocol

**תאריך יצירה:** 13 בינואר 2026
**סמכות:** CEO אייל עמית
**גרסה:** 1.0 - Post-Emergency Recovery

---

## 🚨 לקחים מהתקרית הקריטית

### הבעיה:
- פעולות `REPLACE` ישירות על תוכן DB פוגעות בנתונים מסוג **Serialized PHP**
- טבלאות `wp_options`, `wp_postmeta` מכילות הגדרות Theme/Plugin כמחרוזות serialized
- שינוי אורך מחרוזת ב-serialized data שובר את מבנה האובייקט

### התוצאה:
- קריסת אתר עם HTTP 500
- פגיעה בפונקציונליות Bridge Theme
- אובדן גישה לכלי ניהול WordPress

---

## 🛡️ פרוטוקול פעולות DB בטוחות

### 1. **זיהוי נתונים מסוכנים (Pre-Operation Assessment)**

#### טבלאות בעדיפות גבוהה:
- `wp_options` - הגדרות Theme/Plugin (99% serialized)
- `wp_postmeta` - מטה-דאטה של פוסטים (חלק serialized)
- `wp_usermeta` - מטה-דאטה של משתמשים (serialized)

#### בדיקת סיכון:
```sql
-- חיפוש שדות עם תוכן serialized
SELECT COUNT(*) FROM wp_options WHERE option_value LIKE 'a:%:{%';
SELECT COUNT(*) FROM wp_postmeta WHERE meta_value LIKE 'a:%:{%';
```

### 2. **גיבוי חובה (Mandatory Backup Protocol)**

#### היקף גיבוי:
```bash
# גיבוי כל הטבלאות הקריטיות
wp db export full_backup_$(date +%Y%m%d_%H%M%S).sql

# גיבוי סלקטיבי לפי טבלה
wp db export wp_options_backup.sql --tables=wp_options
wp db export wp_posts_backup.sql --tables=wp_posts
wp db export wp_postmeta_backup.sql --tables=wp_postmeta
```

#### אחסון גיבוי:
- בתיקיית `wp-content/db-backups/`
- עם timestamp מלא
- שמירה למשך 30 יום לפחות

### 3. **מתודולוגיית sanitization בטוחה**

#### ❌ אסור לעשות:
```sql
-- DIRECT REPLACE - מסוכן!
UPDATE wp_posts SET post_content = REPLACE(post_content, '"', '"')
UPDATE wp_options SET option_value = REPLACE(option_value, '"', '"')
```

#### ✅ יש לעשות:
```php
// שימוש ב-PHP unserialize/serialize
$original = get_option('theme_option');
if (is_serialized($original)) {
    $unserialized = unserialize($original);
    // בצע שינויים רק על strings בתוך המערך
    $modified = modify_strings_only($unserialized);
    $safe_result = serialize($modified);
    update_option('theme_option', $safe_result);
}
```

### 4. **כלי Sanitization בטוח**

#### קבצים שנוצרו:
- `safe_smart_quotes_sanitizer.php` - מחלקה PHP לבטיחות מרבית
- `wpcli-safe-sanitizer.php` - פקודת WP-CLI לביצוע

#### שימוש:
```bash
# העתקת קבצים ל-WordPress
cp safe_smart_quotes_sanitizer.php wp-content/mu-plugins/
cp wpcli-safe-sanitizer.php wp-content/mu-plugins/

# הרצת sanitization בטוח
wp safe-sanitize-quotes
```

### 5. **פרוטוקול אימות (Validation Protocol)**

#### בדיקות חובה לפני sanitization:
```php
// בדיקת serialized data integrity
function validate_serialized($data) {
    if (!is_serialized($data)) return true;
    $unserialized = @unserialize($data);
    return $unserialized !== false;
}
```

#### בדיקות חובה אחרי sanitization:
- טעינת דף הבית: HTTP 200
- כניסה ל-wp-admin: תקינה
- בדיקת הגדרות Theme: נטענות ללא שגיאות
- בדיקת VC Shortcodes: מרונדרים כהלכה

### 6. **תוכנית התאוששות (Recovery Plan)**

#### זיהוי תקלה:
- HTTP 500/503 errors
- שגיאות PHP fatal ב-logs
- קריסת Theme/Plugin

#### תהליך התאוששות:
```bash
# 1. זיהוי הגיבוי האחרון
ls -la wp-content/db-backups/

# 2. שחזור DB
wp db import backup_file.sql

# 3. אימות תקינות
wp db check
curl -I http://localhost:9090
```

---

## 📋 צ'קליסט פעולות DB בטוחות

### לפני כל פעולה DB:
- [ ] זיהוי טבלאות עם serialized data
- [ ] יצירת גיבוי מלא של כל הטבלאות
- [ ] בדיקת תקינות serialized data
- [ ] פיתוח script עם unserialize/serialize
- [ ] אישור בכתב מצוות 3

### במהלך הפעולה:
- [ ] ביצוע על סביבה dev קודם
- [ ] הרצה בכמויות קטנות (batch processing)
- [ ] לוגינג מלא של כל שינוי
- [ ] עצירה מיידית בשגיאה ראשונה

### אחרי הפעולה:
- [ ] אימות HTTP status: 200
- [ ] בדיקת wp-admin access
- [ ] אימות Theme/Plugin functionality
- [ ] בדיקת VC Shortcodes rendering
- [ ] דוח מסכם לצוות 3

---

## 🎯 מסקנות והמלצות

### לקחים מרכזיים:
1. **אף פעולת DB ללא גיבוי** - חובה מוחלטת
2. **Serialized data = אזור מסוכן** - דורש טיפול מיוחד
3. **בדיקה על dev תחילה** - למניעת הפתעות
4. **PHP > SQL** - שפת PHP בטוחה יותר לנתוני PHP

### המלצות עתידיות:
- פיתוח plugin ייעודי לבטיחות DB
- הכשרת צוות 4 בטיפול ב-serialized data
- יצירת automated backup system
- פיתוח testing suite לכל פעולת DB

---

**חתימה:** צוות 4 (Database Specialists)  
**אחריות:** פרוטוקול זה מחייב את כל פעולות DB עתידיות  
**תוקף:** מיידי ומוחלט  

**ראיות:** `emergency_restore_evidence_20260113_080500.txt`