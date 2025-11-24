# דוח בדיקות מקיף לפני ייצור

**תאריך:** 2025-11-25 01:03:06

---

## סיכום

- **סך הכל בדיקות:** 29
- **הצליחו:** 11 ✅
- **נכשלו:** 18 ❌
- **אזהרות:** 6 ⚠️
- **שגיאות:** 11 🔴
- **אחוז הצלחה:** 37.93%

## שגיאות קריטיות

- ❌ Port 8080 (main site) is not accessible
- ❌ Could not determine WordPress version
- ❌ Database connection failed
- ❌ Cookie Consent Notice plugin is not active
- ❌ Cookie notice function enqueue_cookie_consent_scripts not found in plugin file
- ❌ Cookie notice function add_cookie_consent_notice not found in plugin file
- ❌ Cookie notice function wp_enqueue_scripts not found in plugin file
- ❌ Cookie notice function wp_footer not found in plugin file
- ❌ Cookie notice JavaScript not found
- ❌ Cookie notice CSS not found
- ❌ Cookie notice HTML structure not found

## אזהרות

- ⚠️  Critical plugin google-site-kit is not active
- ⚠️  Critical plugin wordpress-seo is not active
- ⚠️  Critical plugin woocommerce is not active
- ⚠️  REST API not accessible: HTTPConnectionPool(host='localhost', port=8080): Read timed out. (read timeout=10)
- ⚠️  Homepage load time is 12.23s - consider optimization
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
- **javascript_present:** ❌
- **css_present:** ❌
- **html_present:** ❌
- **plugin_file_valid:** ❌
- **html_in_page:** ❌
- **javascript_in_page:** ✅
- **css_in_page:** ✅

### 5. נגישות האתר

- **homepage:** ✅
  - זמן תגובה: 15.02s
- **admin:** ❌
- **rest_api:** ❌

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
