# מדריך שלב-שלב: העלאת האתר ל-GitHub

## מטרה
ליצור גיבוי מלא של האתר ולהעלות אותו ל-GitHub.

---

## שלב 1: בדיקה שהכל מוכן

### 1.1 בדוק ש-Docker רץ
1. פתח **Command Prompt** (CMD) או **PowerShell**
   - לחץ `Windows + R`
   - הקלד `cmd` או `powershell`
   - לחץ Enter

2. בדוק שהקונטיינרים רצים:
   ```
   docker ps
   ```
   
   **מה לחפש:** אתה צריך לראות לפחות 2 קונטיינרים:
   - אחד עם "db" בשם (בסיס הנתונים)
   - אחד עם "wordpress" או "nginx" בשם
   
   **אם אין קונטיינרים:**
   ```
   cd "C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025 take 2"
   docker compose up -d
   ```

### 1.2 בדוק ש-Git מותקן
באותו חלון CMD/PowerShell, הקלד:
```
git --version
```

**אם אתה רואה משהו כמו `git version 2.x.x`** - מעולה! המשך לשלב 2.

**אם אתה רואה שגיאה** - צריך להתקין Git:
1. לך ל: https://git-scm.com/download/win
2. הורד והתקן (הכל ברירת מחדל)
3. הפעל מחדש את CMD/PowerShell
4. נסה שוב `git --version`

---

## שלב 2: נווט לתיקיית הפרויקט

באותו חלון CMD/PowerShell, הקלד:

```
cd "C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025 take 2"
```

**או דרך Windows Explorer:**
1. פתח את התיקייה בכפול-קליק
2. לחץ על שורת הכתובת (Address Bar) למעלה
3. הקלד `cmd` ולחץ Enter
4. זה יפתח CMD כבר בתיקייה הנכונה

---

## שלב 3: אתחל Git Repository

באותו חלון, הקלד את הפקודות הבאות **אחת אחרי השנייה**:

```
git init
```

**מה זה עושה:** יוצר תיקיית `.git` (תראה אותה אם תפעיל "הצג קבצים מוסתרים")

---

## שלב 4: הוסף את GitHub Repository

הקלד:

```
git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
```

**אם אתה מקבל שגיאה "remote origin already exists":**
```
git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git
```

---

## שלב 5: הוסף קבצים ל-Git

הקלד:

```
git add .
```

**מה זה עושה:** מוסיף את כל הקבצים (חוץ מאלה ב-`.gitignore`) ל-staging

**זה יכול לקחת כמה דקות** - תראה הרבה שמות קבצים עוברים.

---

## שלב 6: צור Commit (שמירה מקומית)

הקלד:

```
git commit -m "Initial commit: WordPress site with Docker setup"
```

**מה זה עושה:** שומר את כל הקבצים ב-Git repository המקומי שלך

**זה יכול לקחת כמה דקות** - Git מעבד את כל הקבצים.

---

## שלב 7: הגדר את ה-Branch

הקלד:

```
git branch -M main
```

**מה זה עושה:** קורא לענף הראשי "main" (זה הסטנדרט החדש)

---

## שלב 8: העלה ל-GitHub

הקלד:

```
git push -u origin main
```

**כאן תתבקש להזין credentials:**

### אופציה A: Personal Access Token (מומלץ)

1. **לך ל-GitHub:**
   - פתח דפדפן
   - לך ל: https://github.com/settings/tokens
   - התחבר אם צריך

2. **צור Token חדש:**
   - לחץ על "Generate new token" → "Generate new token (classic)"
   - תן שם: `WordPress Site Backup`
   - בחר הרשאות: סמן את `repo` (כל הרשאות ה-repository)
   - לחץ "Generate token" בתחתית
   - **העתק את ה-Token מיד!** (לא תראה אותו שוב)

3. **הזן ב-CMD:**
   - כשמבקשים Username: הזן את שם המשתמש שלך ב-GitHub
   - כשמבקשים Password: **הזן את ה-Token** (לא את הסיסמה!)

### אופציה B: אם יש לך SSH Keys

אם כבר הגדרת SSH keys ל-GitHub, תוכל להשתמש בזה:

```
git remote set-url origin git@github.com:EYALAMIT1/eyalamit.co.il.git
git push -u origin main
```

---

## שלב 9: בדיקה שהכל עבד

1. **לך ל-GitHub בדפדפן:**
   https://github.com/EYALAMIT1/eyalamit.co.il

2. **מה לחפש:**
   - אתה אמור לראות את כל הקבצים שלך
   - תיקיות כמו `docs/`, `eyalamit.co.il_bm1763848352dm/`, וכו'
   - קובץ `docker-compose.yml`
   - קובץ `README.md` (אם יש)

**אם אתה רואה את הקבצים** - הכל עבד! ✅

---

## בעיות נפוצות ופתרונות

### "fatal: not a git repository"
**פתרון:** אתה לא בתיקיית הפרויקט. חזור לשלב 2.

### "remote origin already exists"
**פתרון:** זה בסדר, המשך לשלב 5.

### "nothing to commit"
**פתרון:** זה אומר שכבר יש commit. דלג לשלב 7.

### "Authentication failed"
**פתרון:** 
- ודא שהשתמשת ב-Personal Access Token ולא בסיסמה
- או צור Token חדש

### "Permission denied"
**פתרון:**
- ודא שיש לך גישה ל-repository ב-GitHub
- ודא שהשם `EYALAMIT1` נכון

### הפקודה "תלויה" ולא קורה כלום
**פתרון:**
- זה נורמלי! העלאת קבצים גדולים לוקחת זמן
- תן לזה כמה דקות
- אם זה יותר מ-10 דקות, לחץ `Ctrl+C` ונסה שוב

---

## סיכום - רשימת פקודות מהירה

אם אתה רוצה להריץ הכל בבת אחת (אחרי שבדקת שהכל מוכן):

```cmd
cd "C:\Users\USER\Pictures\סטודיו נשימה מעגלית\new website AI nov 2025 take 2"
git init
git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
git add .
git commit -m "Initial commit: WordPress site with Docker setup"
git branch -M main
git push -u origin main
```

---

## עזרה נוספת

אם נתקעת בשלב כלשהו:
1. העתק את ההודעה המדויקת שקיבלת
2. תגיד לי באיזה שלב אתה
3. אני אעזור לך לפתור את הבעיה

**זכור:** אין שאלות טיפשיות! כל שאלה היא לגיטימית.

