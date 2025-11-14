# הוראות התקנה - ניקוי מדיה לא בשימוש

## התקנה

הקבצים כבר נמצאים בתיקיית התבנית:
- `media-cleanup.php` - הסקריפט הראשי
- `wp-cli-media-cleanup.php` - פקודת WP-CLI (אופציונלי)
- `MEDIA-CLEANUP-README.md` - מדריך שימוש מפורט

## הגדרת WP-CLI (אופציונלי)

אם אתה רוצה להשתמש בפקודת WP-CLI, הוסף ל-`wp-config.php` או `functions.php`:

```php
// טעינת פקודת WP-CLI (אופציונלי)
if (defined('WP_CLI') && WP_CLI) {
    require_once(get_stylesheet_directory() . '/wp-cli-media-cleanup.php');
}
```

או הוסף ל-`functions.php`:

```php
// טעינת פקודת WP-CLI
if (defined('WP_CLI') && WP_CLI) {
    require_once(get_stylesheet_directory() . '/wp-cli-media-cleanup.php');
}
```

## שימוש מהיר

### דרך WP-CLI (אם הותקן):

```bash
# ניתוח בלבד
wp media-cleanup analyze

# סימולציה - מחיקת קבצים ללא רשומה
wp media-cleanup cleanup --orphans

# מחיקה בפועל - thumbnails ישנים
wp media-cleanup cleanup --thumbnails --older-than=2020-01-01 --execute
```

### דרך eval-file (ללא התקנה):

```bash
# ניתוח בלבד
wp eval-file wp-content/themes/bridge-child/media-cleanup.php

# סימולציה
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --orphans

# מחיקה בפועל
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --orphans --execute
```

## בדיקה מהירה

לפני שימוש, מומלץ לבדוק שהכל עובד:

```bash
# בדיקה בסיסית
wp eval-file wp-content/themes/bridge-child/media-cleanup.php
```

זה ייצור דוחות ב-`wp-content/uploads/media-cleanup-reports/`

## הערות חשובות

1. **גיבוי לפני שימוש** - חובה!
2. **התחל עם dry run** - תמיד בדוק לפני מחיקה בפועל
3. **מחיקה בשלבים** - אל תמחק הכל בבת אחת

למידע נוסף, ראה `MEDIA-CLEANUP-README.md`





