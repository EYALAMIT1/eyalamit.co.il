# מדריך הגדרת Selenium לבדיקות אוטומטיות בדפדפן

**תאריך:** 2026-01-13  
**מטרה:** הגדרת סביבת בדיקות אוטומטיות מלאה עם Selenium + Firefox לבדיקת Console Logs

---

## 📋 סקירה כללית

המערכת תכלול:
- **Selenium Hub** - מרכז ניהול בדיקות
- **Firefox Node** - דפדפן Firefox headless לבדיקות
- **Python Test Script** - סקריפט לבדיקות אוטומטיות ושליפת Console Logs
- **אוטומציה מלאה** - ללא צורך בפעולה ידנית

---

## 🚀 התקנה מהירה

### שלב 1: עדכון docker-compose.yml

הוסף את השירותים הבאים ל-`docker-compose.yml`:

```yaml
  selenium-hub:
    image: selenium/hub:4.15.0
    container_name: eyalamit-selenium-hub
    restart: always
    ports:
      - "4444:4444"
      - "4445:4444"
    networks:
      - default

  firefox-node:
    image: selenium/node-firefox:4.15.0
    container_name: eyalamit-firefox-node
    restart: always
    depends_on:
      - selenium-hub
    environment:
      - HUB_HOST=selenium-hub
      - HUB_PORT=4444
      - NODE_MAX_INSTANCES=1
      - NODE_MAX_SESSION=1
    volumes:
      - /dev/shm:/dev/shm
    networks:
      - default
```

### שלב 2: התקנת תלויות Python

צור קובץ `requirements-testing.txt`:

```txt
selenium==4.15.2
webdriver-manager==4.0.1
```

הרץ:
```bash
pip3 install -r requirements-testing.txt
```

### שלב 3: הפעלת שירותי Selenium

```bash
docker-compose up -d selenium-hub firefox-node
```

אמת שהכל עובד:
```bash
curl http://localhost:4444/wd/hub/status
```

---

## 📝 שימוש בסקריפט הבדיקה

### הרצה בסיסית

```bash
python3 tests/console_verification_test.py
```

### עם אפשרויות

```bash
# בדיקת דף ספציפי
python3 tests/console_verification_test.py --url http://localhost:9090/about

# שמירת לוג לקובץ
python3 tests/console_verification_test.py --output logs/console-log.txt

# בדיקה עם timeout ארוך יותר
python3 tests/console_verification_test.py --timeout 30
```

---

## 🔍 מה הסקריפט בודק

1. **Console Errors** - כל שגיאות JavaScript (Uncaught TypeError, ReferenceError, וכו')
2. **Network Errors** - בקשות שנכשלו (4xx, 5xx)
3. **CORS Errors** - בעיות CORS עם משאבים חיצוניים
4. **jQuery Errors** - שגיאות "jQuery is not defined"
5. **Font Loading** - בעיות טעינת גופנים

---

## 📊 פורמט פלט

הסקריפט מייצר:
- **Console Log טקסטואלי** - פלט מלא של כל הודעות הקונסולה
- **דוח JSON** - מבנה נתונים לניתוח אוטומטי
- **דוח Markdown** - דוח קריא למנהלת

---

## ⚙️ הגדרות מתקדמות

### שינוי דפדפן

לשימוש ב-Chrome במקום Firefox, שנה ב-`tests/console_verification_test.py`:

```python
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
```

### הגדרת Timeout

```python
driver.set_page_load_timeout(30)  # שניות
```

### Headless Mode (ברירת מחדל)

Firefox רץ ב-headless mode (ללא GUI) - מושלם לסביבת Docker.

---

## 🐛 פתרון בעיות

### Selenium Hub לא מגיב

```bash
docker-compose logs selenium-hub
docker-compose restart selenium-hub
```

### Firefox Node לא מתחבר

```bash
docker-compose logs firefox-node
# ודא ש-HUB_HOST=selenium-hub נכון
```

### שגיאת "Connection refused"

```bash
# ודא שהפורט 4444 פנוי
netstat -an | grep 4444
# או
lsof -i :4444
```

---

## 📚 משאבים נוספים

- [Selenium Documentation](https://www.selenium.dev/documentation/)
- [Selenium Docker Images](https://github.com/SeleniumHQ/docker-selenium)
- [WebDriver Python API](https://selenium-python.readthedocs.io/)

---

## ✅ בדיקת תקינות

לאחר ההתקנה, הרץ:

```bash
python3 tests/console_verification_test.py --url http://localhost:9090
```

אם הכל תקין, תקבל:
- ✅ דוח Console Log טקסטואלי
- ✅ רשימת שגיאות (אם יש)
- ✅ סטטוס HTTP של הדף

---

**הערה:** כל הבדיקות רצות אוטומטית ללא צורך בפעולה ידנית!
