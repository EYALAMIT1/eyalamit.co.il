# רשימת בדיקות מקיפה לפני פריסה לייצור
**Date:** January 14, 2026  
**Status:** 🟡 READY_TO_START  
**Task ID:** EA-V11-PRE-DEPLOYMENT-CHECK

---

## 📋 סקירה כללית

רשימת בדיקות מקיפה לפני פריסה לייצור (Phase 5). כוללת בדיקות טכניות, פונקציונליות, SEO, אבטחה וביצועים.

**חשוב:** לפי החלטת המנכ"ל, בדיקות ביצועים (Lighthouse Performance) יבוצעו רק בפרודקשן. כאן אנחנו בודקים שהכל עובד נכון ומוכן לפריסה.

---

## 🔍 שלב 1: בדיקות טכניות בסיסיות

### 1.1 בדיקת תקינות האתר הכללית
- [ ] **האתר נטען** - `http://localhost:9090` נפתח ללא שגיאות
- [ ] **אין שגיאות 500** - כל הדפים נטענים בהצלחה
- [ ] **אין שגיאות 404** - כל הדפים נגישים
- [ ] **Admin Panel נגיש** - `http://localhost:9090/wp-admin` נפתח
- [ ] **כניסה ל-Admin** - אפשר להתחבר לחשבון Admin
- [ ] **Zero Console Errors** - אין שגיאות JavaScript/CORS/Network

### 1.2 בדיקת שגיאות ב-Logs
```bash
# בדיקת שגיאות PHP:
docker compose logs wordpress | grep -iE "error|fatal|warning" | tail -50

# בדיקת שגיאות WordPress:
docker compose exec wordpress tail -50 /var/www/html/wp-content/debug.log 2>/dev/null || echo "No debug.log"
```

- [ ] **אין שגיאות קריטיות** - רק אזהרות מותרות
- [ ] **אין שגיאות PHP Fatal** - האתר חייב לעבוד ללא שגיאות Fatal
- [ ] **אין שגיאות WordPress** - אין שגיאות ב-debug.log

### 1.3 בדיקת גרסאות
```bash
# בדיקת גרסת WordPress:
docker compose exec wordpress wp core version --allow-root

# בדיקת גרסת PHP:
docker compose exec wordpress php -v

# בדיקת גרסת DB:
docker compose exec wordpress wp core version --extra --allow-root
```

- [ ] **WordPress:** 6.9 (או גרסה עדכנית)
- [ ] **PHP:** 8.3
- [ ] **DB Version:** עדכני

---

## 🗺️ שלב 2: מפת אתר (Sitemap) - עדיפות גבוהה

### 2.1 בדיקת מפת אתר קיימת

**WordPress Core Sitemap (WordPress 5.5+):**
```bash
# בדיקת WordPress Core Sitemap:
curl -s http://localhost:9090/wp-sitemap.xml | head -30

# או:
curl -s http://localhost:9090/wp-sitemap-posts-post-1.xml | head -30
```

- [ ] **WordPress Core Sitemap:** ✅ קיים / ❌ לא קיים

**Yoast SEO Sitemap (אם מותקן):**
```bash
# בדיקת Yoast SEO Sitemap:
curl -s http://localhost:9090/sitemap_index.xml | head -30

# או:
curl -s http://localhost:9090/sitemap.xml | head -30
```

- [ ] **Yoast SEO Sitemap:** ✅ קיים / ❌ לא קיים / ⚠️ לא מותקן

### 2.2 יצירת/עדכון מפת אתר

#### אפשרות A: WordPress Core Sitemap (מומלץ - כבר מובנה)

**WordPress 5.5+ כולל sitemap מובנה:**
- **URL:** `http://localhost:9090/wp-sitemap.xml`
- **אוטומטי:** WordPress יוצר sitemap אוטומטית
- **עדכון:** מתעדכן אוטומטית כשמוסיפים/מעדכנים תוכן

**בדיקה:**
```bash
# בדוק שהסייטמאפ קיים:
curl -I http://localhost:9090/wp-sitemap.xml

# בדוק את התוכן:
curl -s http://localhost:9090/wp-sitemap.xml | head -50
```

**הפעלה (אם לא פעיל):**
```bash
# בדוק אם sitemap מופעל:
docker compose exec wordpress wp option get wp_sitemap_enabled --allow-root

# הפעל sitemap (אם לא מופעל):
docker compose exec wordpress wp option update wp_sitemap_enabled 1 --allow-root
```

#### אפשרות B: Yoast SEO Sitemap (אם מותקן)

**הפעלת Yoast SEO Sitemap:**
1. Admin → SEO → General → Features
2. בדוק ש-"XML sitemaps" מופעל
3. שמור שינויים

**בדיקה:**
```bash
# בדוק שהסייטמאפ קיים:
curl -I http://localhost:9090/sitemap_index.xml

# בדוק את התוכן:
curl -s http://localhost:9090/sitemap_index.xml | head -50
```

**עדכון ידני (אם נדרש):**
```bash
# עדכון sitemap דרך WP-CLI:
docker compose exec wordpress wp yoast sitemap rebuild --allow-root
```

#### אפשרות C: יצירת Sitemap ידנית (אם אין פלאגין)

**אם אין sitemap, ניתן ליצור ידנית:**
```bash
# יצירת sitemap דרך WP-CLI:
docker compose exec wordpress wp sitemap generate --allow-root
```

### 2.3 אימות מפת אתר

**בדיקות:**
1. **בדיקת תקינות XML:**
   ```bash
   # בדוק שהסייטמאפ הוא XML תקין:
   curl -s http://localhost:9090/wp-sitemap.xml | xmllint --format - 2>&1 | head -20
   ```

2. **בדיקת תוכן:**
   - [ ] כל הדפים החשובים נמצאים ב-sitemap
   - [ ] כל הפוסטים נמצאים ב-sitemap
   - [ ] כל הקטגוריות נמצאים ב-sitemap
   - [ ] אין שגיאות XML

3. **בדיקת Google Search Console (אם יש גישה):**
   - [ ] הגש sitemap ל-Google Search Console
   - [ ] בדוק שאין שגיאות

**תוצאה צפויה:**
- ✅ Sitemap קיים ונגיש
- ✅ Sitemap מכיל את כל התוכן החשוב
- ✅ Sitemap תקין (XML valid)

---

## 🔒 שלב 3: בדיקות אבטחה

### 3.1 Security Headers (כבר מוטמע ב-Phase 4)
- [ ] **X-Frame-Options:** ✅ מאומת
- [ ] **X-Content-Type-Options:** ✅ מאומת
- [ ] **X-XSS-Protection:** ✅ מאומת
- [ ] **Referrer-Policy:** ✅ מאומת
- [ ] **Permissions-Policy:** ✅ מאומת
- [ ] **Content-Security-Policy:** ✅ מאומת

### 3.2 בדיקות אבטחה נוספות
- [ ] **wp-config.php:** לא נגיש מהאינטרנט
- [ ] **.htaccess:** מוגדר נכון
- [ ] **Admin Users:** רק משתמשים נדרשים
- [ ] **File Permissions:** נכונים (644 לקבצים, 755 לתיקיות)

---

## 🎨 שלב 4: בדיקות פונקציונליות

### 4.1 בדיקת תבנית Bridge
- [ ] **עמוד ראשי** - נטען ונציג נכון
- [ ] **תפריט ניווט** - עובד ונראה נכון
- [ ] **Footer** - נטען ונציג נכון
- [ ] **תפריט נייד** - עובד במכשירים ניידים
- [ ] **Responsive** - האתר נראה טוב במובייל, טאבלט, דסקטופ

### 4.2 בדיקת Elementor
- [ ] **כל העמודים שנבנו ב-Elementor** - נטענים נכון
- [ ] **Layouts** - נראים נכון
- [ ] **Widgets** - עובדים נכון
- [ ] **אין שגיאות** - אין שגיאות ב-Console

### 4.3 בדיקת טפסים
- [ ] **טופס צור קשר** - אפשר לשלוח טופס בדיקה
- [ ] **אימייל נשלח** - אימייל מגיע (בדוק inbox)
- [ ] **הודעת הצלחה** - מופיעה לאחר שליחה
- [ ] **וולידציה עובדת** - שדות חובה נבדקים

### 4.4 בדיקת WooCommerce (אם יש)
- [ ] **דף חנות** - נטען
- [ ] **דף מוצר** - נטען ונציג נכון
- [ ] **עגלה** - עובדת
- [ ] **Checkout** - נטען וטופס עובד

---

## 🔍 שלב 5: בדיקות SEO

### 5.1 Schema Markup (כבר מוטמע ב-Phase 2.3)
- [ ] **Person Schema:** ✅ מאומת
- [ ] **Specialist Schema:** ✅ מאומת
- [ ] **FAQ Schema:** ✅ מאומת

### 5.2 Meta Tags
- [ ] **Title Tags:** קיימים בכל דף
- [ ] **Meta Descriptions:** קיימים בכל דף
- [ ] **Open Graph Tags:** קיימים (אם יש)
- [ ] **Twitter Cards:** קיימים (אם יש)

### 5.3 Alt Text (כבר מוטמע ב-Phase 2.3)
- [ ] **כל התמונות יש להן alt text**
- [ ] **Alt text תיאורי ומשמעותי**

### 5.4 Robots.txt
```bash
# בדיקת robots.txt:
curl -s http://localhost:9090/robots.txt
```

- [ ] **robots.txt קיים**
- [ ] **robots.txt מוגדר נכון**
- [ ] **Sitemap מוזכר ב-robots.txt**

---

## 📊 שלב 6: בדיקות ביצועים (רק וידוא טכני, לא מדידות)

### 6.1 בדיקות טכניות (לא מדידות ביצועים)
- [ ] **Critical CSS:** ✅ מוטמע ב-`<head>`
- [ ] **CSS Defer:** ✅ פעיל
- [ ] **WebP:** ✅ פונקציות מוטמעות
- [ ] **Lazy Loading:** ✅ פעיל
- [ ] **Image Optimization:** ✅ תמונות מותאמות

**הערה:** מדידות ביצועים (Lighthouse Performance Score) יבוצעו רק בפרודקשן לפי החלטת המנכ"ל.

---

## 🧪 שלב 7: בדיקות אוטומטיות

### 7.1 Playwright Tests
```bash
# הרצת כל הבדיקות:
npx playwright test

# או בדיקה ספציפית:
npx playwright test tests/playwright-example.spec.js
```

- [ ] **כל הבדיקות עוברות** - 12/12 tests passing
- [ ] **Zero Console Errors** - נשמר

### 7.2 PHPCS (Code Quality)
```bash
# בדיקת איכות קוד:
./vendor/bin/phpcs --standard=WordPress wp-content/themes/bridge-child/
```

- [ ] **Code Quality:** נבדק (אם יש בעיות - רשום בדוח)

---

## 📝 שלב 8: תיעוד וסיכום

### 8.1 רשימת דפים חשובים
צור רשימה של כל הדפים החשובים באתר:
- [ ] עמוד ראשי
- [ ] דפי תוכן חשובים
- [ ] דפי קטגוריות
- [ ] דפי מוצרים (אם יש WooCommerce)
- [ ] דפי טפסים

### 8.2 רשימת פלאגינים פעילים
```bash
# רשימת פלאגינים פעילים:
docker compose exec wordpress wp plugin list --status=active --allow-root
```

- [ ] **רשימת פלאגינים פעילים** - מתועדת
- [ ] **גרסאות פלאגינים** - מתועדות

### 8.3 דוח סיכום
צור דוח ב: `docs/testing/reports/pre-deployment-comprehensive-report.md`

**תבנית הדוח:**
```markdown
# Pre-Deployment Comprehensive Report
**Date:** [תאריך]
**Team:** [Team 1/Team 2]
**Status:** 🟢 READY / 🔴 ISSUES_FOUND

## Test Results Summary
- Technical Tests: ✅ / ❌
- Sitemap: ✅ / ❌
- Security: ✅ / ❌
- Functionality: ✅ / ❌
- SEO: ✅ / ❌
- Automated Tests: ✅ / ❌

## Issues Found
- [רשימת בעיות אם היו]

## Recommendations
- [המלצות לפני פריסה]

## Ready for Deployment
- ✅ Yes / ❌ No
```

---

## ⚠️ קריטריוני הצלחה

האתר נחשב מוכן לפריסה רק אם:
- ✅ כל הבדיקות הטכניות עברו
- ✅ Sitemap קיים ומעודכן
- ✅ כל הבדיקות הפונקציונליות עברו
- ✅ Zero Console Errors נשמר
- ✅ כל בדיקות האבטחה עברו
- ✅ דוח סיכום נוצר

---

## 📚 קבצים רלוונטיים

- `docs/testing/PRE-DEPLOYMENT-COMPREHENSIVE-CHECKLIST.md` - רשימת בדיקות זו
- `docs/PRE-PRODUCTION-CHECKLIST.md` - רשימת בדיקות קיימת
- `docs/testing/reports/pre-deployment-comprehensive-report.md` - דוח סיכום (ליצור)

---

**Checklist Created By:** צוות 3 (Gatekeeper - Docs & Git)  
**Date:** 2026-01-14  
**Status:** 🟡 READY_TO_START
