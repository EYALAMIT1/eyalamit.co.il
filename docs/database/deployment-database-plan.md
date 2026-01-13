# 📊 תוכנית פריסה - בסיס נתונים
**תאריך:** 2026-01-14
**סטטוס:** 🟡 READY_FOR_DEPLOYMENT

---

## 📋 סקירה כללית

תוכנית פריסה של בסיס הנתונים מ-localhost לסביבות staging ו-production באמצעות ממשק uPress.

### 🎯 מטרות:
- העברת בסיס נתונים מלא ותקין
- שמירה על Serialization-Aware operations
- עדכון URLs לפי סביבה
- גיבוי מלא לפני כל פעולה

---

## 📊 נתוני בסיס הנתונים הנוכחי

### סטטיסטיקות:
- **גודל:** ~44MB
- **טבלאות:** 71 טבלאות
- **רשומות:**
  - `wp_posts`: 4,415
  - `wp_postmeta`: 21,288
  - `wp_options`: 436
  - `wp_users`: 24

### הגדרות נוכחיות:
- **siteurl:** `https://www.eyalamit.co.il`
- **home:** `https://www.eyalamit.co.il`

---

## 🚀 תוכנית הפריסה

### שלב 1: הכנה מקומית ✅

#### 1.1 ייצוא בסיס נתונים
```bash
# כבר בוצע:
mysqldump -u eyalamit_user -puser_password eyalamit_db --no-tablespaces > database_export_pre_deployment_20260114_002527.sql
```

**✅ קובץ נוצר:** `backups/database_export_pre_deployment_20260114_002527.sql`
**✅ גודל:** 44,647,608 bytes (~44MB)
**✅ תקינות:** מאומתת

#### 1.2 גיבוי נוסף לפני פריסה
```bash
cp backups/database_export_pre_deployment_20260114_002527.sql backups/database_final_backup_before_deployment_$(date +%Y%m%d_%H%M%S).sql
```

---

### שלב 2: פריסה לסביבת בדיקות (Staging)

#### 2.1 יצירת בסיס נתונים חדש בסביבת בדיקות
**⚠️ סביבת הבדיקות היא חדשה וריקה - צריך ליצור בסיס נתונים**

- **דרך ממשק uPress:**
  1. התחבר ל-cPanel של uPress
  2. עבור ל-**"MySQL Databases"**
  3. ב-**"Create New Database"**:
     - **Database Name:** `sb0228693_stagin`
     - לחץ **"Create Database"**
  4. צור משתמש חדש או השתמש בקיים
  5. התחבר ל-**phpMyAdmin**

#### 2.2 ייבוא בסיס נתונים לסביבת בדיקות
- **דרך phpMyAdmin:**
  1. בחר את בסיס הנתונים החדש: `sb0228693_stagin`
  2. לחץ על **"Import"** (יבוא)
  3. בחר את הקובץ: `database_export_pre_deployment_20260114_002527.sql`
  4. הגדר **Format:** SQL
  5. לחץ **"Go"** (התחל)

#### 2.2 עדכון URLs לסביבת בדיקות
**השתמש בסקריפט המוכן:** `scripts/update-database-urls-staging.sql`

**אפשרויות הרצה:**

**אפשרות A - דרך phpMyAdmin:**
1. פתח phpMyAdmin בסביבת uPress
2. בחר את בסיס הנתונים `sb0228693_stagin`
3. לחץ על **"SQL"**
4. העתק והדבק את תוכן הקובץ `scripts/update-database-urls-staging.sql`
5. לחץ **"Go"**

**אפשרות B - דרך קובץ:**
1. העלה את הקובץ `scripts/update-database-urls-staging.sql` לשרת
2. הרץ ב-phpMyAdmin: `SOURCE /path/to/update-database-urls-staging.sql;`

#### 2.3 אימות בסביבת בדיקות
- התחבר לאתר: `http://eyalamit-co-il-2026.s887.upress.link`
- בדוק שהכל נטען תקין
- בדוק שהתפריטים עובדים

---

### שלב 3: פריסה לייצור (Production)

#### 3.1 גיבוי ייצור נוכחי
**⚠️ חובה! לפני כל שינוי בייצור:**
- גיבוי מלא של בסיס הנתונים הנוכחי
- גיבוי של כל הקבצים

#### 3.2 ייבוא בסיס נתונים לייצור
- **דרך ממשק uPress:**
  1. התחבר ל-cPanel של uPress
  2. פתח phpMyAdmin
  3. גבה את בסיס הנתונים הנוכחי
  4. יבא את הקובץ: `database_export_pre_deployment_20260114_002527.sql`

#### 3.3 אימות URLs בייצור
```sql
-- וודא שה-URLs נכונים לייצור:
SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home');
-- צריך להחזיר: https://eyalamit.co.il
```

#### 3.4 אימות בייצור
- התחבר לאתר: `https://eyalamit.co.il`
- בדוק שהכל נטען תקין
- בדוק שהתפריטים עובדים
- בדוק שהטפסים עובדים

---

## 🔧 כלי עזר

### בדיקות בסיס נתונים:
```sql
-- בדיקת מספר רשומות:
SELECT COUNT(*) FROM wp_posts;
SELECT COUNT(*) FROM wp_postmeta;

-- בדיקת URLs:
SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home');
```

### גיבויים חשובים:
- `backups/database_export_pre_deployment_20260114_002527.sql` - גיבוי לפני פריסה
- גיבוי uPress לפני כל שינוי בייצור

---

## ⚠️ נקודות קריטיות

1. **גיבוי תמיד קודם!** - אל תעשה שום שינוי בלי גיבוי
2. **Serialization-Aware** - השתמש ב-REPLACE ולא ב-wp search-replace לפני היבוא
3. **בדוק אחרי כל שלב** - אל תמשיך לשלב הבא לפני אימות
4. **יש תוכנית חזרה** - אם משהו משתבש, יש גיבוי לחזור אליו

---

## ✅ קריטריון הצלחה

### בסביבת בדיקות:
- [ ] בסיס נתונים נטען בהצלחה
- [ ] URLs מעודכנים נכון
- [ ] האתר נטען תקין
- [ ] אין שגיאות קריטיות

### בסביבת ייצור:
- [ ] גיבוי ייצור בוצע
- [ ] בסיס נתונים נטען בהצלחה
- [ ] URLs נכונים לייצור
- [ ] האתר נטען תקין
- [ ] כל הפונקציונליות עובדת

---

## 📝 הערות

- סביבת הבדיקות היא חדשה - אין בה בסיס נתונים קיים
- סביבת הייצור כבר קיימת - חובה גיבוי לפני כל שינוי
- השתמש בממשק uPress לכל הפעולות על בסיס הנתונים

---

**נוצר על ידי:** צוות 3 (Gatekeeper)
**תאריך:** 2026-01-14
**סטטוס:** 🟡 READY_FOR_DEPLOYMENT