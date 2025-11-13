---
title: "סביבת פיתוח מקומית – הנחיות"
description: "רשימת התקנות, הגדרות וצעדים לשחזור אתר WordPress בסביבת Docker מקומית."
last_updated: "2025-11-13"
---

## סקירה

מסמך זה מרכז את ההמלצות להקמת סביבת עבודה מקומית עבור `eyalamit.co.il`, תוך שמירה על משאבים נמוכים במחשב העבודה הראשי.  
האתר רץ ב־WordPress ומגובה ב־Docker, לכן ההנחיות מתמקדות בתשתיות אלו.

## משאבי מערכת

- **זיכרון:** להגביל את Docker ל־6GB מקסימום (מתוך 32GB זמינים) כדי להשאיר מרווח ליישומים אחרים.
- **מעבד:** להקצות עד 2 ליבות (מתוך 4 ליבות לוגיות) עבור הקונטיינרים.
- **אחסון:** לפנות מראש ~20GB עבור דיסקים של Docker ולוגים. קיימים ~109GB פנויים בכונן `C:`.
- **גיבויים:** לשמור את קובצי הגיבוי (`.zip`, `.sql`) מחוץ לספריית הפרויקט, ולהתייחס אליהם כאל read-only.

## תשתית מערכת הפעלה

1. להריץ חלון PowerShell כמנהל מערכת (`Run as administrator`).
2. להפעיל WSL2 ושכבות וירטואליזציה:
   ```powershell
   wsl --install
   # במידה ומותקן, לאשר שהמאפיינים הבאים פעילים:
   dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
   dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart
   ```
3. להגדיר הפצה ברירת מחדל (למשל Ubuntu 22.04) ולהשלים התקנה עם יצירת משתמש.
4. להפעיל מחדש את המחשב לאחר הפעלת המאפיינים.

## תוכנות וכלים מומלצים

| כלי | מטרה | הערות התקנה |
| --- | --- | --- |
| Git for Windows | ניהול גרסאות | להפעיל Credential Manager, להוסיף ל־PATH |
| Docker Desktop | הרצת קונטיינרים | להשתמש ב־WSL2 backend, להגביל משאבים בתפריט Settings |
| PHP (8.2+) | כלי CLI משלימים | התקנה דרך Scoop/Chocolatey או PHP פורטבילי |
| Composer | ניהול חבילות PHP | להוריד `composer.phar`, לשמור ב־`C:\tools` ולהגדיר PATH |
| WP-CLI | פקודות WordPress | קובץ `wp-cli.phar` + קיצור `wp.bat` ב־PATH |
| Node.js + npm (LTS) | בניית תבניות/נכסים | להתקין רק אם ערכת העיצוב דורשת בנייה |
| Visual Studio Code / PhpStorm | עריכת קוד | להתאים תוספים ל־PHP ו־WordPress לפי הצורך |

> **הערה:** ניתן להשתמש ב־[Scoop](https://scoop.sh/) להתקנות קלילות לכלי CLI מבלי להעמיס על המערכת.

## מבנה ספריות

```
new website AI nov 2025/
├─ docs/                  ← תיעוד סביבת הפיתוח (מסמך זה ואחרים)
├─ eyalamit.co.il_bm.../  ← קבצי WordPress + wp-content מהגיבוי
└─ eyalamit.co.il_bm...zip← גיבוי מקורי (לשימור, לא לעריכה)
```

## קובצי תצורה

- ליצור קובץ `.env` (מבוסס על `env.example`) עם משתני סביבה:
  - פרטי בסיס נתונים (`DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`)
  - Salts ו־Keys ל־WordPress (`AUTH_KEY` וכו') – ניתן לייצר מחדש עם WP-CLI.
  - כתובת האתר המקומית (`WP_HOME`, `WP_SITEURL`).
- לעדכן `wp-config.php` כך שיקרא משתנים מהסביבה במקום ערכים קשיחים.
- להוסיף `.env`, `*.sql`, `wp-content/cache/`, `vendor/` לרשימת `.gitignore`.

## Docker Compose (טיוטה)

```yaml
version: "3.9"

services:
  db:
    image: mariadb:10.6
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5

  wordpress:
    image: wordpress:php8.2-fpm
    depends_on:
      db:
        condition: service_healthy
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_NAME: ${DB_NAME}
      WORDPRESS_DB_USER: ${DB_USER}
      WORDPRESS_DB_PASSWORD: ${DB_PASSWORD}
    volumes:
      - ./eyalamit.co.il_bm1763033821dm:/var/www/html

  nginx:
    image: nginx:1.27-alpine
    depends_on:
      - wordpress
    ports:
      - "8080:80"
    volumes:
      - ./eyalamit.co.il_bm1763033821dm:/var/www/html
      - ./docs/nginx.conf:/etc/nginx/conf.d/default.conf:ro

volumes:
  db_data:
```

> **להמשך:** להוסיף שירות phpMyAdmin במידת הצורך, ולוודא שהגדרות nginx בקובץ נפרד.

## שחזור הגיבוי

1. לפרוס את קובץ ה־ZIP (כבר בוצע) לספריית הפרויקט.
2. לאתר קובץ דאמפ (`.sql`). אם אינו קיים, לבקש מגיבוי השרת יצוא עדכני.
3. להריץ:
   ```powershell
   docker compose up -d
   docker compose exec wordpress wp option update siteurl http://local.eyalamit.test
   docker compose exec wordpress wp option update home http://local.eyalamit.test
   ```
4. לייבא בסיס נתונים:
   ```powershell
   docker compose exec -T db mysql -u ${DB_USER} -p${DB_PASSWORD} ${DB_NAME} < backup.sql
   ```
5. לנקות מטמון (`wp rocket`, `cache`, `transient`) באמצעות WP-CLI.

## תחזוקה שוטפת

- להריץ `docker compose down` בסיום יום עבודה כדי לשחרר משאבים.
- אחת לשבוע: `docker system prune --volumes` (בזהירות) כדי למחוק שכבות לא בשימוש.
- לעקוב אחר `error.log` של nginx ו־`debug.log` של WordPress אם נדרש.
- לתעד שינויים בקובץ זה או במסמכים קשורים ולהעלות ל־git.

## משימות המשך

- יצירת קובץ `.env.example` ו־`.gitignore` מעודכן.
- כתיבת קובץ `docs/nginx.conf` (אם נדרש) ורענון קובצי תצורה נוספים.
- בדיקת תאימות תוספים לערכת הפיתוח המקומית.




