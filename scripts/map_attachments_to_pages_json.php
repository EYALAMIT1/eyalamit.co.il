<?php
/**
 * מיפוי קבצים (Attachments) לעמודים שמשתמשים בהם - פורמט JSON
 *
 * יצירת קובץ JSON עם מבנה ברור ומדויק:
 * - metadata על התהליך
 * - מערך של attachments עם מיפוי שימוש
 * - סטטיסטיקות וסיכום
 */

require_once '/var/www/html/wp-load.php';

echo "🔧 ATTACHMENTS MAPPING - JSON OUTPUT FORMAT\n";
echo "===========================================\n";

$csv_file = __DIR__ . '/../docs/sitemap/SITEMAP-v1.0-2026-01-14.csv';
$json_file = __DIR__ . '/../docs/sitemap/SITEMAP-v1.0-2026-01-14-usage-mapping.json';

echo "Processing CSV: $csv_file\n";
echo "Output JSON: $json_file\n\n";

// קריאת CSV
$lines = file($csv_file);
if (empty($lines)) {
    die("❌ Error: Could not read CSV file\n");
}

$header = str_getcsv($lines[0], ",", "\"", "\\");
echo "CSV Header: " . implode(", ", $header) . "\n";

// יצירת מבנה נתונים
$data = [
    'metadata' => [
        'generated_at' => date('c'),
        'generator' => 'Team 4 - Database Specialists',
        'source_csv' => basename($csv_file),
        'total_records' => count($lines) - 1,
        'processing_date' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'wordpress_version' => get_bloginfo('version')
    ],
    'attachments' => [],
    'pages' => [],
    'statistics' => [
        'total_attachments' => 0,
        'total_pages' => 0,
        'attachments_with_usage' => 0,
        'attachments_without_usage' => 0,
        'processing_time_seconds' => 0
    ]
];

$start_time = microtime(true);

// עיבוד שורות
$attachments = [];
$pages = [];

for ($i = 1; $i < count($lines); $i++) {
    $fields = str_getcsv($lines[$i], ",", "\"", "\\");

    if (count($fields) >= 11) {
        $record = array_combine($header, $fields);

        // זיהוי קבצים
        $is_attachment = false;
        $path = parse_url($record['URL'], PHP_URL_PATH);

        if (preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|zip|mp3|mp4)$#i', $path) ||
            strpos($record['URL'], 'attachment_id=') !== false ||
            strpos($record['URL'], '/wp-content/uploads/') !== false ||
            $record['Content_Type'] === "Attachment") {
            $is_attachment = true;
        }

        if ($is_attachment) {
            $attachment = [
                'id' => $i,
                'url' => $record['URL'],
                'filename' => basename(parse_url($record['URL'], PHP_URL_PATH)),
                'content_type' => $record['Content_Type'],
                'status' => $record['Status'],
                'size_bytes' => (int)$record['Size_Bytes'],
                'path' => $record['Path'],
                'first_path_segment' => $record['First_Path_Segment'],
                'used_in_pages' => [], // ימולא מאוחר יותר
                'usage_count' => 0
            ];

            $attachments[] = $attachment;
        } elseif ($record['Status'] === "OK") {
            $page = [
                'id' => $i,
                'url' => $record['URL'],
                'content_type' => $record['Content_Type'],
                'status' => $record['Status'],
                'http_code' => $record['HTTP_Code'],
                'response_time_ms' => (float)$record['Response_Time_MS'],
                'has_errors' => $record['Has_Errors'] === "Yes",
                'error_details' => $record['Error_Details'],
                'size_bytes' => (int)$record['Size_Bytes'],
                'path' => $record['Path'],
                'first_path_segment' => $record['First_Path_Segment']
            ];

            $pages[] = $page;
        }
    }
}

// מיפוי שימוש (פשוט - רק דף בית)
echo "Performing attachment mapping...\n";

$base_url = 'http://localhost:9090';

// סריקת דף הבית
$page_url = $base_url . '/';
$content = fetch_page_content($page_url);

if ($content !== false) {
    echo "✅ Successfully fetched homepage content (" . strlen($content) . " chars)\n";

    foreach ($attachments as &$attachment) {
        $found = false;

        // חיפוש לפי URL מלא
        if (strpos($content, $attachment['url']) !== false) {
            $found = true;
        }
        // חיפוש לפי נתיב
        elseif (strpos($content, $attachment['path']) !== false) {
            $found = true;
        }
        // חיפוש לפי שם קובץ
        elseif (strpos($content, $attachment['filename']) !== false) {
            $found = true;
        }

        if ($found) {
            $attachment['used_in_pages'][] = [
                'page_url' => $page_url,
                'found_by' => 'content_search'
            ];
            $attachment['usage_count']++;
        }
    }
} else {
    echo "❌ Failed to fetch homepage content\n";
}

// חישוב סטטיסטיקות
$data['attachments'] = $attachments;
$data['pages'] = $pages;
$data['statistics']['total_attachments'] = count($attachments);
$data['statistics']['total_pages'] = count($pages);

foreach ($attachments as $attachment) {
    if ($attachment['usage_count'] > 0) {
        $data['statistics']['attachments_with_usage']++;
    } else {
        $data['statistics']['attachments_without_usage']++;
    }
}

$data['statistics']['processing_time_seconds'] = round(microtime(true) - $start_time, 3);

// שמירת JSON
$json_content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (file_put_contents($json_file, $json_content)) {
    echo "\n✅ JSON file saved successfully: $json_file\n";
    echo "File size: " . filesize($json_file) . " bytes\n";
} else {
    echo "\n❌ Failed to save JSON file\n";
    exit(1);
}

// הצגת סיכום
echo "\n📊 PROCESSING SUMMARY\n";
echo "====================\n";
echo "Total attachments: " . $data['statistics']['total_attachments'] . "\n";
echo "Total pages: " . $data['statistics']['total_pages'] . "\n";
echo "Attachments with usage: " . $data['statistics']['attachments_with_usage'] . "\n";
echo "Attachments without usage: " . $data['statistics']['attachments_without_usage'] . "\n";
echo "Processing time: " . $data['statistics']['processing_time_seconds'] . " seconds\n";

echo "\n🎯 JSON ADVANTAGES DEMONSTRATED:\n";
echo "================================\n";
echo "✅ Structured data with clear hierarchy\n";
echo "✅ Full Unicode support (Hebrew text preserved)\n";
echo "✅ Type safety (numbers as numbers, not strings)\n";
echo "✅ Metadata included (generation info, stats)\n";
echo "✅ Easy querying and filtering\n";
echo "✅ Self-documenting format\n";
echo "✅ Validation-friendly structure\n";

echo "\n📄 JSON STRUCTURE PREVIEW:\n";
echo "=========================\n";
$preview = json_encode(array_slice($data, 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo substr($preview, 0, 500) . "...\n";

/**
 * Fetch page content with error handling
 */
function fetch_page_content($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Attachment Mapping Bot)');

    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code === 200) ? $content : false;
}
?>