# תיעוד פרויקט - אתר WordPress של אייל

## מידע כללי

- **שם הפרויקט:** אתר WordPress - eyalamit.co.il
- **לקוח:** אייל
- **תיקיית עבודה:** `C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025 take 2`
- **Repository GitHub:** https://github.com/EYALAMIT1/eyalamit.co.il.git
- **תאריך יצירה:** נובמבר 2025

---

## תשתית וכלים

### כלים מותקנים
- **Docker Desktop** - להרצת סביבת הפיתוח
- **Git** - גרסה 2.51.2.windows.1
- **PowerShell** - להרצת סקריפטים

### Docker Containers
הפרויקט משתמש ב-4 קונטיינרים:
1. **MariaDB 10.11** - בסיס הנתונים
   - פורט פנימי: 3306
   - שם קונטיינר: `newwebsiteainov2025take2-db-1`
2. **WordPress PHP 8.2-FPM** - שרת האפליקציה
   - פורט פנימי: 9000
   - שם קונטיינר: `newwebsiteainov2025take2-wordpress-1`
3. **Nginx 1.27-alpine** - שרת ה-WEB
   - פורט חיצוני: 8080 → פנימי: 80
   - שם קונטיינר: `newwebsiteainov2025take2-nginx-1`
4. **phpMyAdmin 5.2** - ממשק ניהול בסיס נתונים
   - פורט חיצוני: 8081 → פנימי: 80
   - שם קונטיינר: `newwebsiteainov2025take2-phpmyadmin-1`

---

## מבנה התיקיות

```
new website AI nov 2025 take 2/
├── eyalamit.co.il_bm1763848352dm/    # תיקיית האתר WordPress
│   ├── wp-content/                    # תוכן האתר (תבניות, תוספים, מדיה)
│   ├── wp-config.php                 # קובץ הגדרות WordPress
│   └── ...
├── docs/                              # תיעוד נוסף
│   ├── local-setup.md                # הוראות הגדרת סביבה מקומית
│   ├── smoke-tests.md                # רשימת בדיקות תקינות
│   ├── backlog-ideas.md              # רעיונות לעתיד
│   ├── WORDPRESS-UPDATE-2025-11-14.md # תיעוד עדכון WordPress
│   ├── check-google-site-kit.md      # מדריך בדיקת Google Site Kit
│   ├── HANDLING-LONG-PATHS.md        # פתרון בעיית נתיבים ארוכים ב-Windows
│   └── nginx.conf                    # הגדרות Nginx
├── backups/                           # תיקיית גיבויים מקומיים
│   └── backup_YYYY-MM-DD_HH-MM-SS/   # תיקיות גיבוי עם תאריך ושעה
├── docker-compose.yml                 # הגדרות Docker Compose
├── env.example                        # דוגמה לקובץ משתני סביבה
├── env.local                          # משתני סביבה מקומיים (לא ב-Git)
├── .gitignore                         # קבצים להתעלמות ב-Git
└── [סקריפטים שונים]                  # סקריפטי גיבוי והעלאה
```

---

## משתני סביבה

### קובץ `env.local` (לא ב-Git)
```
DB_NAME=eyal_local
DB_USER=eyal
DB_PASSWORD=eyalpass
DB_ROOT_PASSWORD=strong_root_pass
DB_HOST=db
WP_HOME=http://localhost:8080
WP_SITEURL=http://localhost:8080
```

**חשוב:** קובץ זה לא נשמר ב-Git בגלל `.gitignore` - הוא מכיל מידע רגיש.

---

## הוראות הפעלה

### 1. הפעלת האתר המקומי

#### שלב 1: בדיקה שהכל מוכן
```cmd
cd "C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025 take 2"
docker ps
```

#### שלב 2: הפעלת הקונטיינרים
```cmd
docker compose up -d
```

#### שלב 3: בדיקה שהכל רץ
```cmd
docker ps
```
צריך לראות 4 קונטיינרים במצב "Up".

#### שלב 4: פתיחת האתר בדפדפן
- **אתר:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

### 2. עצירת האתר
```cmd
docker compose down
```

### 3. עצירה ומחיקת כל הנתונים
```cmd
docker compose down -v
```
**אזהרה:** זה ימחק את בסיס הנתונים!

---

## גיבוי

### ⚡ זיכרון מהיר - הפתרון הסופי

**בפעם הבאה שצריך לעשות גיבוי, הפתרון שעובד הוא:**

1. **סקריפט:** `backup-site.bat` (קורא ל-`backup-site.ps1`)
2. **גיבוי DB:** `docker exec` + `mysqldump` → `database_backup.sql`
3. **גיבוי קבצים:** `robocopy` → תיקייה זמנית → `.NET Compression` → `files_backup.zip`
4. **תוצאה:** שני קבצים בתיקיית `backups\backup_YYYY-MM-DD_HH-MM-SS\`

**זה הפתרון שעובד! ✅**

---

## העלאה ל-GitHub

### הגדרה ראשונית (בוצע)

הפרויקט כבר מוגדר עם GitHub:
- **Repository:** https://github.com/EYALAMIT1/eyalamit.co.il.git
- **Branch:** main
- **Remote:** origin

### העלאת שינויים

#### ⚡ הפתרון שעובד (מומלץ):

**השתמש בסקריפט: `GIT-COMMIT-AND-PUSH.bat`**

1. פתח את תיקיית הפרויקט ב-Windows Explorer
2. לחץ לחיצה כפולה על: `GIT-COMMIT-AND-PUSH.bat`
3. הסקריפט יעשה הכל אוטומטית

---

## עדכון WordPress - 14 בנובמבר 2025

### סקירה כללית
בוצע עדכון מלא של האתר מ-WordPress 5.2.2 (גרסה ישנה מאוד) ל-WordPress 6.8.3 (גרסה עדכנית).

### תוצאות
- ✅ **WordPress:** עודכן מ-5.2.2 ל-6.8.3
- ✅ **Google Site Kit:** עודכן מ-1.43.0 ל-1.165.0
- ✅ **Yoast SEO:** עודכן מ-11.4 ל-26.3
- ✅ **WooCommerce:** עודכן מ-3.6.4 ל-10.3.5
- ✅ **12 פלאגינים נוספים** עודכנו

### תיעוד מפורט
📄 **ראה:** [docs/WORDPRESS-UPDATE-2025-11-14.md](docs/WORDPRESS-UPDATE-2025-11-14.md)

---

## קבצים חשובים

### קבצי הגדרה
- **`docker-compose.yml`** - הגדרות כל הקונטיינרים
- **`env.local`** - משתני סביבה מקומיים (לא ב-Git)
- **`wp-config.php`** - הגדרות WordPress (קורא מ-env)

### סקריפטי גיבוי
- **`backup-site.bat`** - סקריפט ראשי לגיבוי (מומלץ)
- **`backup-site.ps1`** - סקריפט PowerShell לגיבוי

### סקריפטי Git
- **`GIT-COMMIT-AND-PUSH.bat`** - העלאה מהירה ל-GitHub

### תיעוד
- **`PROJECT-DOCUMENTATION.md`** - מסמך זה
- **`BACKUP-GUIDE.md`** - מדריך מפורט לגיבוי
- **`STEP-BY-STEP-GITHUB-BACKUP.md`** - מדריך שלב-שלב להעלאה ל-GitHub
- **`docs/local-setup.md`** - הוראות הגדרת סביבה מקומית
- **`docs/smoke-tests.md`** - רשימת בדיקות תקינות
- **`docs/WORDPRESS-UPDATE-2025-11-14.md`** - תיעוד עדכון WordPress (14/11/2025)
- **`docs/check-google-site-kit.md`** - מדריך בדיקת Google Site Kit
- **`docs/HANDLING-LONG-PATHS.md`** - פתרון בעיית נתיבים ארוכים ב-Windows

---

## פתרון בעיות נפוצות

### בעיה: "Error establishing a database connection"
**פתרון:**
1. בדוק שהקונטיינרים רצים: `docker ps`
2. אם לא רצים: `docker compose up -d`
3. אם עדיין לא עובד, בדוק את `env.local` והגדרות ה-DB

### בעיה: Docker לא רץ
**פתרון:**
1. פתח Docker Desktop
2. בדוק שהכל מופעל
3. אם יש שגיאות, בדוק את הגדרות WSL2 ו-Hyper-V

---

## הגדרות WordPress

### URLs מקומיים
- **Home URL:** http://localhost:8080
- **Site URL:** http://localhost:8080

### עדכון URLs (אם צריך)
```cmd
docker compose exec wordpress wp option update home http://localhost:8080
docker compose exec wordpress wp option update siteurl http://localhost:8080
```

---

## מידע טכני

### PHP Version
- **גרסה:** PHP 8.2-FPM
- **סיבה:** תאימות עם WordPress ותוספים עדכניים

### בסיס נתונים
- **סוג:** MariaDB 10.11
- **שם DB:** eyal_local
- **משתמש:** eyal
- **Volume:** `db_data` (נשמר גם אחרי `docker compose down`)

### Nginx
- **גרסה:** 1.27-alpine
- **קובץ הגדרות:** `docs/nginx.conf`

---

## הוראות שחזור מגיבוי

### שחזור בסיס נתונים
```cmd
docker compose exec -T db mysql -ueyal -peyalpass eyal_local < backups\backup_YYYY-MM-DD_HH-MM-SS\database_backup.sql
```

### שחזור קבצים
1. חלץ את `files_backup.zip` לתיקיית האתר
2. או העתק ידנית את הקבצים

---

## קישורים שימושיים

- **GitHub Repository:** https://github.com/EYALAMIT1/eyalamit.co.il
- **אתר ייצור:** https://www.eyalamit.co.il
- **אתר מקומי:** http://localhost:8080
- **phpMyAdmin מקומי:** http://localhost:8081
- **GitHub Tokens:** https://github.com/settings/tokens

---

## הערות חשובות

1. **קובץ `env.local`** - לא נשמר ב-Git (רגיש)
2. **תיקיית `backups/`** - לא נשמרת ב-Git (גדולה מדי)
3. **תיקיית `uploads/`** - לא נשמרת ב-Git (גדולה מדי)

---

**תאריך עדכון אחרון:** 25 בנובמבר 2025

