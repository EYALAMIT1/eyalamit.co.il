# מפת אתר - קובץ CSV לניתוח
**תאריך יצירה:** 2026-01-14  
**גרסה:** v1.0  
**פורמט:** CSV (Comma-Separated Values)

---

## 📊 תיאור הקובץ

קובץ CSV זה מכיל את כל ה-URLs במפת האתר עם שדות מפורטים לניתוח.

**קובץ:** `SITEMAP-v1.0-2026-01-14.csv`

---

## 📋 שדות בקובץ

| שם שדה | תיאור | דוגמה |
|--------|-------|-------|
| **URL** | כתובת העמוד המלאה | `http://localhost:9090/Blog/post-name/` |
| **Content_Type** | סוג התוכן | `Blog Post`, `Page`, `Attachment`, `Category`, `Tag`, וכו' |
| **Category** | קטגוריה | `Blog`, `WooCommerce`, `QR`, `Portfolio`, `Image`, `File`, וכו' |
| **Status** | סטטוס בדיקה | `OK`, `ERROR`, `UNKNOWN` |
| **HTTP_Code** | קוד HTTP | `200`, `301`, `404`, וכו' |
| **Response_Time_MS** | זמן תגובה במילישניות | `105.44`, `95.76`, וכו' |
| **Has_Errors** | האם יש שגיאות | `Yes`, `No` |
| **Error_Details** | פרטי שגיאות | `cURL Error: ...`, `HTTP 301`, וכו' |
| **Size_Bytes** | גודל התוכן בבתים | `53427`, `75789`, וכו' |
| **Path** | נתיב ה-URL | `/Blog/post-name/`, `/shop/`, וכו' |
| **First_Path_Segment** | החלק הראשון בנתיב | `Blog`, `shop`, `qr`, וכו' |

---

## 📊 סוגי תוכן (Content_Type)

- **Homepage** - עמוד ראשי
- **Blog Post** - פוסטים בבלוג
- **Page** - עמודי תוכן סטטיים
- **Attachment** - קבצים מצורפים (תמונות, קבצים)
- **Category** - קטגוריות
- **Tag** - תגיות
- **Portfolio** - עמודי Portfolio
- **Shop** - עמודי WooCommerce
- **QR Code** - QR Codes
- **Author** - עמודי מחברים
- **Testimonial** - עדויות
- **Other** - אחרים

---

## 📊 קטגוריות (Category)

- **Blog** - תוכן בלוג
- **WooCommerce** - תוכן Shop
- **QR** - QR Codes
- **Portfolio** - Portfolio
- **Image** - תמונות
- **File** - קבצים (PDF, DOC, וכו')
- **Category** - קטגוריות
- **Tag** - תגיות
- **Author** - מחברים
- **Testimonial** - עדויות
- **Page** - עמודי תוכן

---

## 🔍 דוגמאות לניתוח

### ניתוח לפי סוג תוכן:
```sql
SELECT Content_Type, COUNT(*) as Count 
FROM sitemap 
GROUP BY Content_Type 
ORDER BY Count DESC;
```

### ניתוח לפי סטטוס:
```sql
SELECT Status, COUNT(*) as Count 
FROM sitemap 
GROUP BY Status;
```

### ניתוח redirects:
```sql
SELECT * 
FROM sitemap 
WHERE Status = 'ERROR' AND HTTP_Code = 301;
```

### ניתוח לפי קטגוריה:
```sql
SELECT Category, COUNT(*) as Count 
FROM sitemap 
GROUP BY Category 
ORDER BY Count DESC;
```

---

## 📝 הערות

1. **פורמט CSV:**
   - שדות מוקפים במרכאות כפולות (`"`)
   - מרכאות בשדות מוחלפות ב-`""`
   - שורות מופרדות ב-newline (`\n`)

2. **קידוד:**
   - UTF-8
   - URLs עם encoding עברי נשמרים כמו שהם

3. **נתוני בדיקה:**
   - נתוני בדיקה מתוך `sitemap-validation-results.json`
   - אם אין נתוני בדיקה, השדות יהיו ריקים

---

## 🔗 קבצים קשורים

- `SITEMAP-v1.0-2026-01-14.md` - דוח מפורט
- `SITEMAP-v1.0-2026-01-14-urls.json` - רשימת URLs (JSON)
- `SITEMAP-v1.0-2026-01-14-index.xml` - Sitemap Index (XML)
- `SITEMAP-v1.0-2026-01-14.csv` - קובץ זה (CSV)

---

**נוצר על ידי:** צוות 3 (Gatekeeper - Docs & Git)  
**תאריך:** 2026-01-14  
**גרסה:** v1.0
