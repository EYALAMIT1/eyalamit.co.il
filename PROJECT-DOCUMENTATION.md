# תיעוד פרויקט - אתר WordPress של אייל

## מידע כללי

- **שם הפרויקט:** אתר WordPress - eyalamit.co.il
- **לקוח:** אייל
- **תיקיית עבודה:** `C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025`
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
1. **MariaDB 10.6** - בסיס הנתונים
   - פורט פנימי: 3306
   - שם קונטיינר: `newwebsiteainov2025-db-1`
2. **WordPress PHP 7.4-FPM** - שרת האפליקציה
   - פורט פנימי: 9000
   - שם קונטיינר: `newwebsiteainov2025-wordpress-1`
3. **Nginx 1.27-alpine** - שרת ה-WEB
   - פורט חיצוני: 8080 → פנימי: 80
   - שם קונטיינר: `newwebsiteainov2025-nginx-1`
4. **phpMyAdmin 5** - ממשק ניהול בסיס נתונים
   - פורט חיצוני: 8081 → פנימי: 80
   - שם קונטיינר: `newwebsiteainov2025-phpmyadmin-1`

---

## מבנה התיקיות

```
new website AI nov 2025/
├── eyalamit.co.il_bm1763033821dm/    # תיקיית האתר WordPress
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
cd "C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025"
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

1. **סקריפט:** `backup-site.bat` (קורא ל-`backup-site-simple-fixed.ps1`)
2. **גיבוי DB:** `docker exec` + `mysqldump` → `database_backup.sql`
3. **גיבוי קבצים:** `robocopy` → תיקייה זמנית → `.NET Compression` → `files_backup.zip`
4. **תוצאה:** שני קבצים בתיקיית `backups\backup_YYYY-MM-DD_HH-MM-SS\`

**זה הפתרון שעובד! ✅**

---

### פתרון הגיבוי הסופי (שעובד!)

**הפתרון הסופי כולל שני סוגי גיבויים:**
1. **גיבוי בסיס נתונים (SQL)** - גיבוי מלא של כל הנתונים
2. **גיבוי קבצים (ZIP)** - גיבוי של כל קבצי האתר

### איך זה עובד?

#### הסקריפט הראשי
- **קובץ:** `backup-site.bat`
- **מה הוא עושה:** קורא לסקריפט PowerShell `backup-site-simple-fixed.ps1`

#### הסקריפט PowerShell (`backup-site-simple-fixed.ps1`)

**שלבים:**
1. **בדיקת Docker** - בודק שהקונטיינרים רצים
2. **יצירת תיקיית גיבוי** - `backups\backup_YYYY-MM-DD_HH-MM-SS\`
3. **גיבוי בסיס נתונים:**
   - משתמש ב-`docker exec` להרצת `mysqldump`
   - שומר את הפלט לקובץ `database_backup.sql`
   - גודל טיפוסי: ~60-70 MB
4. **גיבוי קבצים:**
   - משתמש ב-`robocopy` להעתקת קבצים (מטפל טוב יותר בנתיבים ארוכים)
   - מעתיק לתיקייה זמנית `temp_files`
   - יוצר ZIP באמצעות .NET Compression או Compress-Archive
   - גודל טיפוסי: ~1.5-2 GB
5. **יצירת קובץ מידע** - `backup_info.txt` עם פרטי הגיבוי

### גיבוי אוטומטי (מומלץ)

#### דרך 1: סקריפט Batch (הכי פשוט)
```cmd
backup-site.bat
```

#### דרך 2: PowerShell ישירות
```powershell
powershell -ExecutionPolicy Bypass -File ".\backup-site-simple-fixed.ps1"
```

### מה הגיבוי כולל?
1. **גיבוי בסיס נתונים** - `database_backup.sql` (קובץ SQL)
2. **גיבוי קבצים** - `files_backup.zip` (קובץ ZIP עם כל קבצי האתר)
3. **קובץ מידע** - `backup_info.txt` (פרטי הגיבוי והוראות שחזור)

### מיקום הגיבוי
```
backups\backup_YYYY-MM-DD_HH-MM-SS\
├── database_backup.sql      # גיבוי בסיס הנתונים
├── files_backup.zip         # גיבוי כל קבצי האתר
└── backup_info.txt          # קובץ מידע
```

### פרטים טכניים של הפתרון

#### למה robocopy?
- **מטפל טוב יותר בנתיבים ארוכים** מ-Copy-Item של PowerShell
- **אמין יותר** להעתקת קבצים רבים
- **תמיכה ב-Windows** - כלול ב-Windows

#### למה .NET Compression?
- **מטפל טוב יותר בנתיבים ארוכים** מ-Compress-Archive
- **אמין יותר** ליצירת ZIP גדול
- **Fallback:** אם זה נכשל, מנסה Compress-Archive

#### למה mysqldump דרך Docker?
- **גישה ישירה** לבסיס הנתונים
- **לא צריך PHP או WP-CLI** - עובד ישירות
- **מהיר ואמין**

### הערות חשובות
- **נתיבים ארוכים:** חלק מהקבצים עם נתיבים ארוכים מאוד (>260 תווים) לא יועתקו. זה לא קריטי - רוב הקבצים יועתקו (50,000+ קבצים).
- **גודל הגיבוי:** הגיבוי יכול להיות גדול (כמה GB) - זה נורמלי לאתר WordPress עם הרבה תוספים.
- **זמן גיבוי:** גיבוי קבצים יכול לקחת כמה דקות - זה נורמלי.
- **אזהרות robocopy:** אם תראה אזהרות על קבצים שלא הועתקו - זה בדרך כלל בגלל נתיבים ארוכים, לא קריטי.

---

## העלאה ל-GitHub

### הגדרה ראשונית (בוצע)

הפרויקט כבר מוגדר עם GitHub:
- **Repository:** https://github.com/EYALAMIT1/eyalamit.co.il.git
- **Branch:** main
- **Remote:** origin

### העלאת שינויים

#### ⚡ הפתרון שעובד (מומלץ):

**השתמש בסקריפט: `ADD-AND-PUSH.bat`**

1. פתח את תיקיית הפרויקט ב-Windows Explorer
2. לחץ לחיצה כפולה על: `ADD-AND-PUSH.bat`
3. הסקריפט יעשה הכל אוטומטית

**למה זה עובד?** הסקריפט מוסיף את הקבצים במפורש בשמם המלא, כך שהוא לא דולג על קבצים.

ראה גם: `GIT-COMMIT-PUSH-SOLUTION.md` - תיעוד מפורט של הפתרון שעובד.

#### דרך חלופית (ידנית):

```cmd
cd "C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025"

# הוספת קבצים
git add .

# יצירת commit
git commit -m "תיאור השינויים"

# העלאה ל-GitHub
git push
```

### אם תתבקש להזין credentials:
- **Username:** השם שלך ב-GitHub
- **Password:** Personal Access Token (לא סיסמה!)

**איך ליצור Token:**
1. לך ל: https://github.com/settings/tokens
2. Generate new token (classic)
3. שם: `WordPress Site Backup`
4. בחר הרשאות: `repo` (כל הרשאות)
5. Generate token
6. העתק והשתמש בו כ-Password

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

    המסמך כולל:
    - תהליך העדכון המלא
    - רשימת כל הפלאגינים שעודכנו
    - בעיות שנתקלנו בהן ופתרונות
    - הוראות להמשך

    ### הכנה להעלאה לייצור
    📋 **רשימת בדיקות:** [docs/PRE-PRODUCTION-CHECKLIST.md](docs/PRE-PRODUCTION-CHECKLIST.md)  
    🚀 **תוכנית פעולה:** [docs/PRODUCTION-DEPLOYMENT-PLAN.md](docs/PRODUCTION-DEPLOYMENT-PLAN.md)
    
    ### בדיקות אחרונות
    ✅ **דוח בדיקות אוטומטיות:** [AUTOMATED-CHECKS-REPORT.md](AUTOMATED-CHECKS-REPORT.md)  
    ✅ **ניתוח שגיאות Console:** [CONSOLE-ERRORS-ANALYSIS.md](CONSOLE-ERRORS-ANALYSIS.md)  
    ✅ **מדריך בדיקת שגיאות:** [HOW-TO-CHECK-ERRORS.md](HOW-TO-CHECK-ERRORS.md)  
    ✅ **בדיקה אחרונה לפני ייצור:** [FINAL-PRE-DEPLOYMENT-CHECK.md](FINAL-PRE-DEPLOYMENT-CHECK.md)  
    ✅ **רשימת פלאגינים מפורטת:** [PLUGINS-FULL-LIST.md](PLUGINS-FULL-LIST.md)  
    ✅ **מדריך התחלת העלאה:** [START-DEPLOYMENT-NOW.md](START-DEPLOYMENT-NOW.md)

**לפני העלאת האתר לייצור:**
1. ודא שסיימת את כל הבדיקות המקומיות (ראה רשימת בדיקות)
2. עקוב אחר התוכנית המפורטת להעלאה לייצור
3. תמיד גבה את אתר ייצור לפני העתקה!

---

## קבצים חשובים

### קבצי הגדרה
- **`docker-compose.yml`** - הגדרות כל הקונטיינרים
- **`env.local`** - משתני סביבה מקומיים (לא ב-Git)
- **`wp-config.php`** - הגדרות WordPress (קורא מ-env)

### סקריפטי גיבוי
- **`backup-site.bat`** - סקריפט ראשי לגיבוי (מומלץ)
- **`backup-site-simple-fixed.ps1`** - סקריפט PowerShell לגיבוי
- **`backup-site-simple.bat`** - סקריפט חלופי

### סקריפטי Git
- **`git-push-now.bat`** - העלאה מהירה ל-GitHub
- **`git_push.py`** - סקריפט Python להעלאה

### תיעוד
- **`PROJECT-DOCUMENTATION.md`** - מסמך זה
- **`BACKUP-GUIDE.md`** - מדריך מפורט לגיבוי
- **`STEP-BY-STEP-GITHUB-BACKUP.md`** - מדריך שלב-שלב להעלאה ל-GitHub
- **`docs/local-setup.md`** - הוראות הגדרת סביבה מקומית
- **`docs/smoke-tests.md`** - רשימת בדיקות תקינות
- **`docs/WORDPRESS-UPDATE-2025-11-14.md`** - תיעוד עדכון WordPress (14/11/2025)
- **`docs/check-google-site-kit.md`** - מדריך בדיקת Google Site Kit
- **`docs/HANDLING-LONG-PATHS.md`** - פתרון בעיית נתיבים ארוכים ב-Windows (16/11/2025)

---

## פתרון בעיות נפוצות

### בעיה: "Error establishing a database connection"
**פתרון:**
1. בדוק שהקונטיינרים רצים: `docker ps`
2. אם לא רצים: `docker compose up -d`
3. אם עדיין לא עובד, בדוק את `env.local` והגדרות ה-DB

### בעיה: "Cannot validate since a PHP installation could not be found"
**פתרון:**
זה רק אזהרה מ-VS Code. לא קריטי. אם רוצים לתקן:
1. התקן PHP מקומי (למשל דרך Scoop)
2. הוסף ל-VS Code settings: `"php.validate.executablePath": "C:\\path\\to\\php.exe"`

### בעיה: נתיבים ארוכים בגיבוי
**פתרון:**
1. הפעל תמיכה בנתיבים ארוכים ב-Windows:
   - פתח Registry Editor (`regedit`)
   - לך ל: `HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\FileSystem`
   - צור/עדכן `LongPathsEnabled` = `1`
   - הפעל מחדש
2. או התקן 7-Zip - הסקריפט ישתמש בו אוטומטית

### בעיה: Git לא מזוהה
**פתרון:**
1. התקן Git for Windows: https://git-scm.com/download/win
2. הפעל מחדש את CMD/PowerShell
3. בדוק: `git --version`

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

### עדכון URLs ב-DB (אם צריך)
```cmd
docker compose exec wordpress wp search-replace 'https://www.eyalamit.co.il' 'http://localhost:8080' --all-tables
```

---

## תוספים ותבניות

### תוספים מושבתים
- **WP Rocket** - מושבת זמנית (חסר קובץ רישיון)
  - מיקום: `wp-content/plugins/wp-rocket.disabled`

### תבנית
- **Bridge Child** - תבנית ילד
  - מיקום: `wp-content/themes/bridge-child`

---

## מידע טכני

### PHP Version
- **גרסה:** PHP 7.4-FPM
- **סיבה:** תאימות עם WordPress ותוספים ישנים יותר

### בסיס נתונים
- **סוג:** MariaDB 10.6
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

## עדכונים עתידיים

### מה צריך לעשות בעתיד
1. **הפעלת WP Rocket** - לאחר קבלת קובץ רישיון
2. **ניקוי מדיה** - יש סקריפטים ב-`bridge-child` לניקוי קבצים לא בשימוש
3. **עדכון WordPress** - עדכון גרסת WordPress ותוספים
4. **אופטימיזציה** - אופטימיזציה של תמונות וקבצים

### רעיונות נוספים
ראה: `docs/backlog-ideas.md`

---

## קישורים שימושיים

- **GitHub Repository:** https://github.com/EYALAMIT1/eyalamit.co.il
- **אתר ייצור:** https://www.eyalamit.co.il
- **אתר מקומי:** http://localhost:8080
- **phpMyAdmin מקומי:** http://localhost:8081
- **GitHub Tokens:** https://github.com/settings/tokens

---

## היסטוריית שינויים

### נובמבר 2025
- **14/11/2025** - הגדרת סביבת פיתוח מקומית עם Docker
- **14/11/2025** - יצירת סקריפטי גיבוי
- **14/11/2025** - העלאה ראשונית ל-GitHub
- **14/11/2025** - יצירת תיעוד מקיף

---

## הערות חשובות

1. **קובץ `env.local`** - לא נשמר ב-Git (רגיש)
2. **תיקיית `backups/`** - לא נשמרת ב-Git (גדולה מדי)
3. **תיקיית `uploads/`** - לא נשמרת ב-Git (גדולה מדי)
4. **WP Rocket** - מושבת זמנית עד קבלת רישיון
5. **נתיבים ארוכים** - חלק מהקבצים לא יועתקו בגיבוי (לא קריטי)

---

## תמיכה ועזרה

אם יש בעיות:
1. בדוק את המסמכים ב-`docs/`
2. בדוק את `BACKUP-GUIDE.md` לבעיות גיבוי
3. בדוק את `STEP-BY-STEP-GITHUB-BACKUP.md` לבעיות Git

---

**תאריך עדכון אחרון:** 14 בנובמבר 2025

