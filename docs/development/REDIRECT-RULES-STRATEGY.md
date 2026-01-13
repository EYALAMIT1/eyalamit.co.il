# אסטרטגיה להגדרת Redirects - כללים כלליים
**תאריך:** 2026-01-14  
**מטרה:** להגדיר את כל ההפניות בכמה כללים ספורים כלליים

---

## 🎯 המטרה

לפי בקשת המנכ"ל:
- **חובה לשמור את כל הכתובות פעילות** - לא להסיר מ-sitemap
- **להגדיר את כל ההפניות בכמה כללים ספורים כלליים** - במקום redirects בודדים

---

## 📊 ניתוח דפוסים ב-Redirects

### דפוסים שזוהו:

1. **Blog URLs** - 148 redirects (64.9%)
   - דפוס: `/Blog/...` → צריך להפנות ל-URL נכון
   - כללים: כל ה-URLs שמתחילים ב-`/Blog` צריכים redirect

2. **QR Codes** - 50 redirects (21.9%)
   - דפוס: `/qr/...` → צריך להפנות ל-URL נכון
   - כללים: כל ה-URLs שמתחילים ב-`/qr` צריכים redirect

3. **WooCommerce Shop** - 5 redirects (2.2%)
   - דפוס: `/shop/...` → צריך להפנות ל-URL נכון
   - כללים: כל ה-URLs שמתחילים ב-`/shop` צריכים redirect

4. **קטגוריות עברית** - 11+ redirects
   - דפוס: URLs עם encoding עברי → צריך להפנות נכון
   - כללים: URLs עם encoding מיוחד צריכים redirect

---

## 🔧 פתרון: כללים כלליים ב-.htaccess

### אפשרות 1: Redirects ב-.htaccess (מומלץ)

**יתרונות:**
- ✅ מהיר מאוד (רמת שרת)
- ✅ לא תלוי ב-WordPress
- ✅ עובד לפני WordPress נטען

**דוגמה:**

```apache
# Redirect Rules - General Patterns

# Blog URLs - Redirect /Blog to correct URL
RewriteCond %{REQUEST_URI} ^/Blog(/.*)?$ [NC]
RewriteRule ^Blog(/.*)?$ /%1 [R=301,L]

# QR Codes - Redirect /qr to correct URL  
RewriteCond %{REQUEST_URI} ^/qr(/.*)?$ [NC]
RewriteRule ^qr(/.*)?$ /%1 [R=301,L]

# Shop URLs - Redirect /shop to correct URL (if needed)
RewriteCond %{REQUEST_URI} ^/shop(/.*)?$ [NC]
RewriteRule ^shop(/.*)?$ /%1 [R=301,L]
```

---

### אפשרות 2: Redirects ב-WordPress (functions.php)

**יתרונות:**
- ✅ יותר גמיש
- ✅ יכול להשתמש ב-WordPress functions
- ✅ קל יותר לתחזוקה

**דוגמה:**

```php
/**
 * General Redirect Rules
 * מטפל בכל ההפניות בכמה כללים ספורים כלליים
 */
function ea_general_redirect_rules() {
    // רק אם לא ב-admin
    if (is_admin()) {
        return;
    }
    
    $request_uri = $_SERVER['REQUEST_URI'];
    $redirect_url = null;
    
    // כלל 1: Blog URLs
    if (preg_match('#^/Blog(/.*)?$#i', $request_uri, $matches)) {
        // הסרת /Blog מהתחלה
        $redirect_url = isset($matches[1]) ? $matches[1] : '/';
        if (empty($redirect_url) || $redirect_url === '/') {
            $redirect_url = home_url('/');
        } else {
            $redirect_url = home_url($redirect_url);
        }
    }
    
    // כלל 2: QR Codes
    elseif (preg_match('#^/qr(/.*)?$#i', $request_uri, $matches)) {
        // שמירה על /qr אבל תיקון URL
        $redirect_url = home_url($request_uri);
    }
    
    // כלל 3: Shop URLs
    elseif (preg_match('#^/shop(/.*)?$#i', $request_uri, $matches)) {
        // שמירה על /shop אבל תיקון URL
        $redirect_url = home_url($request_uri);
    }
    
    // ביצוע redirect אם נדרש
    if ($redirect_url && $redirect_url !== home_url($request_uri)) {
        wp_redirect($redirect_url, 301);
        exit;
    }
}
add_action('template_redirect', 'ea_general_redirect_rules', 1);
```

---

## 🎯 המלצה: שילוב של שני הפתרונות

### שלב 1: תיקון Multi-Environment (כבר בוצע)
- ✅ הוספת קוד ל-`wp-config.php` שמזהה סביבה
- ✅ זה אמור לפתור את רוב הבעיות

### שלב 2: כללים כלליים ב-.htaccess
- ✅ להוסיף כללים ל-.htaccess עבור דפוסים ברורים
- ✅ זה יטופל ברמת השרת (מהיר יותר)

### שלב 3: כללים כלליים ב-WordPress (אם נדרש)
- ✅ להוסיף כללים ב-functions.php עבור מקרים מורכבים יותר
- ✅ זה יטופל ב-WordPress (גמיש יותר)

---

## 📋 תוכנית ביצוע

### שלב 1: בדיקת Multi-Environment (15 דקות)
```bash
# בדיקת URLs אחרי עדכון wp-config.php
curl -I http://localhost:9090/Blog
curl -I http://localhost:9090/shop
curl -I http://localhost:9090/qr
```

### שלב 2: הוספת כללים ל-.htaccess (30 דקות)
- ניתוח דפוסים מדויקים
- הוספת כללים ל-.htaccess
- בדיקת שהכללים עובדים

### שלב 3: הוספת כללים ל-WordPress (אם נדרש) (30 דקות)
- הוספת כללים ל-functions.php
- בדיקת שהכללים עובדים

### שלב 4: בדיקה חוזרת (15 דקות)
- בדיקת כל ה-URLs שוב
- וידוא שכל הכתובות פעילות
- וידוא ש-100% מהעמודים תקינים

---

## ✅ קריטריוני הצלחה

האתר ייחשב מוכן לפריסה רק אם:
- ✅ **כל הכתובות פעילות** - כל ה-URLs ב-sitemap עובדים
- ✅ **כל ההפניות נכונות** - redirects מפנים לכתובות נכונות
- ✅ **כללים כלליים** - לא redirects בודדים אלא כללים
- ✅ **100% מהעמודים תקינים** - בדיקה חוזרת מאשרת

---

## 📁 קבצים רלוונטיים

1. **`.htaccess`** - להוסיף כללים כלליים
2. **`wp-content/themes/bridge-child/functions.php`** - להוסיף כללים כלליים (אם נדרש)
3. **`wp-config.php`** - כבר עודכן עם Multi-Environment support

---

**נוצר על ידי:** צוות 3 (Gatekeeper)  
**תאריך:** 2026-01-14
