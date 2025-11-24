# דוח בדיקות מקיף לפני ייצור

**תאריך:** 2025-11-25 01:10:59

---

## סיכום

- **סך הכל בדיקות:** 29
- **הצליחו:** 15 ✅
- **נכשלו:** 14 ❌
- **אזהרות:** 5 ⚠️
- **שגיאות:** 5 🔴
- **אחוז הצלחה:** 51.72%

## שגיאות קריטיות

- ❌ Port 8080 (main site) is not accessible
- ❌ Could not determine WordPress version
- ❌ Database connection failed
- ❌ Cookie Consent Notice plugin is not active
- ❌ Homepage not accessible: HTTPConnectionPool(host='localhost', port=8080): Read timed out. (read timeout=15)

## אזהרות

- ⚠️  Critical plugin google-site-kit is not active
- ⚠️  Critical plugin wordpress-seo is not active
- ⚠️  Critical plugin woocommerce is not active
- ⚠️  Homepage load time is 9.68s - consider optimization
- ⚠️  Response size is 505.19 KB - consider optimization

## תוצאות מפורטות

### 1. Docker ו-Infrastructure

- **wordpress:** ✅
- **nginx:** ✅
- **db:** ✅
- **phpmyadmin:** ✅
- **port_8080:** ❌
- **port_8081:** ❌

### 2. WordPress Core

- **version:** unknown
- **version_check:** ❌
- **db_version:** unknown
- **db_version_check:** ❌
- **wp_debug:** ❌
- **disallow_file_edit:** ✅
- **db_connection:** ❌
- **php_version:** 8.2
- **php_version_check:** ✅

### 3. פלאגינים

- **plugin_count:** 0
- **plugin_google-site-kit:** ❌
- **plugin_wordpress-seo:** ❌
- **plugin_woocommerce:** ❌
- **updates_available:** 0
- **cookie_plugin:** ❌

### 4. הודעת הקוקיז

- **functions:** ❌
- **javascript_present:** ✅
- **css_present:** ✅
- **html_present:** ✅
- **plugin_file_valid:** ✅
- **html_in_page:** ❌
- **javascript_in_page:** ✅
- **css_in_page:** ✅

### 5. נגישות האתר

- **homepage:** ❌
- **admin:** ❌
- **rest_api:** ✅

### 6. שגיאות

- **wordpress_logs:** ❌
- **debug_log:** ❌
- **nginx_logs:** ❌

### 7. תאימות

- **php_version:** 8.2.29
- **php_compatible:** ✅

### 8. ביצועים

- **homepage_load_time:** ❌

### 9. אבטחה

- **wp_debug:** ❌
- **disallow_file_edit:** ✅
- **security_score:** 2

### 10. WooCommerce

- **active:** ❌

---

**דוח נוצר אוטומטית על ידי comprehensive-site-check.py**
