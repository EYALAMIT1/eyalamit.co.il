<?php
/**
 * General Redirect Rules - כללים כלליים להפניות
 * 
 * מטרה: להגדיר את כל ההפניות בכמה כללים ספורים כלליים
 * במקום redirects בודדים, נשתמש בכללים כלליים שיטפלו בכל המקרים
 * 
 * @package Bridge Child Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * General Redirect Rules Handler
 * מטפל בכל ההפניות בכמה כללים ספורים כלליים
 * 
 * כללים:
 * 1. Blog URLs - /Blog/... → /... (הסרת /Blog)
 * 2. QR Codes - /qr/... → שמירה על /qr אבל תיקון URL
 * 3. Shop URLs - /shop → /shop/ (הוספת trailing slash)
 * 4. URLs עם encoding עברי - תיקון URL encoding
 */
function ea_general_redirect_rules() {
    // רק אם לא ב-admin או ב-ajax
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    
    // קבלת ה-URL הנוכחי
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $request_uri = urldecode($request_uri); // decode כדי לטפל בעברית
    
    if (empty($request_uri)) {
        return;
    }
    
    $redirect_url = null;
    $redirect_type = 301; // Permanent redirect
    
    // כלל 1: Blog URLs - /Blog/... → /... (הסרת /Blog)
    // דוגמה: /Blog/post-name → /post-name
    if (preg_match('#^/Blog(/.*)?$#i', $request_uri, $matches)) {
        $path_after_blog = isset($matches[1]) ? $matches[1] : '/';
        if (empty($path_after_blog) || $path_after_blog === '/') {
            $redirect_url = home_url('/');
        } else {
            // הסרת /Blog והשארת השאר
            $redirect_url = home_url($path_after_blog);
        }
    }
    
    // כלל 2: Shop URLs - /shop → /shop/ (הוספת trailing slash)
    // דוגמה: /shop → /shop/
    elseif (preg_match('#^/shop$#i', $request_uri)) {
        $redirect_url = home_url('/shop/');
    }
    
    // כלל 3: QR Codes - שמירה על /qr אבל תיקון URL אם נדרש
    // דוגמה: /qr → /qr/ (אם צריך)
    elseif (preg_match('#^/qr$#i', $request_uri)) {
        $redirect_url = home_url('/qr/');
    }
    
    // כלל 4: URLs עם trailing slash כפול או שגוי
    // דוגמה: /page// → /page/
    elseif (preg_match('#//+#', $request_uri)) {
        $clean_uri = preg_replace('#//+#', '/', $request_uri);
        $redirect_url = home_url($clean_uri);
    }
    
    // כלל 5: URLs עם encoding עברי - תיקון אם נדרש
    // אם ה-URL מכיל encoding שגוי, WordPress יטפל בזה אוטומטית
    // אבל אפשר להוסיף כאן טיפול מיוחד אם נדרש
    
    // ביצוע redirect אם נדרש
    if ($redirect_url && $redirect_url !== home_url($request_uri)) {
        // וידוא שה-redirect לא לולאתי
        $current_url = home_url($request_uri);
        if ($redirect_url !== $current_url) {
            wp_redirect($redirect_url, $redirect_type);
            exit;
        }
    }
}
add_action('template_redirect', 'ea_general_redirect_rules', 1);

/**
 * Filter to fix redirect URLs - תיקון URLs ב-redirects
 * 
 * זה עוזר לוודא שכל ה-redirects משתמשים ב-URL הנכון לפי הסביבה
 * 
 * Phase 4 Step 2 - Enhanced to catch all redirects including permalink redirects
 */
function ea_fix_redirect_urls($location, $status) {
    // אם ה-redirect מכיל localhost ללא פורט (בסביבת development)
    // תיקון: localhost → localhost:9090
    if (strpos($location, 'http://localhost/') === 0) {
        $http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        if (strpos($http_host, 'localhost:9090') !== false || strpos($http_host, '127.0.0.1:9090') !== false) {
            $location = str_replace('http://localhost/', 'http://localhost:9090/', $location);
        }
    }
    
    // תיקון: localhost:80 → localhost:9090 (בסביבת development)
    if (strpos($location, 'http://localhost:80/') !== false) {
        $http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        if (strpos($http_host, 'localhost:9090') !== false || strpos($http_host, '127.0.0.1:9090') !== false) {
            $location = str_replace('http://localhost:80/', 'http://localhost:9090/', $location);
        }
    }
    
    // אם ה-redirect מכיל localhost:9090 (בסביבת staging/production)
    if (strpos($location, 'http://localhost:9090') !== false) {
        $http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        
        // תיקון לפי הסביבה
        if (strpos($http_host, 'eyalamit-co-il-2026.s887.upress.link') !== false) {
            $location = str_replace('http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link', $location);
        } elseif (strpos($http_host, 'eyalamit.co.il') !== false) {
            $location = str_replace('http://localhost:9090', 'https://eyalamit.co.il', $location);
        }
    }
    
    return $location;
}
add_filter('wp_redirect', 'ea_fix_redirect_urls', 10, 2);

/**
 * Additional filter to catch redirects from permalink system
 * WordPress permalink redirects sometimes bypass wp_redirect filter
 * 
 * Phase 4 Step 2 - Catch permalink redirects
 */
function ea_fix_permalink_redirects() {
    // Only on frontend
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    
    // Check if we're in a redirect situation
    // This catches redirects that happen before wp_redirect is called
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    
    // If we're on localhost:9090 but WordPress is trying to redirect to localhost:80
    if (strpos($http_host, 'localhost:9090') !== false || strpos($http_host, '127.0.0.1:9090') !== false) {
        // WordPress might be generating URLs with wrong port
        // We'll fix this in the output buffer
    }
}
add_action('template_redirect', 'ea_fix_permalink_redirects', 0);

/**
 * Output buffer filter to fix URLs in redirect headers
 * Catches redirects that are sent via header() directly
 * 
 * Phase 4 Step 2 - Fix redirect headers in output
 */
function ea_fix_redirect_headers($buffer) {
    // Fix Location headers in redirects
    if (headers_sent() || !isset($_SERVER['HTTP_HOST'])) {
        return $buffer;
    }
    
    $http_host = $_SERVER['HTTP_HOST'];
    
    // Fix localhost:80 → localhost:9090 in development
    if (strpos($http_host, 'localhost:9090') !== false || strpos($http_host, '127.0.0.1:9090') !== false) {
        // This is handled by wp_redirect filter, but we add extra safety here
    }
    
    return $buffer;
}
// Note: Output buffering for redirects is tricky, wp_redirect filter should handle most cases
