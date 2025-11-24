# הוראות התקנת Python

אם Python לא מותקן במחשב שלך, יש לך כמה אפשרויות:

## אפשרות 1: Microsoft Store (הכי קל)

1. לחץ על כפתור Start
2. חפש "Microsoft Store"
3. חפש "Python 3.12" או "Python 3.11"
4. לחץ על "התקן"
5. המתן לסיום ההתקנה
6. פתח מחדש את CMD/PowerShell

## אפשרות 2: אתר Python הרשמי

1. לך ל- https://www.python.org/downloads/
2. לחץ על "Download Python 3.12.x" (הגרסה האחרונה)
3. הרץ את הקובץ שהורד
4. **חשוב מאוד**: בעת ההתקנה, סמן ✓ "Add Python to PATH"
5. לחץ על "Install Now"
6. המתן לסיום ההתקנה
7. פתח מחדש את CMD/PowerShell

## אפשרות 3: דרך winget (Windows Package Manager)

אם יש לך winget מותקן, פתח PowerShell כמנהל והרץ:

```powershell
winget install Python.Python.3.12
```

## אפשרות 4: הרצה דרך Docker

אם יש לך Docker Desktop מותקן, תוכל להריץ את הבדיקות דרך Docker:

```bash
RUN-COMPREHENSIVE-CHECKS-DOCKER.bat
```

## איך לבדוק שה-Python מותקן?

פתח CMD או PowerShell והרץ:

```bash
python --version
```

אם אתה רואה משהו כמו `Python 3.12.x`, Python מותקן!

## התקנת החבילה requests

אחרי התקנת Python, התקן את החבילה requests:

```bash
python -m pip install requests
```

או:

```bash
pip install requests
```

## בעיות נפוצות

### "Python לא מזוהה כפקודה"

- פתח מחדש את CMD/PowerShell לאחר ההתקנה
- ודא שסימנת "Add Python to PATH" בהתקנה
- נסה `python3` במקום `python`

### "pip לא מזוהה"

- נסה: `python -m pip install requests`
- או: `python3 -m pip install requests`

## עזרה נוספת

אם אתה נתקל בבעיות, נסה:
1. לפתוח מחדש את CMD/PowerShell
2. להריץ את הפקודה שוב
3. לבדוק אם Python מותקן: `where python`

