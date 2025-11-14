# הוראות העלאה ל-GitHub

## בעיה טכנית זמנית
יש בעיה טכנית עם הרצת פקודות Git דרך הטרמינל. הנה שתי דרכים לבצע את העבודה:

## דרך 1: הרצת הסקריפט (מומלץ)

### אופציה A - Batch Script (הכי פשוט):
1. פתח את `git-push-now.bat` (כפול-קליק)
2. הסקריפט יבצע הכל אוטומטית

### אופציה B - PowerShell:
1. פתח PowerShell בתיקיית הפרויקט
2. הרץ:
   ```powershell
   powershell -ExecutionPolicy Bypass -File ".\git-push-now.ps1"
   ```

## דרך 2: ביצוע ידני (אם הסקריפט לא עובד)

פתח **Git Bash** או **PowerShell** בתיקיית הפרויקט והרץ את הפקודות הבאות:

```bash
# 1. אתחל Git repository
git init

# 2. הוסף remote
git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git

# 3. הוסף קבצים
git add .

# 4. Commit
git commit -m "Initial commit: WordPress site with Docker setup and local environment configuration"

# 5. הגדר branch ל-main
git branch -M main

# 6. Push ל-GitHub
git push -u origin main
```

## אם תתבקש להזין credentials:

1. **Personal Access Token (מומלץ)**:
   - לך ל: https://github.com/settings/tokens
   - לחץ על "Generate new token (classic)"
   - תן שם: "WordPress Site Backup"
   - בחר הרשאות: `repo` (כל הרשאות ה-repository)
   - לחץ "Generate token"
   - העתק את ה-token והשתמש בו במקום הסיסמה

2. **או SSH** (אם יש לך מפתחות SSH):
   ```bash
   git remote set-url origin git@github.com:EYALAMIT1/eyalamit.co.il.git
   git push -u origin main
   ```

## בדיקה שהכל עבד:

לך ל: https://github.com/EYALAMIT1/eyalamit.co.il

אם אתה רואה את הקבצים שם - הכל עבד! ✅

## בעיות נפוצות:

### "fatal: not a git repository"
- הרץ `git init` תחילה

### "remote origin already exists"
- הרץ: `git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git`

### "Authentication failed"
- ודא שהשתמשת ב-Personal Access Token ולא בסיסמה
- או הגדר SSH keys

### "nothing to commit"
- זה בסדר! זה אומר שכבר יש commit
- פשוט הרץ: `git push -u origin main`

