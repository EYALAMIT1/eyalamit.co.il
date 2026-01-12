<?php
/**
 * WordPress Configuration - SECURE VERSION
 *
 * This is a secure version of wp-config.php that uses environment variables
 * instead of hardcoded sensitive information.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================================
// ENVIRONMENT VARIABLES CONFIGURATION
// ========================================

// Load environment variables
if (file_exists(dirname(__FILE__) . '/.env.php')) {
    require_once dirname(__FILE__) . '/.env.php';
}

// ========================================
// DATABASE CONFIGURATION
// ========================================

/** The name of the database for WordPress */
define('DB_NAME', getenv('DB_NAME') ?: 'deveyala_uprdb');

/** MySQL database username */
define('DB_USER', getenv('DB_USER') ?: 'deveyala_uprdb');

/** MySQL database password */
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'fallback_password_not_secure');

/** MySQL hostname */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', getenv('DB_COLLATE') ?: '');

// ========================================
// WORDPRESS CONFIGURATION
// ========================================

/** WordPress Environment */
define('WP_ENV', getenv('WP_ENV') ?: 'production');

/** Home URL */
define('WP_HOME', getenv('WP_HOME') ?: 'https://www.eyalamit.co.il');

/** Site URL */
define('WP_SITEURL', getenv('WP_SITEURL') ?: 'https://www.eyalamit.co.il');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Or use environment variables for better security
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         getenv('AUTH_KEY')         ?: 'your-unique-auth-key-here');
define('SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY')  ?: 'your-unique-secure-auth-key-here');
define('LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY')    ?: 'your-unique-logged-in-key-here');
define('NONCE_KEY',        getenv('NONCE_KEY')        ?: 'your-unique-nonce-key-here');
define('AUTH_SALT',        getenv('AUTH_SALT')        ?: 'your-unique-auth-salt-here');
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT') ?: 'your-unique-secure-auth-salt-here');
define('LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT')   ?: 'your-unique-logged-in-salt-here');
define('NONCE_SALT',       getenv('NONCE_SALT')       ?: 'your-unique-nonce-salt-here');

/**#@-*/

// ========================================
// SECURITY CONFIGURATION
// ========================================

/** WordPress Cache */
define('WP_CACHE', getenv('WP_CACHE') ?: true);

/** Disable File Editor */
define('DISALLOW_FILE_EDIT', getenv('DISALLOW_FILE_EDIT') ?: true);

/** Force SSL for Admin */
define('FORCE_SSL_ADMIN', getenv('FORCE_SSL_ADMIN') ?: true);

/** WordPress Debug Mode */
define('WP_DEBUG', getenv('WP_DEBUG') ?: false);
define('WP_DEBUG_LOG', getenv('WP_DEBUG_LOG') ?: false);
define('WP_DEBUG_DISPLAY', getenv('WP_DEBUG_DISPLAY') ?: false);

// ========================================
// PERFORMANCE CONFIGURATION
// ========================================

/** Memory Limits */
define('WP_MEMORY_LIMIT', getenv('WP_MEMORY_LIMIT') ?: '512M');
define('WP_MAX_MEMORY_LIMIT', getenv('WP_MAX_MEMORY_LIMIT') ?: '512M');

// ========================================
// EMAIL CONFIGURATION
// ========================================

/** SMTP Settings */
define('WP_MAIL_SMTP_HOST', getenv('WP_MAIL_SMTP_HOST') ?: 'smtp.sendgrid.net');
define('WP_MAIL_SMTP_PORT', getenv('WP_MAIL_SMTP_PORT') ?: 587);
define('WP_MAIL_SMTP_USER', getenv('WP_MAIL_SMTP_USER') ?: 'apikey');
define('WP_MAIL_SMTP_PASS', getenv('WP_MAIL_SMTP_PASS') ?: '');
define('WP_MAIL_FROM', getenv('WP_MAIL_FROM') ?: 'wordpress@eyalamit.co.il');
define('WP_MAIL_FROM_NAME', getenv('WP_MAIL_FROM_NAME') ?: 'Eyal Amit');

// ========================================
// DATABASE TABLE PREFIX
// ========================================

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

// ========================================
// WORDPRESS ABSOLUTE PATH
// ========================================

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// ========================================
// SECURITY ENHANCEMENTS
// ========================================

/**
 * Block access to sensitive files
 */
if (!function_exists('block_direct_access')) {
    function block_direct_access() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';

        // Block access to sensitive files
        $blocked_files = [
            'wp-config.php',
            '.env.php',
            'wp-config-sample.php',
            'readme.html',
            'license.txt',
            'wp-admin/install.php'
        ];

        $file_name = basename($request_uri);
        if (in_array($file_name, $blocked_files)) {
            http_response_code(403);
            exit('Access denied');
        }

        // Block PHP execution in uploads directory
        if (strpos($request_uri, '/wp-content/uploads/') !== false &&
            strpos($request_uri, '.php') !== false) {
            http_response_code(403);
            exit('Access denied');
        }
    }
    block_direct_access();
}

// ========================================
// LOAD WORDPRESS
// ========================================

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

// ========================================
// ENVIRONMENT LOADER
// ========================================

/**
 * Load encrypted environment file
 * This function should be called after WordPress is loaded
 */
function load_encrypted_env() {
    $env_file = ABSPATH . '.env.enc.php';
    $key_file = ABSPATH . '.env.key';

    if (file_exists($env_file) && file_exists($key_file)) {
        $encryption_key = trim(file_get_contents($key_file));

        // Decrypt and load environment variables
        $encrypted_content = file_get_contents($env_file);
        $decrypted_content = openssl_decrypt($encrypted_content, 'aes-256-cbc', $encryption_key, 0);

        if ($decrypted_content !== false) {
            $env_vars = parse_ini_string($decrypted_content);
            if ($env_vars) {
                foreach ($env_vars as $key => $value) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}
load_encrypted_env();