# מדריך שימוש - ניקוי מדיה לא בשימוש

## סקירה כללית

הסקריפט `media-cleanup.php` מאפשר לזהות ולנקות תמונות ומדיה לא בשימוש ב-WordPress. הסקריפט מזהה:

1. **קבצים ללא רשומה ב-DB (Orphan Files)** - קבצים פיזיים שלא רשומים בבסיס הנתונים
2. **Thumbnails מיותרים** - גרסאות קטנות של תמונות שנוצרו אוטומטית
3. **Attachments לא בשימוש** - תמונות רשומות ב-DB אבל לא מקושרות לתוכן

## אזהרה חשובה!

**לפני כל פעולה - חובה לבצע גיבוי מלא:**
- גיבוי בסיס הנתונים
- גיבוי תיקיית `wp-content/uploads`
- שמירת הגיבוי במקום בטוח

## דרכי שימוש

### אופציה 1: דרך WP-CLI (מומלץ)

#### ניתוח בלבד (ללא מחיקה):
```bash
wp eval-file wp-content/themes/bridge-child/media-cleanup.php
```

#### ניתוח עם מחיקה (Dry Run - סימולציה):
```bash
# מחיקת קבצים ללא רשומה (סימולציה)
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --orphans --dry_run

# מחיקת thumbnails ישנים (סימולציה)
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --thumbnails --dry_run

# מחיקת attachments לא בשימוש (סימולציה)
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --unused --dry_run
```

#### מחיקה בפועל:
```bash
# מחיקת קבצים ללא רשומה (בפועל)
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --orphans --execute

# מחיקת thumbnails ישנים לפני תאריך מסוים
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --thumbnails --older_than=2020-01-01 --execute

# מחיקת attachments לא בשימוש לפני 2020
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --unused --older_than=2020-01-01 --execute
```

### אופציה 2: דרך דף Admin (דורש התאמה)

ניתן להוסיף ל-`functions.php`:

```php
// הוספת דף admin לניקוי מדיה
add_action('admin_menu', function() {
    add_management_page(
        'ניקוי מדיה',
        'ניקוי מדיה',
        'manage_options',
        'media-cleanup',
        'media_cleanup_admin_page'
    );
});

function media_cleanup_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('אין הרשאה');
    }
    
    require_once(get_stylesheet_directory() . '/media-cleanup.php');
    
    echo '<div class="wrap">';
    echo '<h1>ניקוי מדיה לא בשימוש</h1>';
    
    if (isset($_GET['run_media_cleanup'])) {
        echo '<pre>';
        $cleanup = new Media_Cleanup();
        $mode = isset($_GET['mode']) ? $_GET['mode'] : 'analyze';
        
        if ($mode === 'cleanup') {
            $options = array(
                'dry_run' => !isset($_GET['execute']),
                'delete_orphans' => isset($_GET['orphans']),
                'delete_thumbnails' => isset($_GET['thumbnails']),
                'delete_unused' => isset($_GET['unused']),
                'older_than' => isset($_GET['older_than']) ? $_GET['older_than'] : null
            );
            $cleanup->cleanup($options);
        } else {
            $cleanup->analyze($mode);
        }
        echo '</pre>';
    } else {
        echo '<p><a href="?page=media-cleanup&run_media_cleanup=1&mode=analyze" class="button">הרץ ניתוח</a></p>';
    }
    
    echo '</div>';
}
```

### אופציה 3: דרך PHP ישירה

```php
<?php
require_once('wp-load.php');
require_once(get_stylesheet_directory() . '/media-cleanup.php');

$cleanup = new Media_Cleanup();

// ניתוח בלבד
$results = $cleanup->analyze();

// מחיקה מבוקרת (dry run)
$cleanup->cleanup(array(
    'dry_run' => true,
    'delete_orphans' => true,
    'delete_thumbnails' => true,
    'delete_unused' => false,
    'older_than' => '2020-01-01'
));
```

## שלבי עבודה מומלצים

### שלב 1: ניתוח ראשוני
```bash
wp eval-file wp-content/themes/bridge-child/media-cleanup.php
```

זה ייצור דוחות ב-`wp-content/uploads/media-cleanup-reports/`:
- `orphan_files_YYYY-MM-DD_HH-MM-SS.csv` - קבצים ללא רשומה
- `thumbnails_YYYY-MM-DD_HH-MM-SS.csv` - thumbnails
- `unused_attachments_YYYY-MM-DD_HH-MM-SS.csv` - attachments לא בשימוש
- `summary_YYYY-MM-DD_HH-MM-SS.json` - סיכום מפורט

### שלב 2: בדיקה ידנית
1. פתח את קבצי ה-CSV ובדוק דוגמאות
2. ודא שהקבצים באמת לא בשימוש
3. בדוק כמה מקום אפשר לחסוך

### שלב 3: מחיקה בשלבים (מומלץ!)

#### שלב א' - הכי בטוח: קבצים ללא רשומה
```bash
# סימולציה
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --orphans

# מחיקה בפועל (אחרי בדיקה!)
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --orphans --execute
```

#### שלב ב' - thumbnails ישנים
```bash
# סימולציה - thumbnails לפני 2020
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --thumbnails --older_than=2020-01-01

# מחיקה בפועל
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --thumbnails --older_than=2020-01-01 --execute
```

#### שלב ג' - attachments לא בשימוש (זהיר!)
```bash
# סימולציה - attachments ישנים מאוד
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --unused --older_than=2018-01-01

# מחיקה בפועל (רק אחרי בדיקה יסודית!)
wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=cleanup --unused --older_than=2018-01-01 --execute
```

## פרמטרים

- `--mode=analyze` - ניתוח בלבד (ברירת מחדל)
- `--mode=cleanup` - ניקוי (מחיקה)
- `--orphans` - מחיקת קבצים ללא רשומה
- `--thumbnails` - מחיקת thumbnails
- `--unused` - מחיקת attachments לא בשימוש
- `--older_than=YYYY-MM-DD` - סינון לפי תאריך (רק קבצים ישנים יותר)
- `--execute` - ביצוע בפועל (ללא זה זה dry run)
- `--dry_run` - סימולציה בלבד (ברירת מחדל)

## דוחות

הדוחות נשמרים ב-`wp-content/uploads/media-cleanup-reports/`

### קבצי CSV
כוללים את כל הפרטים על הקבצים:
- נתיב מלא
- גודל
- תאריך שינוי
- ועוד...

### קובץ JSON
סיכום מפורט עם:
- סטטיסטיקות
- פוטנציאל שחרור
- תאריך הרצה

## טיפים ואזהרות

1. **תמיד התחל עם dry run** - בדוק מה היה נמחק לפני מחיקה בפועל
2. **גיבוי לפני כל פעולה** - זה קריטי!
3. **מחיקה בשלבים** - אל תמחק הכל בבת אחת
4. **בדוק דוגמאות ידנית** - פתח כמה קבצים מהדוח ובדוק
5. **התחל עם קבצים ישנים** - השתמש ב-`--older_than` להתחלה זהירה
6. **בדוק את האתר אחרי מחיקה** - ודא שאין תמונות חסרות

## פתרון בעיות

### שגיאת הרשאות
אם יש שגיאת הרשאות, ודא ש-WordPress יכול לכתוב ל-`wp-content/uploads/`

### הסקריפט איטי
עם מעל 30,000 קבצים, הסקריפט יכול לקחת זמן. זה נורמלי.

### קבצים לא נמחקים
בדוק הרשאות קבצים. חלק מהקבצים עשויים להיות מוגנים.

## תמיכה

לשאלות או בעיות, בדוק את הדוחות שנוצרו ובדוק את הלוגים.





