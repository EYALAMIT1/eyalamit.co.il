# 🗄️ מדריך בסיס הנתונים ב-WordPress
**מבוסס על:** SSOT v11.0  
**סעיפים רלוונטיים:** 7, 10, 11

---

## 📍 מה התפקיד של בסיס הנתונים ב-WordPress?

### **בסיס הנתונים הוא הלב של WordPress**

WordPress הוא מערכת **Content Management System (CMS)** מבוססת מסד נתונים. כל התוכן, ההגדרות, והמטא-דאטה נשמרים במסד הנתונים.

**תפקידים עיקריים:**
1. **אחסון תוכן** - כל הפוסטים, עמודים, תגובות, מדיה
2. **אחסון הגדרות** - הגדרות האתר, תוספים, תבניות
3. **אחסון מטא-דאטה** - מידע נוסף על תוכן (Custom Fields, Elementor Data)
4. **אחסון משתמשים** - משתמשים, הרשאות, פרופילים

---

## 🔍 מה מוגדר בקוד ומה בבסיס הנתונים?

### **מה מוגדר בקוד (Code Files):**

#### 1. **קבצי Core WordPress:**
- `wp-config.php` - הגדרות חיבור ל-DB, Security Keys, Constants
- `wp-includes/` - ליבת WordPress (אין לערוך!)
- `wp-admin/` - ממשק ניהול (אין לערוך!)

#### 2. **קבצי Theme (תבנית):**
- `wp-content/themes/bridge-child/functions.php` - פונקציות מותאמות אישית
- `wp-content/themes/bridge-child/style.css` - עיצוב מותאם אישית
- `wp-content/themes/bridge-child/schema-person-specialist.php` - Schema JSON-LD

#### 3. **קבצי Plugins (תוספים):**
- `wp-content/plugins/[plugin-name]/` - קוד התוספים (אין לערוך!)
- `wp-content/mu-plugins/` - Must-Use Plugins (קוד מותאם אישית)

#### 4. **קבצי קונפיגורציה:**
- `.htaccess` - הגדרות Apache
- `docker-compose.yml` - הגדרות Docker
- `composer.json` - תלויות PHP

---

### **מה מוגדר בבסיס הנתונים (Database):**

#### 1. **טבלאות Core WordPress:**

**`wp_posts`** - כל התוכן:
- פוסטים, עמודים, תגובות, Revisions
- `post_content` - התוכן המלא (HTML)
- `post_title` - כותרת
- `post_status` - סטטוס (publish, draft, etc.)
- `post_type` - סוג (post, page, attachment, etc.)

**`wp_postmeta`** - מטא-דאטה של תוכן:
- Custom Fields
- Elementor Data (serialized!)
- Plugin Data
- **⚠️ קריטי:** מכיל נתונים serialized (`a:`, `O:`) - אין לערוך ישירות!

**`wp_options`** - הגדרות האתר:
- הגדרות כלליות (site_url, home_url, admin_email)
- הגדרות תוספים
- הגדרות תבנית
- Cache Data

**`wp_users`** - משתמשים:
- שמות משתמש, סיסמאות (hashed), אימיילים

**`wp_usermeta`** - מטא-דאטה של משתמשים:
- פרופילים, הרשאות, הגדרות אישיות

**`wp_terms`** - קטגוריות ותגיות:
- קטגוריות, תגיות, Taxonomies

**`wp_term_relationships`** - קשרים:
- קשרים בין תוכן לקטגוריות/תגיות

**`wp_comments`** - תגובות:
- תגובות על פוסטים

**`wp_commentmeta`** - מטא-דאטה של תגובות

#### 2. **טבלאות תוספים:**
- כל תוסף יכול ליצור טבלאות משלו
- דוגמה: `wp_woocommerce_*` (אם WooCommerce מותקן)

---

## ⚠️ כללי חובה שלנו בנוגע לסביבת WordPress

### **1. איסור עריכת Core/Plugins/Parent Themes**

**🔴 איסור מוחלט על עריכת קבצים של:**
- WordPress Core (`wp-includes/`, `wp-admin/`) - כל שינוי יאבד בעדכון WP
- תוספים של צד שלישי (`wp-content/plugins/[plugin-name]/`) - כל שינוי יאבד בעדכון התוסף
- תבניות אב (Parent Themes) - כל שינוי יאבד בעדכון התבנית

**✅ כל התאמה תיעשה דרך:**
- Must-Use Plugins (`wp-content/mu-plugins/`) - נטענים אוטומטית, לא נדרסים בעדכונים
- Child Theme (`wp-content/themes/bridge-child/`) - עוקף את תבנית האב, בטוח לעדכונים
- WordPress Hooks בלבד (`add_action`, `add_filter`) - שינויים דינמיים ללא עריכת קבצים

---

### **2. פרוטוקול עבודה בטוחה במסד הנתונים**

#### **🔴 Serialization Safety - קריטי!**

**איסור מוחלט על REPLACE ידני ב-SQL על נתונים serialized!**

**מה זה Serialized Data?**
- נתונים ב-`wp_postmeta` ו-`wp_options` שמוגדרים בפורמט מיוחד
- מתחיל ב-`a:` (array) או `O:` (object)
- דוגמה: `a:3:{s:4:"name";s:8:"אייל עמית";s:5:"email";s:18:"info@eyalamit.co.il";}`

**למה זה מסוכן?**
- REPLACE על נתונים serialized יהרוס את המבנה
- יגרום לשגיאות קריאה של נתונים
- יכול לשבור את האתר

**✅ מה לעשות במקום:**
```bash
# שימוש ב-wp search-replace בלבד (מטפל ב-serialized data נכון)
wp search-replace 'old-url' 'new-url' --all-tables

# או עבור Elementor URLs (פקודה ייעודית)
wp elementor replace-urls http://www.eyalamit.co.il http://localhost:9090 --allow-root
```

---

#### **🔴 גיבוי חובה לפני כל פעולה**

**כל פעולה ב-DB חייבת להתחיל בגיבוי מלא:**
```bash
# יצירת Snapshot מלא
wp db export docs/database/backups/backup-$(date +%Y%m%d-%H%M%S).sql
```

**אין יוצאים מהכלל!**

---

#### **🔴 כללי עבודה ב-DB:**

1. **שימוש ב-WP-CLI בלבד:**
   - `wp search-replace` - החלפת URLs
   - `wp db export` - גיבוי
   - `wp db optimize` - אופטימיזציה
   - `wp post meta update` - עדכון מטא-דאטה

2. **אין SQL ישיר:**
   - אין להריץ SQL ישירות על נתונים serialized
   - רק במקרים מיוחדים (גרשיים חכמים) עם בדיקה מקדימה

3. **בדיקה לפני ביצוע:**
   - תמיד לבדוק אם נתונים serialized לפני REPLACE
   - להשתמש ב-`WHERE meta_value NOT LIKE 'a:%' AND meta_value NOT LIKE 'O:%'`

---

### **3. תקן הקידוד (ea_ Standard)**

**חובה:** כל קוד מותאם אישית חייב להשתמש ב-prefix `ea_`:

- **פונקציות:** `ea_core_hardening()`, `ea_add_person_schema()`
- **Classes:** `class EA_Optimizer`, `class EA_Security_Headers`
- **Constants:** `EA_PLUGIN_VERSION`, `EA_DEBUG_MODE`
- **CSS Classes:** `.ea-custom-class`, `.ea-wrapper`
- **CSS IDs:** `#ea-header`, `#ea-footer`

**דוגמה:**
```php
// ✅ נכון
function ea_add_person_schema() { ... }
class EA_Optimizer { ... }
define('EA_DEBUG_MODE', true);

// ❌ שגוי
function add_person_schema() { ... }
class Optimizer { ... }
define('DEBUG_MODE', true);
```

---

### **4. חובת Sanitization**

**כל קלט משתמש חייב לעבור Sanitization:**

```php
// ✅ נכון
$name = sanitize_text_field($_POST['name']);
$email = sanitize_email($_POST['email']);
$content = wp_kses_post($_POST['content']);
$id = absint($_GET['id']);

// ❌ שגוי
$name = $_POST['name'];  // לא מאובטח!
```

---

### **5. שימוש ב-Hooks בלבד**

**אין לערוך קבצים ישירות. כל שינוי דרך WordPress Hooks:**

```php
// ✅ נכון - דרך Hooks
add_action('wp_enqueue_scripts', 'ea_enqueue_scripts');
add_filter('the_content', 'ea_filter_content');
add_action('init', 'ea_init_function');

// ❌ שגוי - עריכה ישירה של קבצים
// אין לערוך wp-includes/functions.php ישירות!
```

---

### **6. ניהול קבצים גדולים**

**איסור קבצים מעל 50MB ב-Git:**
- קבצי SQL גדולים: `docs/database/backups/` (מוחרג ב-.gitignore)
- קבצי מדיה: לא להעלות ל-Git
- תיקיית `db_data/` מוחרגת ב-.gitignore

---

## 📊 סיכום: מה היכן?

| סוג מידע | מיקום | ניתן לערוך? |
|---------|-------|-------------|
| **תוכן פוסטים/עמודים** | DB (`wp_posts`) | ✅ דרך Admin/WP-CLI |
| **Elementor Data** | DB (`wp_postmeta` - serialized) | ✅ דרך Elementor/WP-CLI |
| **הגדרות אתר** | DB (`wp_options`) | ✅ דרך Admin/WP-CLI |
| **קוד Theme** | Files (`wp-content/themes/bridge-child/`) | ✅ Child Theme בלבד |
| **קוד Plugins** | Files (`wp-content/plugins/`) | ❌ אין לערוך |
| **קוד Core** | Files (`wp-includes/`, `wp-admin/`) | ❌ אין לערוך |
| **הגדרות DB** | Files (`wp-config.php`) | ✅ רק הגדרות חיבור |

---

## 🎯 כללי זהב לעבודה ב-WordPress

1. **תמיד גבה לפני שינויים ב-DB**
2. **אין REPLACE על נתונים serialized**
3. **השתמש ב-WP-CLI בלבד**
4. **אין עריכת Core/Plugins/Parent Themes**
5. **כל קוד מותאם אישית עם prefix `ea_`**
6. **כל קלט משתמש עובר Sanitization**
7. **שינויים דרך Hooks בלבד**

---

**מסמך זה מבוסס על SSOT v11.0**  
**עודכן:** 2026-01-14  
**גרסה:** 1.0
