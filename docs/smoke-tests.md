---
title: "רשימת בדיקות תקינות לסביבה המקומית"
description: "תסריטי Smoke Test לאחר הרמת סביבת Docker ל-WordPress."
last_updated: "2025-11-13"
---

## הכנה

- ודא שקובץ `.env` מכיל את כל המשתנים הדרושים ושהקונטיינרים מכובים (`docker compose down`).
- וודא שפורט 8080 פנוי (או הפורט שהוגדר ל־nginx).

## 1. הרצת שירותים

1. `docker compose up -d`
2. `docker compose ps` – כל השירותים במצב `Up`.
3. `docker compose logs --tail=50` – אין שגיאות PHP פטאליות או שגיאות התחברות לבסיס הנתונים.

## 2. בדיקות WordPress בסיסיות

1. `docker compose exec wordpress wp core is-installed` → פקודה מחזירה `Success`.
2. `docker compose exec wordpress wp option get siteurl` → מצביע לכתובת המקומית.
3. `docker compose exec wordpress wp plugin list` → כל התוספים החשובים במצב הנכון (פעיל/מושבת).

## 3. בדיקות דפדפן

- גלישה ל־`http://localhost:8080` ובדיקת טעינת עמוד הבית ללא שגיאות PHP.
- כניסה ל־`http://localhost:8080/wp-admin` עם משתמש מקומי; אימות תפריט לוח הבקרה.
- פתיחת עמוד ייצוגי (לדוגמה דף נשימה מעגלית) לבדיקה ויזואלית בסיסית.

## 4. בדיקות מדיה ותוספים

- העלאת קובץ בדיקה למדיה (לוודא הרשאות כתיבה ב־`wp-content/uploads`).
- הרצת מנקה מטמון: `docker compose exec wordpress wp rocket clean --confirm`.
- בדיקת תוסף בולט (לדוגמה Elementor / Advanced Custom Fields) – פתיחת עורך ובדיקת שגיאות קונסול.

## 5. בדיקות בסיס נתונים

- `docker compose exec db mysqladmin ping` → החזרת `mysqld is alive`.
- `docker compose exec wordpress wp db check` → וידוא שלמות הטבלאות.

## 6. בדיקות לוגים

- בחינת `logs/nginx/access.log` ו־`logs/nginx/error.log` (אם הוגדר Bind Mount).
- `docker compose exec wordpress tail -n 50 /var/www/html/wp-content/debug.log` (אם קיים) לחיפוש התראות.

## 7. כיבוי נקי

1. `docker compose down`
2. `docker volume ls` – וידוא שאין נפחים לא בשימוש (אופציונלי: `docker system prune` עם זהירות).

## טיפים לסגירת תקלות

- אם nginx לא עולה: לבדוק שמסלול הקבצים ב־`default.conf` קיים ושרשאות תקינות.
- אם WP-CLI נעצר על `Error establishing a database connection`, לבדוק ערכי DB ב־`.env`.
- אם דפדפן מציג Mixed Content, להריץ `wp search-replace 'https://www.eyalamit.co.il' 'http://localhost:8080'`.




