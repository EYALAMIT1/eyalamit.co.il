# Phase 2.3 - Implementation Checklist & Quality Assurance
**Task ID:** EA-V11-PHASE-2.3  
**Team:** Team 1 (Development) + Team 2 (QA)  
**Status:** 🔴 BLOCKED - Awaiting Implementation

## 🎯 מטרת המסמך

מסמך זה מפרט את כל השלבים הנדרשים להטמעה מלאה ואיכותית של Schema JSON-LD ו-Alt-Text Coverage עבור Phase 2.3.

---

## ✅ שלב 1: בדיקת תשתית בסיסית

### 1.1 וידוא טעינת הקובץ

**בדיקה ידנית:**
```php
// הוסף זמנית ל-functions.php לבדיקה:
error_log('Schema file loaded: ' . (file_exists(get_stylesheet_directory() . '/schema-person-specialist.php') ? 'YES' : 'NO'));
```

**בדיקה אוטומטית:**
```bash
# בדוק שהקובץ קיים
ls -la wp-content/themes/bridge-child/schema-person-specialist.php

# בדוק שהקובץ נטען ב-functions.php
grep -n "schema-person-specialist" wp-content/themes/bridge-child/functions.php
```

**תוצאה צפויה:**
- ✅ הקובץ קיים
- ✅ הקובץ נטען ב-`functions.php` בשורה 12

### 1.2 בדיקת שגיאות PHP

**בדיקה:**
```bash
# הרץ בדיקת syntax
php -l wp-content/themes/bridge-child/schema-person-specialist.php
php -l wp-content/themes/bridge-child/functions.php
```

**תוצאה צפויה:**
- ✅ No syntax errors detected

### 1.3 בדיקת פונקציות רשומות

**בדיקה:**
```php
// הוסף זמנית ל-functions.php:
add_action('wp_footer', function() {
    if (function_exists('ea_add_person_schema')) {
        error_log('✅ ea_add_person_schema exists');
    } else {
        error_log('❌ ea_add_person_schema NOT FOUND');
    }
    
    if (function_exists('ea_add_specialist_schema')) {
        error_log('✅ ea_add_specialist_schema exists');
    } else {
        error_log('❌ ea_add_specialist_schema NOT FOUND');
    }
    
    if (function_exists('ea_add_faq_schema')) {
        error_log('✅ ea_add_faq_schema exists');
    } else {
        error_log('❌ ea_add_faq_schema NOT FOUND');
    }
}, 999);
```

**תוצאה צפויה:**
- ✅ כל הפונקציות קיימות

---

## ✅ שלב 2: בדיקת Schema Output

### 2.1 בדיקת Page Source

**הוראות:**
1. פתח את האתר בדפדפן: `http://localhost:9090`
2. לחץ על View Source (Ctrl+U / Cmd+U)
3. חפש: `ea-person-schema`
4. חפש: `ea-specialist-schema`
5. חפש: `ea-faq-schema`

**תוצאה צפויה:**
- ✅ נמצא: `<script type="application/ld+json" class="ea-person-schema">`
- ✅ נמצא: `<script type="application/ld+json" class="ea-specialist-schema">`
- ✅ נמצא: `<script type="application/ld+json" class="ea-faq-schema">` (רק בעמוד הבית)

### 2.2 בדיקת תוכן Schema

**בדיקה ידנית:**
- פתח את ה-Schema ב-Page Source
- העתק את ה-JSON-LD
- בדוק שהשדות הבאים קיימים:

**Person Schema:**
- ✅ `@type: "Person"`
- ✅ `name: "אייל עמית"`
- ✅ `jobTitle: "מומחה לריפוי בדיגרידו ומורה נשימה מעגלית"`
- ✅ `sameAs` (קישורים לרשתות חברתיות)
- ✅ `telephone: "052-4822842"`
- ✅ `email: "info@eyalamit.co.il"`

**Specialist Schema:**
- ✅ `@type: "HealthAndBeautyBusiness"`
- ✅ `name: "מרכז לטיפול בדיגרידו - סטודיו נשימה מעגלית"`
- ✅ `address` (כתובת מלאה)
- ✅ `geo` (קואורדינטות GPS)
- ✅ `hasOfferCatalog` (שירותים)

**FAQ Schema:**
- ✅ `@type: "FAQPage"`
- ✅ `mainEntity` (רשימת שאלות ותשובות)

### 2.3 בדיקת Schema.org Validator

**הוראות:**
1. פתח: https://validator.schema.org/
2. העתק את ה-JSON-LD מה-Page Source
3. הדבק ב-validator
4. לחץ על "Test"

**תוצאה צפויה:**
- ✅ No errors
- ✅ No warnings (או אזהרות מינוריות בלבד)

### 2.4 בדיקת Google Rich Results Test

**הוראות:**
1. פתח: https://search.google.com/test/rich-results
2. הזן את ה-URL: `http://localhost:9090`
3. לחץ על "Test URL"

**תוצאה צפויה:**
- ✅ Person Schema מוכר
- ✅ Business Schema מוכר
- ✅ FAQ Schema מוכר (אם קיים)

---

## ✅ שלב 3: בדיקת Alt-Text Coverage

### 3.1 זיהוי תמונות ללא Alt-Text

**בדיקה באמצעות WP-CLI:**
```bash
wp eval-file docs/development/ALT-TEXT-INVENTORY-SCRIPT.php
```

**או באמצעות PHP:**
```php
// הוסף זמנית ל-functions.php:
add_action('admin_init', function() {
    if (current_user_can('manage_options') && isset($_GET['ea_check_alt'])) {
        require_once get_stylesheet_directory() . '/../development/ALT-TEXT-INVENTORY-SCRIPT.php';
        $inventory = ea_get_images_without_alt();
        echo '<pre>';
        print_r($inventory);
        echo '</pre>';
        exit;
    }
});
```

**בדיקה ידנית:**
1. פתח את Media Library ב-WordPress Admin
2. בדוק כל תמונה
3. וודא שיש Alt Text

**תוצאה צפויה:**
- ✅ כל התמונות בעלות Alt Text
- ✅ Alt Text תיאורי ומשמעותי

### 3.2 בדיקת Alt-Text בעמודים

**בדיקה ידנית:**
1. פתח את האתר בדפדפן
2. לחץ על כל תמונה (Right Click → Inspect)
3. בדוק שה-`alt` attribute קיים ומשמעותי

**בדיקה אוטומטית:**
```javascript
// הרץ בקונסולה של הדפדפן:
document.querySelectorAll('img').forEach(img => {
    if (!img.alt || img.alt.trim() === '') {
        console.warn('Image without alt:', img.src);
    }
});
```

**תוצאה צפויה:**
- ✅ כל התמונות בעלות `alt` attribute
- ✅ אין אזהרות בקונסולה

---

## ✅ שלב 4: בדיקת Zero Console Errors

### 4.1 בדיקה אוטומטית

**הרץ את הבדיקה:**
```bash
python3 tests/console_verification_test.py
```

**תוצאה צפויה:**
- ✅ 0 JavaScript errors
- ✅ 0 CORS errors
- ✅ 0 Network errors

### 4.2 בדיקה ידנית

**הוראות:**
1. פתח את האתר בדפדפן
2. פתח את Developer Tools (F12)
3. עבור ל-tab Console
4. בדוק שאין שגיאות אדומות

**תוצאה צפויה:**
- ✅ אין שגיאות אדומות
- ✅ אין אזהרות קריטיות

---

## ✅ שלב 5: בדיקת איכות קוד

### 5.1 בדיקת PHP Syntax

```bash
php -l wp-content/themes/bridge-child/schema-person-specialist.php
```

### 5.2 בדיקת PHPCS (אם מותקן)

```bash
phpcs --standard=WordPress wp-content/themes/bridge-child/schema-person-specialist.php
```

### 5.3 בדיקת Best Practices

**וידוא:**
- ✅ כל הפונקציות משתמשות בקידומת `ea_`
- ✅ כל הקלטים עוברים sanitization
- ✅ אין hardcoded values (חוץ מנתונים אישיים)
- ✅ יש הערות תיעוד (PHPDoc)
- ✅ הקוד מאורגן ונקי

---

## 🔧 פתרון בעיות נפוצות

### בעיה: Schema לא מופיע ב-Page Source

**סיבות אפשריות:**
1. הקובץ לא נטען
2. יש שגיאת PHP שמונעת את הטעינה
3. הפונקציות לא נקראות

**פתרון:**
```php
// הוסף זמנית ל-functions.php לבדיקה:
add_action('wp_head', function() {
    if (function_exists('ea_add_person_schema')) {
        error_log('✅ Schema functions loaded');
        ea_add_person_schema();
    } else {
        error_log('❌ Schema functions NOT loaded');
    }
}, 1);
```

### בעיה: Schema מופיע אבל לא תקין

**בדיקה:**
1. בדוק את ה-JSON syntax
2. בדוק שאין שגיאות PHP
3. בדוק שהנתונים מלאים

**פתרון:**
```php
// הוסף זמנית לבדיקת output:
add_action('wp_head', function() {
    $person_schema = array(/* ... */);
    $json = wp_json_encode($person_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    error_log('Person Schema JSON: ' . $json);
}, 1);
```

### בעיה: Alt-Text לא נשמר

**בדיקה:**
1. בדוק שהפונקציה `ea_enforce_alt_text_on_upload` רשומה
2. בדוק שהפונקציה `ea_auto_fix_alt_text` עובדת

**פתרון:**
```php
// בדוק שהפונקציות רשומות:
add_action('admin_init', function() {
    if (has_action('add_attachment', 'ea_enforce_alt_text_on_upload')) {
        error_log('✅ Alt-text enforcement registered');
    } else {
        error_log('❌ Alt-text enforcement NOT registered');
    }
});
```

---

## 📋 Checklist סופי להטמעה

### לפני בדיקת QA:

- [ ] **תשתית:**
  - [ ] קובץ `schema-person-specialist.php` קיים
  - [ ] הקובץ נטען ב-`functions.php`
  - [ ] אין שגיאות PHP syntax
  - [ ] כל הפונקציות קיימות

- [ ] **Schema Output:**
  - [ ] Person Schema מופיע ב-Page Source
  - [ ] Specialist Schema מופיע ב-Page Source
  - [ ] FAQ Schema מופיע ב-Page Source (עמוד הבית)
  - [ ] Schema עובר validation ב-Schema.org Validator
  - [ ] Schema מוכר ב-Google Rich Results Test

- [ ] **Alt-Text:**
  - [ ] כל התמונות בעלות Alt Text
  - [ ] Alt Text תיאורי ומשמעותי
  - [ ] פונקציית auto-fix עובדת

- [ ] **Zero Console Errors:**
  - [ ] 0 JavaScript errors
  - [ ] 0 CORS errors
  - [ ] 0 Network errors

- [ ] **איכות קוד:**
  - [ ] כל הפונקציות משתמשות בקידומת `ea_`
  - [ ] כל הקלטים עוברים sanitization
  - [ ] יש הערות תיעוד
  - [ ] הקוד נקי ומאורגן

### לאחר בדיקת QA:

- [ ] Team 2 אישר Schema validation
- [ ] Team 2 אישר Alt-Text coverage (100%)
- [ ] Team 2 אישר Zero Console Errors
- [ ] Phase 2.3 Step 3 מסומן כ-🟢 COMPLETED

---

## 📝 דיווח סיום

לאחר השלמת כל הבדיקות, צוות 1 צריך לדווח:

```
✅ Phase 2.3 Schema Implementation - COMPLETED

Done:
- Schema JSON-LD implemented and validated
- Alt-Text coverage: 100%
- Zero Console Errors maintained
- All quality checks passed

Evidence:
- Page source shows Schema markup
- Schema.org Validator: PASSED
- Google Rich Results Test: PASSED
- Console verification: 0 errors
- Alt-Text inventory: 100% coverage

Ready for Team 2 validation.
```

---

## 🆘 תמיכה

לשאלות או בעיות:
- **צוות 3 (Gatekeeper):** תיעוד וניהול
- **צוות 0 (Architect):** בעיות טכניות מורכבות
- **CEO:** אישורים והחלטות

---

**מסמך זה הוא חלק מ-SSOT v11.0**  
**עודכן:** 2026-01-14  
**גרסה:** 1.0
