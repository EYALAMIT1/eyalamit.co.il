<?php
/**
 * ניקוי Sitemap מעמודים לא רלוונטיים
 * 
 * מטרה:
 * 1. להסיר redirects מה-sitemap
 * 2. להשאיר רק עמודים אמיתיים
 * 3. ליצור sitemap נקי
 */

require_once __DIR__ . '/../wp-load.php';

// טעינת מפת redirects
$redirects_file = __DIR__ . '/../docs/testing/reports/redirects-mapping.json';
if (!file_exists($redirects_file)) {
    die("Error: redirects-mapping.json not found. Run map_redirects.php first.\n");
}

$mapping_data = json_decode(file_get_contents($redirects_file), true);
$redirects = $mapping_data['redirects_map'] ?? [];
$real_pages = $mapping_data['real_pages'] ?? [];

// יצירת רשימת URLs שצריך להסיר (redirects)
$redirects_urls = array_column($redirects, 'from');

echo "Found " . count($redirects_urls) . " redirects to remove from sitemap\n";
echo "Found " . count($real_pages) . " real pages to keep\n\n";

// כאן צריך להוסיף לוגיקה לעדכון ה-sitemap ב-Yoast SEO או WordPress
// כרגע רק מדפיס את הרשימה

echo "## URLs to Remove from Sitemap:\n\n";
foreach ($redirects_urls as $url) {
    echo "- $url\n";
}

echo "\n## Real Pages to Keep in Sitemap:\n\n";
foreach ($real_pages as $page) {
    echo "- " . $page['url'] . " (HTTP " . $page['http_code'] . ")\n";
}

// שמירת רשימה
$clean_sitemap_data = [
    'cleanup_date' => date('Y-m-d H:i:s'),
    'redirects_to_remove' => $redirects_urls,
    'real_pages_to_keep' => array_column($real_pages, 'url'),
    'total_redirects' => count($redirects_urls),
    'total_real_pages' => count($real_pages)
];

$output_file = __DIR__ . '/../docs/testing/reports/clean-sitemap-data.json';
file_put_contents($output_file, json_encode($clean_sitemap_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "\nClean sitemap data saved to: $output_file\n";
echo "\n**Next Steps:**\n";
echo "1. Review the list of redirects to remove\n";
echo "2. Update Yoast SEO settings to exclude redirects from sitemap\n";
echo "3. Or manually remove redirects from sitemap\n";
