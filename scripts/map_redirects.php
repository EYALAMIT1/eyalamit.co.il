<?php
/**
 * מיפוי redirects ישנים וזיהוי עמודים אמיתיים
 * 
 * מטרה:
 * 1. לזהות מה הפניה של כתובת ישנה לדף חדש
 * 2. לזהות מה עמוד אמיתי ומה redirect
 * 3. ליצור מפה מסודרת של כל ה-redirects
 */

require_once __DIR__ . '/../wp-load.php';

$base_url = 'http://localhost:9090';
$sitemap_urls = [];

// טעינת כל ה-URLs מה-sitemap
$sitemap_index = $base_url . '/sitemap_index.xml';
$content = @file_get_contents($sitemap_index);

if ($content === false) {
    die("Error: Could not fetch sitemap\n");
}

$xml = simplexml_load_string($content);
if ($xml === false) {
    die("Error: Could not parse XML\n");
}

$all_urls = [];

// טעינת כל ה-URLs מה-sitemap
if (isset($xml->sitemap)) {
    foreach ($xml->sitemap as $sitemap) {
        $sub_sitemap_url = (string)$sitemap->loc;
        $sub_content = @file_get_contents($sub_sitemap_url);
        if ($sub_content !== false) {
            $sub_xml = simplexml_load_string($sub_content);
            if ($sub_xml !== false && isset($sub_xml->url)) {
                foreach ($sub_xml->url as $url) {
                    $all_urls[] = (string)$url->loc;
                }
            }
        }
    }
} elseif (isset($xml->url)) {
    foreach ($xml->url as $url) {
        $all_urls[] = (string)$url->loc;
    }
}

$all_urls = array_unique($all_urls);
sort($all_urls);

echo "Found " . count($all_urls) . " URLs in sitemap\n";
echo "Starting redirect mapping...\n\n";

$redirects_map = [];
$real_pages = [];
$errors = [];

foreach ($all_urls as $index => $url) {
    $progress = round((($index + 1) / count($all_urls)) * 100, 1);
    echo "[$progress%] Checking: $url ... ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // לא לעקוב אחרי redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code >= 200 && $http_code < 300 && empty($redirect_url)) {
        // עמוד אמיתי (לא redirect)
        $real_pages[] = [
            'url' => $url,
            'http_code' => $http_code,
            'final_url' => $final_url
        ];
        echo "✅ Real Page ($http_code)\n";
    } elseif ($http_code >= 300 && $http_code < 400) {
        // Redirect
        $redirects_map[] = [
            'from' => $url,
            'to' => $redirect_url ?: $final_url,
            'http_code' => $http_code,
            'type' => $http_code == 301 ? 'Permanent' : 'Temporary'
        ];
        echo "🔄 Redirect ($http_code) → " . ($redirect_url ?: $final_url) . "\n";
    } else {
        // Error
        $errors[] = [
            'url' => $url,
            'http_code' => $http_code,
            'error' => $error
        ];
        echo "❌ Error ($http_code)\n";
    }
}

echo "\n=== Summary ===\n";
echo "Total URLs: " . count($all_urls) . "\n";
echo "Real Pages: " . count($real_pages) . "\n";
echo "Redirects: " . count($redirects_map) . "\n";
echo "Errors: " . count($errors) . "\n";

// שמירת תוצאות
$output = [
    'mapping_date' => date('Y-m-d H:i:s'),
    'total_urls' => count($all_urls),
    'real_pages_count' => count($real_pages),
    'redirects_count' => count($redirects_map),
    'errors_count' => count($errors),
    'real_pages' => $real_pages,
    'redirects_map' => $redirects_map,
    'errors' => $errors
];

$output_file = __DIR__ . '/../docs/testing/reports/redirects-mapping.json';
file_put_contents($output_file, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "\nResults saved to: $output_file\n";

// יצירת דוח מפורט
$report_file = __DIR__ . '/../docs/testing/reports/redirects-mapping-report.md';
$report = "# דוח מיפוי Redirects\n\n";
$report .= "**תאריך:** " . date('Y-m-d H:i:s') . "\n\n";
$report .= "## סיכום\n\n";
$report .= "- **סה\"כ URLs:** " . count($all_urls) . "\n";
$report .= "- **עמודים אמיתיים:** " . count($real_pages) . "\n";
$report .= "- **Redirects:** " . count($redirects_map) . "\n";
$report .= "- **שגיאות:** " . count($errors) . "\n\n";

$report .= "## Redirects Map\n\n";
foreach ($redirects_map as $redirect) {
    $report .= "- **From:** `" . $redirect['from'] . "`\n";
    $report .= "  - **To:** `" . $redirect['to'] . "`\n";
    $report .= "  - **Type:** " . $redirect['type'] . " (" . $redirect['http_code'] . ")\n\n";
}

file_put_contents($report_file, $report);
echo "Report saved to: $report_file\n";
