# הוראות להעלאת תיעוד הודעת קוקיז ל-GitHub

## קבצים שנוצרו

1. `ADD-COOKIE-NOTICE.bat` - סקריפט התקנה
2. `add-cookie-script.php` - סקריפט PHP להוספת הקוד
3. `COOKIE-CONSENT-DOCUMENTATION.md` - תיעוד מלא
4. `COOKIE-NOTICE-INSTALLED.md` - תיעוד התקנה
5. `SUCCESS-COOKIE-NOTICE.md` - תיעוד הצלחה
6. `GIT-COMMIT-COOKIE-DOCS.bat` - סקריפט להוספה ל-git

## הוראות להעלאה ל-GitHub

### שלב 1: הפעל את הסקריפט

פתח Command Prompt (CMD) בתיקיית הפרויקט והרץ:

```batch
GIT-COMMIT-COOKIE-DOCS.bat
```

או פשוט לחץ כפול על הקובץ `GIT-COMMIT-COOKIE-DOCS.bat`.

### שלב 2: צור repository ב-GitHub

1. לך ל-GitHub ויצור repository חדש
2. העתק את ה-URL של ה-repository (לדוגמה: `https://github.com/username/repo-name.git`)

### שלב 3: הוסף remote repository

פתח Command Prompt בתיקיית הפרויקט והרץ:

```batch
cd /d "C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025 take 2"
git remote add origin [URL של ה-repository שלך]
```

### שלב 4: העלה ל-GitHub

```batch
git push -u origin main
```

או אם ה-branch נקרא `master`:

```batch
git push -u origin master
```

## בדיקה

לאחר ההעלאה, בדוק ב-GitHub שהקבצים הבאים קיימים:

- ✅ `ADD-COOKIE-NOTICE.bat`
- ✅ `add-cookie-script.php`
- ✅ `COOKIE-CONSENT-DOCUMENTATION.md`
- ✅ `COOKIE-NOTICE-INSTALLED.md`
- ✅ `SUCCESS-COOKIE-NOTICE.md`

## הערות

- אם יש כבר git repository בתיקייה, הסקריפט יוסיף את הקבצים ל-commit קיים
- אם אין git repository, הסקריפט ייצור אחד חדש
- הסקריפט מגדיר אוטומטית את זהות המשתמש ל-git

