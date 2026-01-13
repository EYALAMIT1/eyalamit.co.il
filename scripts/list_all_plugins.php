<?php
/**
 * Script to list all plugins from database (active_plugins option)
 * and compare with filesystem
 */

require_once __DIR__ . '/../wp-load.php';

// Get active plugins from database
$active_plugins = get_option('active_plugins', []);

// Get all installed plugins (from filesystem)
$all_plugins = get_plugins();

// Get plugin updates info
$updates = get_site_transient('update_plugins');
$update_available = [];
if ($updates && isset($updates->response)) {
    foreach ($updates->response as $file => $plugin_data) {
        $update_available[$file] = $plugin_data->new_version;
    }
}

echo "=== רשימת כל התוספים מבסיס הנתונים ===\n\n";
echo "סה\"כ תוספים פעילים בבסיס הנתונים: " . count($active_plugins) . "\n";
echo "סה\"כ תוספים בקבצים: " . count($all_plugins) . "\n\n";

$plugins_data = [];

foreach ($active_plugins as $plugin_file) {
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
    $exists = file_exists($plugin_path);
    
    $plugin_info = [
        'file' => $plugin_file,
        'exists' => $exists,
        'name' => 'Unknown',
        'version' => 'Unknown',
        'update_available' => false,
        'new_version' => null
    ];
    
    if ($exists) {
        // Get plugin data from file
        $plugin_data = get_plugin_data($plugin_path);
        $plugin_info['name'] = $plugin_data['Name'];
        $plugin_info['version'] = $plugin_data['Version'];
        
        // Check if update available
        if (isset($update_available[$plugin_file])) {
            $plugin_info['update_available'] = true;
            $plugin_info['new_version'] = $update_available[$plugin_file];
        }
    } else {
        // Try to extract plugin name from file path
        $parts = explode('/', $plugin_file);
        $plugin_info['name'] = $parts[0] ?? $plugin_file;
    }
    
    $plugins_data[] = $plugin_info;
}

// Sort by name
usort($plugins_data, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Output table
echo str_pad("#", 4) . " | " . 
     str_pad("שם התוסף", 50) . " | " . 
     str_pad("קובץ", 40) . " | " . 
     str_pad("גרסה נוכחית", 15) . " | " . 
     str_pad("סטטוס", 10) . " | " . 
     str_pad("עדכון זמין", 15) . "\n";
echo str_repeat("-", 150) . "\n";

$exists_count = 0;
$missing_count = 0;
$needs_update_count = 0;

foreach ($plugins_data as $index => $plugin) {
    $num = $index + 1;
    $status = $plugin['exists'] ? "✅ קיים" : "❌ חסר";
    $update_info = $plugin['update_available'] 
        ? "→ {$plugin['new_version']}" 
        : "✅ עדכני";
    
    echo str_pad($num, 4) . " | " . 
         str_pad($plugin['name'], 50) . " | " . 
         str_pad($plugin['file'], 40) . " | " . 
         str_pad($plugin['version'], 15) . " | " . 
         str_pad($status, 10) . " | " . 
         str_pad($update_info, 15) . "\n";
    
    if ($plugin['exists']) {
        $exists_count++;
    } else {
        $missing_count++;
    }
    
    if ($plugin['update_available']) {
        $needs_update_count++;
    }
}

echo "\n=== סיכום ===\n";
echo "✅ תוספים קיימים: {$exists_count}\n";
echo "❌ תוספים חסרים: {$missing_count}\n";
echo "🔄 תוספים שצריכים עדכון: {$needs_update_count}\n";

// Generate detailed JSON report
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_active_plugins' => count($active_plugins),
    'total_plugins_in_filesystem' => count($all_plugins),
    'exists_count' => $exists_count,
    'missing_count' => $missing_count,
    'needs_update_count' => $needs_update_count,
    'plugins' => $plugins_data
];

$json_file = __DIR__ . '/../docs/testing/reports/all-plugins-list.json';
file_put_contents($json_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nדוח JSON נשמר ל: {$json_file}\n";

// Generate text list for missing plugins
$missing_plugins_file = __DIR__ . '/../docs/testing/reports/missing-plugins-detailed-list.txt';
$missing_content = "רשימת תוספים חסרים מבסיס הנתונים\n";
$missing_content .= "תאריך: " . date('Y-m-d H:i:s') . "\n";
$missing_content .= str_repeat("=", 80) . "\n\n";

foreach ($plugins_data as $plugin) {
    if (!$plugin['exists']) {
        $missing_content .= "❌ {$plugin['name']}\n";
        $missing_content .= "   קובץ: {$plugin['file']}\n";
        $missing_content .= "   גרסה בבסיס נתונים: {$plugin['version']}\n\n";
    }
}

file_put_contents($missing_plugins_file, $missing_content);
echo "רשימת תוספים חסרים נשמרה ל: {$missing_plugins_file}\n";

// Generate markdown report
$md_file = __DIR__ . '/../docs/testing/reports/all-plugins-detailed-report.md';
$md_content = "# דוח מפורט - כל התוספים מבסיס הנתונים\n\n";
$md_content .= "**תאריך:** " . date('Y-m-d H:i:s') . "\n\n";
$md_content .= "## סיכום\n\n";
$md_content .= "- **סה\"כ תוספים פעילים בבסיס נתונים:** " . count($active_plugins) . "\n";
$md_content .= "- **סה\"כ תוספים בקבצים:** " . count($all_plugins) . "\n";
$md_content .= "- **✅ תוספים קיימים:** {$exists_count}\n";
$md_content .= "- " . "**❌ תוספים חסרים:** {$missing_count}\n";
$md_content .= "- " . "**🔄 תוספים שצריכים עדכון:** {$needs_update_count}\n\n";

$md_content .= "## רשימת כל התוספים\n\n";
$md_content .= "| # | שם התוסף | קובץ | גרסה נוכחית | סטטוס | עדכון זמין |\n";
$md_content .= "|---|-----------|------|---------------|--------|-------------|\n";

foreach ($plugins_data as $index => $plugin) {
    $num = $index + 1;
    $status = $plugin['exists'] ? "✅ קיים" : "❌ חסר";
    $update_info = $plugin['update_available'] 
        ? "→ {$plugin['new_version']}" 
        : "✅ עדכני";
    
    $md_content .= "| {$num} | {$plugin['name']} | `{$plugin['file']}` | {$plugin['version']} | {$status} | {$update_info} |\n";
}

$md_content .= "\n## תוספים חסרים (פירוט)\n\n";
foreach ($plugins_data as $plugin) {
    if (!$plugin['exists']) {
        $md_content .= "### ❌ {$plugin['name']}\n\n";
        $md_content .= "- **קובץ:** `{$plugin['file']}`\n";
        $md_content .= "- **גרסה בבסיס נתונים:** {$plugin['version']}\n";
        $md_content .= "- **סטטוס:** רשום כפעיל אבל הקובץ לא נמצא\n\n";
    }
}

$md_content .= "\n## תוספים שצריכים עדכון\n\n";
foreach ($plugins_data as $plugin) {
    if ($plugin['update_available']) {
        $md_content .= "- **{$plugin['name']}**: {$plugin['version']} → {$plugin['new_version']}\n";
    }
}

file_put_contents($md_file, $md_content);
echo "דוח Markdown נשמר ל: {$md_file}\n";
