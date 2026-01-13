# מדריך ליצירת מפת אתר ב-WordPress
**Date:** January 14, 2026  
**Version:** 1.0

---

## 📋 סקירה כללית

מפת אתר (Sitemap) היא קובץ XML שמכיל רשימה של כל הדפים, פוסטים וקטגוריות באתר. זה עוזר למנועי חיפוש (Google, Bing) למצוא ולסרוק את כל התוכן באתר.

**WordPress מספק 3 דרכים ליצירת sitemap:**
1. **WordPress Core Sitemap** (מובנה מ-WordPress 5.5+) - מומלץ
2. **Yoast SEO Sitemap** (אם מותקן)
3. **פלאגינים אחרים** (Google XML Sitemaps, etc.)

---

## 🎯 אפשרות 1: WordPress Core Sitemap (מומלץ)

### מה זה?
WordPress 5.5+ כולל sitemap מובנה שמייצר sitemap אוטומטית. זה הפתרון הכי פשוט ולא דורש פלאגינים נוספים.

### איך זה עובד?
- **אוטומטי:** WordPress יוצר sitemap אוטומטית
- **עדכון אוטומטי:** מתעדכן כשמוסיפים/מעדכנים תוכן
- **URL:** `http://yoursite.com/wp-sitemap.xml`

### בדיקה אם פעיל:
```bash
# בדיקת sitemap:
curl -I http://localhost:9090/wp-sitemap.xml

# בדיקת תוכן:
curl -s http://localhost:9090/wp-sitemap.xml | head -50
```

### הפעלה (אם לא פעיל):
```bash
# בדוק אם sitemap מופעל:
docker compose exec wordpress wp option get wp_sitemap_enabled --allow-root

# הפעל sitemap (אם לא מופעל):
docker compose exec wordpress wp option update wp_sitemap_enabled 1 --allow-root

# עדכן permalinks (חשוב!):
docker compose exec wordpress wp rewrite flush --allow-root
```

### מה כלול ב-Sitemap?
- **Posts** - כל הפוסטים (`wp-sitemap-posts-post-1.xml`)
- **Pages** - כל הדפים (`wp-sitemap-posts-page-1.xml`)
- **Categories** - כל הקטגוריות (`wp-sitemap-taxonomies-category-1.xml`)
- **Tags** - כל התגיות (`wp-sitemap-taxonomies-post_tag-1.xml`)
- **Authors** - כל המחברים (`wp-sitemap-users-1.xml`)

### הגדרות נוספות:
```bash
# הגבל מספר פריטים ב-sitemap:
docker compose exec wordpress wp option update wp_sitemap_max_urls 2000 --allow-root

# עדכן permalinks אחרי שינויים:
docker compose exec wordpress wp rewrite flush --allow-root
```

---

## 🎯 אפשרות 2: Yoast SEO Sitemap

### מה זה?
Yoast SEO כולל פונקציונליות sitemap מתקדמת עם אפשרויות נוספות.

### בדיקה אם מותקן:
```bash
# בדוק אם Yoast SEO מותקן:
docker compose exec wordpress wp plugin list --allow-root | grep -i yoast

# בדוק אם Yoast SEO פעיל:
docker compose exec wordpress wp plugin list --status=active --allow-root | grep -i yoast
```

### הפעלה:
1. **דרך Admin Panel:**
   - Admin → SEO → General → Features
   - בדוק ש-"XML sitemaps" מופעל
   - שמור שינויים

2. **דרך WP-CLI:**
   ```bash
   # הפעל XML sitemaps:
   docker compose exec wordpress wp option update wpseo_xml '{"enablexmlsitemap":"1"}' --format=json --allow-root
   
   # עדכן permalinks:
   docker compose exec wordpress wp rewrite flush --allow-root
   ```

### בדיקה:
```bash
# בדיקת Yoast SEO Sitemap:
curl -I http://localhost:9090/sitemap_index.xml

# בדיקת תוכן:
curl -s http://localhost:9090/sitemap_index.xml | head -50
```

### עדכון ידני:
```bash
# עדכון sitemap דרך WP-CLI:
docker compose exec wordpress wp yoast sitemap rebuild --allow-root
```

---

## 🎯 אפשרות 3: פלאגינים אחרים

### Google XML Sitemaps
אם משתמשים בפלאגין "Google XML Sitemaps":
- **URL:** `http://yoursite.com/sitemap.xml`
- **עדכון:** אוטומטי או ידני דרך Admin Panel

### All in One SEO
אם משתמשים בפלאגין "All in One SEO":
- **URL:** `http://yoursite.com/sitemap.xml`
- **הגדרות:** Admin → All in One SEO → XML Sitemap

---

## ✅ המלצה למערכת שלנו

**למערכת שלנו מומלץ להשתמש ב-WordPress Core Sitemap** כי:
1. ✅ כבר מובנה ב-WordPress (לא צריך פלאגין נוסף)
2. ✅ אוטומטי ומתעדכן אוטומטית
3. ✅ עובד מצוין עם Google Search Console
4. ✅ לא דורש תחזוקה

**אם יש Yoast SEO מותקן:**
- אפשר להשתמש ב-Yoast SEO Sitemap (יותר אפשרויות)
- או להשאיר את WordPress Core Sitemap (פשוט יותר)

---

## 🔍 אימות Sitemap

### בדיקת תקינות XML:
```bash
# בדוק שהסייטמאפ הוא XML תקין:
curl -s http://localhost:9090/wp-sitemap.xml | xmllint --format - 2>&1 | head -20
```

### בדיקת תוכן:
1. פתח את ה-sitemap בדפדפן: `http://localhost:9090/wp-sitemap.xml`
2. בדוק שכל הדפים החשובים נמצאים
3. בדוק שכל הפוסטים נמצאים
4. בדוק שכל הקטגוריות נמצאות

### בדיקת Google Search Console:
1. פתח Google Search Console
2. Sitemaps → Add a new sitemap
3. הוסף: `wp-sitemap.xml` (או `sitemap_index.xml` אם Yoast)
4. בדוק שאין שגיאות

---

## 📝 עדכון Sitemap

### אוטומטי:
- WordPress Core Sitemap מתעדכן אוטומטית כשמוסיפים/מעדכנים תוכן
- Yoast SEO Sitemap מתעדכן אוטומטית (אם מוגדר כך)

### ידני (אם נדרש):
```bash
# עדכון permalinks (מאלץ עדכון sitemap):
docker compose exec wordpress wp rewrite flush --allow-root

# עדכון Yoast SEO sitemap (אם משתמשים):
docker compose exec wordpress wp yoast sitemap rebuild --allow-root
```

---

## 🔗 קישורים רלוונטיים

- WordPress Core Sitemap: https://wordpress.org/support/article/sitemaps/
- Yoast SEO Sitemap: https://yoast.com/help/xml-sitemaps-in-the-wordpress-seo-plugin/
- Google Search Console: https://search.google.com/search-console

---

**Guide Created By:** צוות 3 (Gatekeeper - Docs & Git)  
**Date:** 2026-01-14
