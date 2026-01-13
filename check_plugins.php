<?php
$db = new mysqli('db', 'eyalamit_user', 'user_password', 'eyalamit_db');
$result = $db->query("SELECT option_value FROM wp_options WHERE option_name = 'active_plugins'");
$row = $result->fetch_assoc();

$data = $row['option_value'];
echo "Raw data length: " . strlen($data) . " bytes\n";
echo "Raw data preview: " . substr($data, 0, 100) . "...\n";

$plugins = unserialize($data);
if ($plugins === false) {
    echo "Unserialize failed!\n";
    // Try to fix common issues
    $data = preg_replace('/s:(\d+):"(.*?)";/', 's:'.strlen('$2').':"'.'";', $data);
    $plugins = unserialize($data);
}

if (is_array($plugins)) {
    echo "סה\"כ תוספים פעילים: " . count($plugins) . "\n";
    echo "רשימת התוספים:\n";
    foreach($plugins as $i => $plugin) {
        echo ($i+1) . '. ' . $plugin . "\n";
    }
} else {
    echo "Still failed to unserialize\n";
}
?>