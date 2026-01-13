<?php
/**
 * בדיקה מקיפה של עדכונים ל-WordPress ותוספים
 * 
 * מטרה: לבדוק אם WordPress וכל התוספים מעודכנים לגרסאות האחרונות
 */

require_once __DIR__ . '/../wp-load.php';

// עדכון מידע על עדכונים
wp_update_plugins();
wp_update_themes();

echo "=== WordPress Version Check ===\n";
$current_wp_version = get_bloginfo('version');
echo "Current version: $current_wp_version\n";

// בדיקת גרסה אחרונה זמינה
$wp_api_response = @file_get_contents('https://api.wordpress.org/core/version-check/1.7/');
$latest_wp_version = null;
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

$all_plugins_list = [];
$needs_update = [];
$up_to_date = [];
$inactive_plugins = [];

foreach ($plugins as $plugin_file => $plugin_data) {
    $plugin_name = $plugin_data['Name'];
    $current_version = $plugin_data['Version'];
    $is_active = in_array($plugin_file, $active_plugins);
    
    // בדיקה אם יש עדכון זמין
    $has_update = false;
    $latest_version = $current_version;
    $update_url = null;
    
    if ($update_info && isset($update_info->response[$plugin_file])) {
        $has_update = true;
        $latest_version = $update_info->response[$plugin_file]->new_version;
        $update_url = $update_info->response[$plugin_file]->package ?? null;
    }
    
    $status = [
        'name' => $plugin_name,
        'file' => $plugin_file,
        'current_version' => $current_version,
        'latest_version' => $latest_version,
        'has_update' => $has_update,
        'is_active' => $is_active,
        'update_url' => $update_url
    ];
    
    $all_plugins_list[] = $status;
    
    if ($has_update) {
        if ($is_active) {
            $needs_update[] = $status;
        } else {
            $inactive_plugins[] = $status;
        }
    } elseif ($is_active) {
        $up_to_date[] = $status;
    } else {
        $inactive_plugins[] = $status;
    }
}

// סיכום
echo "Total plugins installed: " . count($all_plugins_list) . "\n";
echo "Active plugins: " . count($active_plugins) . "\n";
echo "Active plugins needing update: " . count($needs_update) . "\n";
echo "Active plugins up to date: " . count($up_to_date) . "\n";
echo "Inactive plugins: " . count($inactive_plugins) . "\n";

// רשימת תוספים פעילים שצריכים עדכון
if (!empty($needs_update)) {
    echo "\n=== ⚠️ Active Plugins Needing Update ===\n";
    foreach ($needs_update as $plugin) {
        echo "⚠️ {$plugin['name']}\n";
        echo "   Current: {$plugin['current_version']}\n";
        echo "   Latest: {$plugin['latest_version']}\n";
        echo "   File: {$plugin['file']}\n\n";
    }
}

// רשימת כל התוספים הפעילים
echo "\n=== All Active Plugins ===\n";
$active_plugins_list = array_filter($all_plugins_list, function($p) { return $p['is_active']; });
usort($active_plugins_list, function($a, $b) { return strcmp($a['name'], $b['name']); });

foreach ($active_plugins_list as $plugin) {
    $status_icon = $plugin['has_update'] ? '⚠️' : '✅';
    echo "$status_icon {$plugin['name']} - {$plugin['current_version']}";
    if ($plugin['has_update']) {
        echo " → {$plugin['latest_version']} (UPDATE AVAILABLE)";
    }
    echo "\n";
}

// יצירת דוח JSON
$report = [
    'check_date' => date('Y-m-d H:i:s'),
    'wordpress' => [
        'current_version' => $current_wp_version,
        'latest_version' => $latest_wp_version,
        'needs_update' => $latest_wp_version && version_compare($current_wp_version, $latest_wp_version, '<')
    ],
    'plugins' => [
        'total_installed' => count($all_plugins_list),
        'total_active' => count($active_plugins),
        'active_needing_update' => count($needs_update),
        'active_up_to_date' => count($up_to_date),
        'inactive' => count($inactive_plugins),
        'active_plugins_needing_update' => $needs_update,
        'all_active_plugins' => $active_plugins_list,
        'all_plugins' => $all_plugins_list
    ]
];

$report_file = __DIR__ . '/../docs/testing/reports/wordpress-updates-comprehensive-check.json';
file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\nReport saved to: $report_file\n";
