# 🔒 תוכנית חיזוק אבטחה (Security Hardening Plan)

**ענף:** task-002-security-hardening
**תאריך:** 12 בינואר 2026
**מטרה:** העלאת ציון אבטחה ל-A+ והגנה מקסימלית

---

## 📊 מצב אבטחה נוכחי (מניתוח)

### בעיות קריטיות זוהו
- ❌ **WordPress 5.2.2** - גרסה מיושנת עם פגיעויות ידועות
- ❌ **סיסמת DB חשופה** - `@Mj4ja%9P5du8Qzy` ב-wp-config.php
- ❌ **ללא Wordfence/Sucuri** - אין הגנה מפני התקפות
- ❌ **ללא 2FA** - אימות חד-שלבי בלבד
- ❌ **File editor enabled** - אפשרות עריכת קבצים דרך admin

### בעיות בינוניות
- ⚠️ **ללא security headers** - CSP, HSTS, X-Frame-Options
- ⚠️ **ללא rate limiting** - הגנה מפני brute force
- ⚠️ **ללא backup encryption** - גיבויים לא מוצפנים
- ⚠️ **SMTP פנימי** - אין הגנה מפני spam

---

## 🎯 יעדי אבטחה

### ציוני אבטחה מוגדרים
- **Sucuri Security Score:** A+ (95-100)
- **Mozilla Observatory:** A+ (95-100)
- **Security Headers:** Grade A
- **Zero Vulnerabilities:** סריקות נקיות

### מדדי אבטחה
- **Failed Login Attempts:** < 5% מהצלחות
- **Malware Scans:** Clean 100%
- **SSL Rating:** A+
- **Uptime Security:** 99.9%

---

## 🛡️ תוכנית חיזוק אבטחה

### שלב 1: תשתית אבטחה בסיסית (יום 1)

#### 1.1 הסרת סיסמאות חשופות
**בעיה:** סיסמת DB חשופה ב-wp-config.php
```php
// נוכחי (מסוכן)
define('DB_PASSWORD', '@Mj4ja%9P5du8Qzy');

// חדש (בטוח)
define('DB_PASSWORD', getenv('DB_PASSWORD'));
```

**פעולות:**
- [ ] העברת סיסמאות ל-env variables
- [ ] יצירת .env.production עם סיסמאות מוצפנות
- [ ] עדכון docker-compose.yml להשתמש ב-env
- [ ] בדיקת גישה ל-DB עם סיסמאות חדשות

#### 1.2 הפעלת Security Headers
**קובץ:** `wp-content/mu-plugins/security-headers.php`
```php
<?php
// Security Headers Implementation
function add_security_headers() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google-analytics.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://www.google-analytics.com");

    // HSTS (HTTP Strict Transport Security)
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
add_action('send_headers', 'add_security_headers');
```

#### 1.3 חסימת XML-RPC (אם לא נחוץ)
```php
// Block XML-RPC if not needed for Jetpack/etc
add_filter('xmlrpc_enabled', '__return_false');
```

### שלב 2: הגנות מתקדמות (יום 2-3)

#### 2.1 התקנת Wordfence Security
**התקנה:**
- [ ] הורדת והתקנת Wordfence plugin
- [ ] הגדרת firewall rules
- [ ] הפעלת real-time scanning
- [ ] הגדרת alerts למייל

**הגדרות מומלצות:**
```php
// Wordfence configuration recommendations
define('WORDFENCE_DISABLE_FILE_MODS', false); // Enable file scanning
define('WORDFENCE_DISABLE_LIVE_TRAFFIC', false); // Enable traffic monitoring
```

#### 2.2 הגנה מפני Brute Force
**Limit Login Attempts plugin או Wordfence built-in:**
- [ ] הגבלת 5 נסיונות כניסה לכל IP
- [ ] חסימה זמנית לאחר כשלונות
- [ ] הודעות מיוחדות למנהלים

#### 2.3 הצפנת קבצים רגישים
**Encryption strategy:**
- [ ] הצפנת קובץ .env עם openssl
- [ ] שמירת מפתח הצפנה בנפרד
- [ ] תהליך decryption אוטומטי ב-deployment

#### 2.4 SMTP חיצוני לאימיילים
**הגדרת SMTP עם SendGrid/Mailgun:**
```php
// WP Mail SMTP configuration
define('WPMS_ON', true);
define('WPMS_MAILER', 'sendgrid');
define('WPMS_SENDGRID_API_KEY', getenv('SENDGRID_API_KEY'));
```

### שלב 3: חיזוק שרת ומערכת (יום 4)

#### 3.1 חסימת גישה לקבצים רגישים
**קובץ .htaccess:**
```apache
# Block access to sensitive files
<Files "wp-config.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files "*.sql">
    Order Allow,Deny
    Deny from all
</Files>

# Block PHP execution in uploads
<Directory "wp-content/uploads">
    <Files "*.php">
        Order Allow,Deny
        Deny from all
    </Files>
</Directory>
```

#### 3.2 ניטור ו-logging משופר
**הפעלת debug logging מאובטח:**
```php
// Secure debug logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Custom log file with proper permissions
ini_set('error_log', '/var/log/wordpress/debug.log');
```

#### 3.3 חסימת user enumeration
```php
// Prevent username enumeration
add_action('wp_head', function() {
    if (is_author()) {
        wp_redirect(home_url());
        exit;
    }
});
```

### שלב 4: בדיקות ואימות (יום 5)

#### 4.1 בדיקות אבטחה מקיפות
**כלי בדיקה:**
- [ ] **Sucuri SiteCheck** - סריקת malware
- [ ] **Wordfence Scanner** - סריקת קבצים
- [ ] **Mozilla Observatory** - SSL ו-headers
- [ ] **Security Headers** - ציון headers

#### 4.2 Penetration Testing
**בדיקות ידניות:**
- [ ] נסיון SQL injection
- [ ] נסיון XSS attacks
- [ ] נסיון file upload vulnerabilities
- [ ] בדיקת session security

#### 4.3 Performance Impact
**בדיקת השפעה על ביצועים:**
- [ ] מדידת TTFB לפני/אחרי
- [ ] בדיקת CPU/memory usage
- [ ] זמני טעינה של דפים

---

## 📁 מבנה קבצי אבטחה

```
wp-content/
├── mu-plugins/
│   ├── performance-optimization.php
│   ├── security-headers.php          # Security headers
│   └── security-hardening.php        # General security
├── plugins/
│   └── wordfence/                     # Security plugin
├── themes/
│   └── bridge-child/
│       └── security/                  # Security templates
└── uploads/
    └── .htaccess                      # Upload restrictions

.env files:
├── .env.local                         # Development
├── .env.production                    # Production (encrypted)
└── .env.keys                          # Encryption keys (separate)
```

---

## 🔐 Encryption Strategy

### הצפנת משתני סביבה
```bash
# Encrypt .env file
openssl enc -aes-256-cbc -salt -in .env.production -out .env.production.enc -k $ENCRYPTION_KEY

# Decrypt during deployment
openssl enc -aes-256-cbc -d -in .env.production.enc -out .env.production -k $ENCRYPTION_KEY
```

### ניהול מפתחות
- [ ] שמירת מפתחות ב-KMS (AWS) או Vault
- [ ] רוטציה של מפתחות כל 90 יום
- [ ] גיבוי מוצפן של מפתחות

---

## 📊 מדדי הצלחה

### לפני אופטימיזציה
| מדד | ערך נוכחי | בעיה |
|-----|------------|-------|
| Security Score | C- | פגיעויות מרובות |
| SSL Rating | A | בסיסי |
| Malware Status | ⚠️ Warning | סריקות חשודות |
| Login Security | None | ללא הגנה |

### אחרי אופטימיזציה
| מדד | יעד | כלי מדידה |
|-----|------|------------|
| Security Score | A+ | Sucuri SiteCheck |
| SSL Rating | A+ | SSL Labs |
| Malware Status | Clean | Wordfence Scan |
| Login Security | Protected | Failed attempts <5% |

---

## ⚠️ סיכונים ופתרונות

### סיכונים
1. **חסימת גישה** - שגיאות ב-firewall
2. **האטה בביצועים** - overhead של security
3. **תקלות במיילים** - SMTP configuration
4. **בעיות ב-plugins** - conflicts עם security

### פתרונות
1. **Testing environment** - בדיקה בסטייג' לפני פרודקשן
2. **Monitoring** - alerts מיידיים לבעיות
3. **Rollback plan** - כיבוי מהיר של הגנות
4. **Documentation** - הוראות שחזור מפורטות

---

## 📋 רשימת בדיקה ליישום

### שלב 1: תשתית בסיסית
- [ ] הסרת סיסמאות חשופות מ-wp-config.php
- [ ] יצירת .env מוצפן עם סיסמאות
- [ ] הפעלת security headers בסיסיים
- [ ] חסימת XML-RPC אם לא נחוץ

### שלב 2: הגנות מתקדמות
- [ ] התקנת Wordfence Security
- [ ] הגדרת firewall rules
- [ ] הפעלת rate limiting
- [ ] הגדרת SMTP חיצוני

### שלב 3: חיזוק שרת
- [ ] חסימת גישה לקבצים רגישים
- [ ] שיפור logging ואימות
- [ ] מניעת user enumeration
- [ ] הגבלת file uploads

### שלב 4: בדיקות ואימות
- [ ] סריקות אבטחה מקיפות
- [ ] penetration testing
- [ ] בדיקת ביצועים
- [ ] תיעוד וגיבוי

---

## 🔗 קישורים וכלים

### כלי אבטחה מומלצים
- [Sucuri SiteCheck](https://sitecheck.sucuri.net/)
- [Mozilla Observatory](https://observatory.mozilla.org/)
- [Security Headers](https://securityheaders.com/)
- [SSL Labs](https://www.ssllabs.com/ssltest/)

### תיעוד
- [WordPress Security](https://wordpress.org/support/article/hardening-wordpress/)
- [Wordfence Documentation](https://www.wordfence.com/help/)
- [OWASP Guidelines](https://owasp.org/www-project-top-ten/)

---

## 📞 תמיכה ובדיקות

### במקרה של בעיות אבטחה
1. **מיידי:** כיבוי Wordfence firewall
2. **בדיקה:** הסרת security headers
3. **שחזור:** rollback מגיבוי
4. **דיווח:** לצוות האבטחה

### בדיקות שגרה
- **יומי:** Wordfence scans
- **שבועי:** Security headers check
- **חודשי:** Full security audit
- **רבעוני:** Penetration testing

---

**תאריך עדכון:** 12 בינואר 2026
**אחראי:** AI Assistant - Cursor
**סטטוס:** מוכן ליישום