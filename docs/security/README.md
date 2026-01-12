# 🔐 מדריך אבטחה - Security Hardening Guide

**ענף:** task-002-security-hardening
**תאריך:** 12 בינואר 2026

## 📋 תוכן עניינים

1. [סקירה כללית](#סקירה-כללית)
2. [מה מיושם](#מה-מיושם)
3. [איך להפעיל](#איך-להפעיל)
4. [הצפנת משתני סביבה](#הצפנת-משתני-סביבה)
5. [בדיקות אבטחה](#בדיקות-אבטחה)
6. [פתרון בעיות](#פתרון-בעיות)

## 🎯 סקירה כללית

תיקיית האבטחה מכילה כלים ותצורות לחיזוק אבטחת WordPress 2026, כולל:

- **הסרת סיסמאות חשופות** - העברה ל-env מוצפן
- **Security Headers** - CSP, HSTS, X-Frame-Options
- **ניטור אבטחה** - לוגים ומעקב
- **הגנות מתקדמות** - XML-RPC, file uploads

## 🔧 מה מיושם

### 1. תצורת wp-config מאובטחת
**קובץ:** `docs/security/wp-config-secure.php`

#### שינויים מ-wp-config המקורי:
- ❌ הסרת סיסמה חשופה: `@Mj4ja%9P5du8Qzy`
- ✅ שימוש ב-`getenv()` למשתני סביבה
- ✅ חסימת גישה לקבצים רגישים
- ✅ תמיכה בקבצי .env מוצפנים

### 2. Security Headers Plugin
**קובץ:** `wp-content/mu-plugins/security-headers.php`

#### תכונות:
- ✅ **Content Security Policy (CSP)** - מניעת XSS
- ✅ **Security Headers** - X-Frame-Options, X-Content-Type-Options
- ✅ **HSTS** - אכיפת HTTPS
- ✅ **Feature Policy** - בקרת הרשאות
- ✅ **ניטור CSP violations** - דיווח על הפרות

### 3. תבנית משתני סביבה
**קובץ:** `docs/security/env-production-template`

#### משתנים מאובטחים:
- 🔐 סיסמאות DB מוצפנות
- 🔐 API keys מוגנים
- 🔐 SMTP configuration
- 🔐 Wordfence settings

## 🚀 איך להפעיל

### שלב 1: הכנת משתני סביבה

#### יצירת קובץ .env מאובטח
```bash
# העתק את התבנית
cp docs/security/env-production-template .env.production

# ערוך עם הסיסמאות האמיתיות
nano .env.production
```

#### הצפנת קובץ .env
```bash
# יצירת מפתח הצפנה
openssl rand -base64 32 > .env.key

# הצפנת הקובץ
ENCRYPTION_KEY=$(cat .env.key)
openssl enc -aes-256-cbc -salt -in .env.production -out .env.production.enc -k $ENCRYPTION_KEY
```

### שלב 2: עדכון wp-config.php

#### החלפת הקובץ המקורי
```bash
# גיבוי הקובץ המקורי
cp wp-config.php wp-config.php.backup

# העתקת הקובץ המאובטח
cp docs/security/wp-config-secure.php wp-config.php
```

### שלב 3: הפעלת Security Headers

ה-plugin `security-headers.php` פעיל אוטומטית ב-`wp-content/mu-plugins/`.

**לבדוק שהוא פעיל:**
```bash
ls -la wp-content/mu-plugins/
# Should show: security-headers.php
```

### שלב 4: התקנת Wordfence

#### דרך wp-cli או admin
```bash
# התקנה
wp plugin install wordfence --activate

# הגדרת API key
wp option update wordfence_api_key 'your_api_key_here'
```

## 🔐 הצפנת משתני סביבה

### יצירת מפתח הצפנה
```bash
# יצירת מפתח אקראי
openssl rand -base64 32 > .env.key

# שמירת המפתח בנפרד (לא ב-Git!)
echo ".env.key" >> .gitignore
```

### הצפנת קובץ ה-env
```bash
# קריאת מפתח ההצפנה
ENCRYPTION_KEY=$(cat .env.key)

# הצפנה
openssl enc -aes-256-cbc -salt \
  -in .env.production \
  -out .env.production.enc \
  -k $ENCRYPTION_KEY
```

### פענוח ב-deployment
```bash
# פענוח (אוטומטי ב-wp-config)
ENCRYPTION_KEY=$(cat .env.key)
openssl enc -aes-256-cbc -d \
  -in .env.production.enc \
  -out .env.production \
  -k $ENCRYPTION_KEY
```

## 📊 בדיקות אבטחה

### כלי בדיקה מומלצים

#### 1. Security Headers Check
```
https://securityheaders.com/
```
- בדוק ציון A+
- ודא כל ה-headers קיימים

#### 2. SSL Labs
```
https://www.ssllabs.com/ssltest/
```
- ציון A+ ל-SSL
- בדוק certificate validity

#### 3. Sucuri SiteCheck
```
https://sitecheck.sucuri.net/
```
- סריקת malware
- בדיקת blacklists

#### 4. Mozilla Observatory
```
https://observatory.mozilla.org/
```
- ציון אבטחה כולל
- המלצות לשיפור

### בדיקות מקומיות

#### בדיקת Headers
```bash
# בדיקת security headers
curl -I https://www.eyalamit.co.il

# Expected headers:
# Content-Security-Policy: ...
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
# Strict-Transport-Security: ...
```

#### בדיקת CSP
```bash
# בדיקת Content Security Policy
curl -s https://www.eyalamit.co.il | grep -i "content-security-policy"
```

#### בדיקת SSL
```bash
# בדיקת תעודת SSL
openssl s_client -connect eyalamit.co.il:443 -servername eyalamit.co.il < /dev/null
```

## ⚠️ פתרון בעיות

### בעיה: אתר לא נטען אחרי עדכון wp-config
```
✅ בדוק syntax errors: php -l wp-config.php
✅ בדוק permissions: chmod 644 wp-config.php
✅ בדוק .env file decryption
✅ שחזר מגיבוי: cp wp-config.php.backup wp-config.php
```

### בעיה: Security headers לא מופיעים
```
✅ בדוק שה-plugin פעיל
✅ בדוק .env CSP_ENABLED=true
✅ נקה cache של browser
✅ בדוק HTTPS (HSTS דורש SSL)
```

### בעיה: לא מצליח לפענח .env
```
✅ בדוק ש-.env.key קיים
✅ בדוק הרשאות קריאה
✅ בדוק שה-openssl מותקן
✅ נסה לפענח ידנית עם המפתח
```

### בעיה: Wordfence לא מתחבר
```
✅ בדוק API key
✅ בדוק firewall settings
✅ בדוק PHP allow_url_fopen
✅ בדוק outbound connections
```

## 📈 מדדי הצלחה

### לפני אופטימיזציה
| מדד | ערך | סיבה |
|-----|------|-------|
| Security Score | C- | פגיעויות מרובות |
| SSL Rating | A | בסיסי |
| Headers Score | F | חסרים headers |
| Malware Status | ⚠️ | סריקות חשודות |

### אחרי אופטימיזציה
| מדד | יעד | כלי מדידה |
|-----|------|------------|
| Security Score | A+ | Sucuri SiteCheck |
| SSL Rating | A+ | SSL Labs |
| Headers Score | A+ | Security Headers |
| Malware Status | Clean | Wordfence |

## 🔄 ניטור שוטף

### לוגים לבדוק
- `wp-content/security.log` - אירועי אבטחה
- `wp-content/csp-violations.log` - הפרות CSP
- Wordfence activity log
- Server access logs

### התראות להגדיר
- Failed login attempts > 5
- CSP violations
- Malware detections
- SSL certificate expiry

## 📞 תמיכה ובדיקות

### במקרה של בעיית אבטחה
1. **מיידי:** בדוק server logs
2. **חסימה:** הפעל emergency lockdown
3. **חקירה:** בדוק security logs
4. **תיקון:** עדכן headers או firewall
5. **דיווח:** עדכן את הצוות

### בדיקות תקופתיות
- **יומי:** Wordfence scans
- **שבועי:** Security headers check
- **חודשי:** Full security audit
- **רבעוני:** Penetration testing

## 🎉 סיכום

### מה הושג
- ✅ הסרנו סיסמאות חשופות
- ✅ הוספנו security headers מקיפים
- ✅ יצרנו מערכת ניטור אבטחה
- ✅ הכנו תשתית ל-Wordfence

### מה צריך להמשיך
- [ ] התקנת Wordfence מלאה
- [ ] הגדרת SMTP מאובטח
- [ ] בדיקות אבטחה מקיפות
- [ ] ניטור שוטף

### המלצות
1. **גיבוי תמיד** לפני שינויי אבטחה
2. **בדיקה הדרגתית** בסביבת staging
3. **ניטור מתמיד** אחר איומים
4. **עדכונים שוטפים** של plugins

---

**תאריך עדכון:** 12 בינואר 2026
**אחראי:** AI Assistant - Cursor
**סטטוס:** מוכן ליישום ולבדיקה