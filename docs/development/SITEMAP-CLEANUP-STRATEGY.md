# אסטרטגיה לניקוי Sitemap - מיפוי Redirects ועמודים אמיתיים
**תאריך:** 2026-01-14  
**מטרה:** לזהות מה הפניה של כתובת ישנה לדף חדש ומה עמוד אמיתי

---

## 🎯 המטרה

לפי בקשת המנכ"ל:
- **למפות נכון את האתר** - להבין מה הפניה של כתובת ישנה לדף חדש
- **לזהות מה עמוד אמיתי** - מה צריך להיות ב-sitemap ומה לא
- **לנקות את ה-sitemap** - להסיר redirects ולהותיר רק עמודים אמיתיים

---

## 📋 תהליך העבודה

### שלב 1: מיפוי כל ה-URLs

**סקריפט:** `scripts/map_redirects.php`

**מה הוא עושה:**
1. טוען את כל ה-URLs מה-sitemap
2. בודק כל URL:
   - אם מחזיר 200 OK → עמוד אמיתי ✅
   - אם מחזיר 301/302 → redirect 🔄
   - אם מחזיר שגיאה → error ❌
3. יוצר מפה של כל ה-redirects:
   - מה הכתובת הישנה (from)
   - מה הכתובת החדשה (to)
   - איזה סוג redirect (301/302)

**הרצה:**
```bash
php scripts/map_redirects.php
```

**תוצאות:**
- `docs/testing/reports/redirects-mapping.json` - מפה מפורטת
- `docs/testing/reports/redirects-mapping-report.md` - דוח מפורט

---

### שלב 2: ניתוח המפה

**מה לבדוק:**
1. **דפוסים חוזרים:**
   - האם יש דפוסים ברורים ב-redirects?
   - האם יש קטגוריות שלמות שצריך להסיר?

2. **עמודים אמיתיים:**
   - כמה עמודים אמיתיים יש?
   - מה הם העמודים החשובים?

3. **Redirects ישנים:**
   - מה הכתובות הישנות?
   - לאן הן מפנות?
   - האם צריך לשמור עליהן ב-sitemap?

---

### שלב 3: ניקוי Sitemap

**סקריפט:** `scripts/clean_sitemap.php`

**מה הוא עושה:**
1. טוען את מפת ה-redirects
2. יוצר רשימה של URLs להסרה (redirects)
3. יוצר רשימה של URLs לשמירה (עמודים אמיתיים)

**הרצה:**
```bash
php scripts/clean_sitemap.php
```

**תוצאות:**
- `docs/testing/reports/clean-sitemap-data.json` - רשימת URLs לניקוי

---

### שלב 4: עדכון Yoast SEO Sitemap

**אפשרויות:**

#### אפשרות A: הסרת Redirects דרך Yoast SEO Settings
1. Admin → SEO → General → Features
2. XML Sitemaps → Settings
3. הסרת קטגוריות/Post Types שמכילים רק redirects

#### אפשרות B: עדכון ידני של Sitemap
1. לבדוק כל קטגוריה ב-Yoast SEO
2. להסיר קטגוריות שמכילות רק redirects
3. לשמור רק עמודים אמיתיים

#### אפשרות C: יצירת Sitemap חדש
1. ליצור sitemap חדש עם רק עמודים אמיתיים
2. להחליף את ה-sitemap הישן

---

## 🔍 ניתוח דפוסים

### דפוסים שצפויים:

1. **Blog URLs → Redirects:**
   - כל ה-URLs של `/Blog` מפנים לכתובות אחרות
   - **החלטה:** להסיר מ-sitemap או לתקן את ה-redirects

2. **QR Codes → Redirects:**
   - כל ה-URLs של `/qr` מפנים לכתובות אחרות
   - **החלטה:** להסיר מ-sitemap או לתקן את ה-redirects

3. **קטגוריות ישנות → Redirects:**
   - קטגוריות ישנות מפנות לעמודים חדשים
   - **החלטה:** לשמור ב-sitemap (למטרות SEO) או להסיר

---

## ✅ קריטריונים להחלטה

### מתי להסיר מ-Sitemap:
- ✅ אם זה redirect ל-URL אחר שכבר ב-sitemap
- ✅ אם זה redirect לכתובת חיצונית
- ✅ אם זה redirect לכתובת שגויה (404)

### מתי לשמור ב-Sitemap:
- ✅ אם זה redirect ל-URL חדש (למטרות SEO)
- ✅ אם זה עמוד אמיתי (200 OK)
- ✅ אם זה חשוב ל-SEO

---

## 📊 דוגמאות לתוצאות צפויות

### לפני ניקוי:
- סה"כ URLs: 1,233
- עמודים אמיתיים: ~1,005
- Redirects: ~228

### אחרי ניקוי:
- סה"כ URLs: ~1,005 (רק עמודים אמיתיים)
- Redirects: 0 (הוסרו מ-sitemap)
- אחוז הצלחה: 100%

---

## 🚀 תוכנית ביצוע

### שלב 1: מיפוי (15 דקות)
```bash
php scripts/map_redirects.php
```

### שלב 2: ניתוח (30 דקות)
- בדיקת הדוח
- זיהוי דפוסים
- החלטות על מה להסיר

### שלב 3: ניקוי (30 דקות)
```bash
php scripts/clean_sitemap.php
```

### שלב 4: עדכון Sitemap (30 דקות)
- עדכון Yoast SEO settings
- או עדכון ידני

### שלב 5: בדיקה חוזרת (15 דקות)
- בדיקת sitemap חדש
- וידוא שכל העמודים תקינים

---

## 📁 קבצים רלוונטיים

1. **`scripts/map_redirects.php`** - סקריפט למיפוי redirects
2. **`scripts/clean_sitemap.php`** - סקריפט לניקוי sitemap
3. **`docs/testing/reports/redirects-mapping.json`** - מפת redirects
4. **`docs/testing/reports/redirects-mapping-report.md`** - דוח מפורט
5. **`docs/testing/reports/clean-sitemap-data.json`** - רשימת URLs לניקוי

---

**נוצר על ידי:** צוות 3 (Gatekeeper)  
**תאריך:** 2026-01-14
