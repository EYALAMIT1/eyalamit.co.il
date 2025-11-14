# הוראות Push ל-GitHub

## לפני ה-Push הראשון

### 1. התקנת Git (אם עדיין לא מותקן)
- הורד מ: https://git-scm.com/download/win
- התקן עם ברירת המחדל (כולל Git Bash)
- ודא ש-Git נוסף ל-PATH

### 2. הגדרת Git (פעם אחת)
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### 3. יצירת Repository ב-GitHub
- היכנס ל-GitHub ויצור repository חדש (או השתמש בקיים)
- אל תאתחל עם README אם יש לך כבר קבצים מקומיים

### 4. אתחול Git בפרויקט
```bash
cd "C:\Users\USER\Pictures\5848~1\new website AI nov 2025"
git init
git add .
git commit -m "Initial commit: WordPress site with Docker setup"
```

### 5. הוספת Remote ו-Push
```bash
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
git branch -M main
git push -u origin main
```

## הערות חשובות
- קובץ `.gitignore` כבר נוצר ומגן על קבצים רגישים
- הקבצים `.env`, `*.sql`, `uploads/`, `cache/` לא יועלו
- אם יש לך repository קיים, ודא שאתה לא דורס נתונים חשובים




