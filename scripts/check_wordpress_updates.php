<?php
/**
 * בדיקת עדכונים ל-WordPress ותוספים
 * 
 * מטרה: לבדוק אם WordPress וכל התוספים מעודכנים לגרסאות האחרונות
 */

require_once __DIR__ . '/../wp-load.php';

// בדיקת גרסת WordPress
$current_wp_version = get_bloginfo('version');
echo "=== WordPress Version Check ===\n";
echo "Current version: $current_wp_version\n";

// בדיקת גרסה אחרונה זמינה
$wp_api_response = @file_get_contents('https://api.wordpress.org/core/version-check/1.7/');
if ($wp_api_response) {
    $wp_api_data = json_decode($wp_api_response, true);
    if (isset($wp_api_data['offers'][0]['version'])) {
        $latest_wp_version = $wp_api_data['offers'][0]['version'];
        echo "Latest version: $latest_wp_version\n";
        
        if (version_compare($current_wp_version, $latest_wp_version, '<')) {
            echo "Status: ⚠️ UPDATE AVAILABLE\n";
        } else {
            echo "Status: ✅ UP TO DATE\n";
        }
    }
}

echo "\n=== Plugins Check ===\n";

// קבלת כל התוספים
$plugins = get_plugins();
$active_plugins = get_option('active_plugins', []);
$update_info = get_site_transient('update_plugins');

$plugins_status = [];
$needs_update = [];
$up_to_date = [];
$unknown_status = [];

foreach ($plugins as $plugin_file => $plugin_data) {
    $plugin_name = $plugin_data['Name'];
    $current_version = $plugin_data['Version'];
    $is_active = in_array($plugin_file, $active_plugins);
    
    // בדיקה אם יש עדכון זמין
    $has_update = false;
    $latest_version = $current_version;
    
    if ($update_info && isset($update_info->response[$plugin_file])) {
        $has_update = true;
        $latest_version = $update_info->response[$plugin_file]->new_version;
    }
    
    $status = [
        'name' => $plugin_name,
        'file' => $plugin_file,
        'current_version' => $current_version,
        'latest_version' => $latest_version,
        'has_update' => $has_update,
        'is_active' => $is_active
    ];
    
    if ($has_update) {
        $needs_update[] = $status;
    } elseif ($is_active) {
        $up_to_date[] = $status;
    } else {
        $unknown_status[] = $status;
    }
    
    $plugins_status[] = $status;
}

// סיכום
echo "Total plugins: " . count($plugins_status) . "\n";
echo "Active plugins: " . count($active_plugins) . "\n";
echo "Plugins needing update: " . count($needs_update) . "\n";
echo "Plugins up to date: " . count($up_to_date) . "\n";

// רשימת תוספים שצריכים עדכון
if (!empty($needs_update)) {
    echo "\n=== Plugins Needing Update ===\n";
    foreach ($needs_update as $plugin) {
        echo "⚠️ {$plugin['name']}\n";
        echo "   Current: {$plugin['current_version']}\n";
        echo "   Latest: {$plugin['latest_version']}\n";
        echo "   Status: " . ($plugin['is_active'] ? 'Active' : 'Inactive') . "\n\n";
    }
}

// יצירת דוח JSON
$report = [
    'wordpress' => [
        'current_version' => $current_wp_version,
        'latest_version' => $latest_wp_version ?? null,
        'needs_update' => isset($latest_wp_version) && version_compare($current_wp_version, $latest_wp_version, '<')
    ],
    'plugins' => [
        'total' => count($plugins_status),
        'active' => count($active_plugins),
        'needs_update' => count($needs_update),
        'up_to_date' => count($up_to_date),
        'plugins_needing_update' => $needs_update,
        'all_plugins' => $plugins_status
    ]
];

$report_file = __DIR__ . '/../docs/testing/reports/wordpress-updates-check.json';
file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\nReport saved to: $report_file\n";
