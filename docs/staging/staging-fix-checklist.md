# ✅ רשימת תיקון שגיאות סביבת בדיקות
**סביבה:** http://eyalamit-co-il-2026.s887.upress.link
**תאריך:** 2026-01-14

---

## 🚨 סטטוס נוכחי: שגיאות והגדרות חסרות

### בעיות ידועות:
- שגיאות Console
- סגנונות חסרים
- פונקציונליות חלקית
- Theme לא נראה כמו בסביבה המקומית

---

## 🔧 תיקונים דחופים

### 0. **תיקון CORS - localhost:9090 (ראשון וחשוב ביותר!)**
**שגיאה:** `Access to image/font at 'http://localhost:9090/...' has been blocked by CORS policy`

**פתרון ב-phpMyAdmin:**
```sql
-- CRITICAL: Replace localhost:9090 references
UPDATE wp_posts SET post_content = REPLACE(post_content, 'http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link');
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link');
UPDATE wp_options SET option_value = REPLACE(option_value, 'http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link') WHERE option_name LIKE '%url%';

-- Verification
SELECT COUNT(*) as localhost_remaining FROM wp_posts WHERE post_content LIKE '%localhost%';
SELECT COUNT(*) as localhost_meta_remaining FROM wp_postmeta WHERE meta_value LIKE '%localhost%';
```

**אחרי SQL - בצע:**
1. **Regenerate Elementor CSS:** Admin → Elementor → Tools → General → Regenerate CSS
2. **Flush Permalinks:** Settings → Permalinks → Save Changes
3. **Clear browser cache** ורענן את הדף

### 1. **תיקון Permalinks (שני!)**
**למה:** זה מתקן הרבה בעיות routing ו-404

**איך:**
1. היכנס ל-Admin: `http://eyalamit-co-il-2026.s887.upress.link/wp-admin`
2. Settings → Permalinks
3. שמור שינויים (ללא שינוי)
4. נקה cache בדפדפן

### 2. **תיקון Theme Files & Permissions**
**בדוק ב-File Manager:**
```bash
# הרשאות נכונות צריכות להיות:
755 - תיקיות (wp-content/, themes/, plugins/)
644 - קבצים (style.css, functions.php)

# אם לא נכון - תקן:
chmod 755 wp-content/themes/
chmod 644 wp-content/themes/bridge/style.css
```

### 3. **איפוס Cache & CSS**
**Elementor:**
1. Admin → Elementor → Tools → General
2. לחץ "Regenerate CSS"
3. לחץ "Sync Library"

**WordPress:**
1. Admin → Tools → Site Health
2. בדוק אם יש בעיות

### 4. **בדיקת Theme Activation**
**ב-WordPress Admin:**
1. Appearance → Themes
2. וודא ש-`Bridge Child` פעיל
3. אם לא - הפעל אותו

### 5. **תיקון Plugin Configurations**

**WooCommerce:**
1. Admin → WooCommerce → Status → Tools
2. לחץ "Clear transients"
3. לחץ "Reset capabilities"

**Yoast SEO:**
1. Admin → SEO → Tools
2. בדוק "File editor"
3. אם יש שגיאות - תקן

---

## 📊 בדיקות אבחון

### Console Errors
פתח Chrome DevTools → Console ובדוק:
- ❌ 404 errors על CSS/JS files
- ❌ JavaScript errors
- ❌ Network errors

### Network Tab
- ❌ קבצים שלא נטענים (404)
- ❌ CSS/JS עם errors
- ❌ Images חסרות

### Theme Check
השווה עם סביבה מקומית:
- אותו theme פעיל?
- אותן הגדרות?
- אותם plugins?

---

## 🛠️ תיקונים מתקדמים (אם צריך)

### אם Theme לא עובד:
```bash
# בדוק אם קבצים קיימים:
ls -la wp-content/themes/bridge/
ls -la wp-content/themes/bridge-child/

# אם חסרים - העלה מחדש דרך FTP
```

### אם CSS לא נטען:
1. בדוק file permissions
2. נקה browser cache
3. בדוק .htaccess

### אם JavaScript לא עובד:
1. בדוק אם jQuery נטען
2. בדוק conflicts עם plugins
3. השבת plugins אחד-אחד לבדיקה

---

## 📋 רשימת בדיקות סופית

### אחרי כל תיקון - בדוק:
- [ ] דף בית נטען ללא שגיאות
- [ ] Theme נראה נכון (לא broken)
- [ ] תפריט עובד
- [ ] אין Console errors קריטיים
- [ ] Admin נגיש
- [ ] Mobile responsive

### דפים ספציפיים לבדיקה:
- [ ] `/` - דף בית
- [ ] `/wp-admin/` - Admin
- [ ] `/צור-קשר/` - דף עם טופס
- [ ] `/shop/` - WooCommerce

---

## 🎯 קריטריון "מוכן"

האתר ייחשב מוכן כש:
- ✅ דף בית נטען תקין
- ✅ Theme נראה כמו בסביבה המקומית
- ✅ אין שגיאות 500/404
- ✅ Admin פועל
- ✅ Console נקי משגיאות קריטיות

---

## 📞 אם צריך עזרה

1. **צלם מסך** של השגיאות
2. **שלח** את הודעת השגיאה המדויקת
3. **ציין** איזה דף נבדק

נפתור יחד! 🔧

---

**נוצר על ידי:** צוות 3 (Gatekeeper)
**תאריך:** 2026-01-14