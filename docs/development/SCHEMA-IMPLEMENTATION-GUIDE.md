# Schema JSON-LD Implementation Guide
**Phase 2.3 - Semantic SEO & Schema Infrastructure**  
**Task ID:** EA-V11-PHASE-2.3  
**Team:** Team 1 (Development)

## סקירה כללית

מדריך זה מפרט את הטמעת Schema JSON-LD עבור אתר אייל עמית. הקוד מוכן להטמעה ומותאם אישית לפי הצרכים.

## קבצים שנוצרו

1. **`wp-content/themes/bridge-child/schema-person-specialist.php`** - קובץ Schema מלא מוכן להטמעה

## הוראות הטמעה

### שלב 1: העתקת הקובץ

```bash
# הקובץ כבר נוצר ב:
wp-content/themes/bridge-child/schema-person-specialist.php
```

### שלב 2: הכללת הקובץ ב-functions.php

הוסף את השורה הבאה ל-`wp-content/themes/bridge-child/functions.php`:

```php
// Load Schema JSON-LD implementation
require_once get_stylesheet_directory() . '/schema-person-specialist.php';
```

### שלב 3: בדיקת נתונים אישיים

הקוד כבר כולל את כל המידע האישי של אייל עמית:
- ✅ שם: אייל עמית / Eyal Amit
- ✅ תפקיד: מומחה לריפוי בדיגרידו ומורה נשימה מעגלית
- ✅ תיאור מקצועי מלא
- ✅ קישורים לרשתות חברתיות (Instagram, YouTube, Facebook)
- ✅ פרטי קשר (טלפון, אימייל)
- ✅ כתובת (פרדס חנה)
- ✅ שירותים (טיפול, שיעורים, סדנאות)

אם יש צורך לעדכן נתונים, ערוך את `schema-person-specialist.php` ישירות.

### שלב 4: בדיקת הטמעה

1. **בדיקת Source Code:**
   - פתח את העמוד בדפדפן
   - לחץ על View Source (Ctrl+U / Cmd+U)
   - חפש: `<script type="application/ld+json" class="ea-person-schema">`
   - וידוא: Schema JSON-LD מופיע ב-`<head>`

2. **בדיקת Schema.org Validator:**
   - העתק את ה-JSON-LD מה-source
   - הדבק ב: https://validator.schema.org/
   - וידוא: אין שגיאות validation

3. **בדיקת Google Rich Results:**
   - פתח: https://search.google.com/test/rich-results
   - הזן את ה-URL
   - וידוא: Rich Results מוצגים נכון

## מבנה Schema

### Person Schema (מוכן להטמעה)

הקוד כולל Person Schema מלא עם:
- שם בעברית ואנגלית
- תפקיד מקצועי
- תיאור מפורט
- קישורים לרשתות חברתיות
- תחומי ידע (knowsAbout)
- תעסוקה ומיקום (hasOccupation)
- תמונה, טלפון, אימייל

### Specialist Schema (HealthAndBeautyBusiness)

הקוד כולל Schema מלא של עסק טיפולי עם:
- שם העסק בעברית ואנגלית
- כתובת מלאה עם קואורדינטות GPS
- שעות פתיחה
- טווח מחירים
- שירותים (טיפול, שיעורים, סדנאות)
- מייסד (אייל עמית)

### FAQ Schema (אוטומטי מ-Elementor)

```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [{
    "@type": "Question",
    "name": "[שאלה]",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "[תשובה]"
    }
  }]
}
```

## הערות חשובות

1. **קידומת ea_**: כל הפונקציות משתמשות בקידומת `ea_` לפי סטנדרט הפיתוח
2. **Sanitization**: כל הקלטים עוברים `sanitize_text_field()` או `wp_kses_post()`
3. **Performance**: Schema נטען רק בעמודים רלוונטיים (front page או pages)
4. **Elementor Integration**: FAQ Schema נוצר אוטומטית מ-Elementor Toggle widgets

## בדיקות נדרשות

- [ ] Schema מופיע ב-page source
- [ ] Schema.org Validator מאשר ללא שגיאות
- [ ] Google Rich Results Test מציג תוצאות
- [ ] Zero Console Errors נשמר
- [ ] FAQ Schema נוצר אוטומטית מ-Elementor Toggles

## תמיכה

לשאלות או בעיות, צור קשר עם צוות 3 (Gatekeeper).
