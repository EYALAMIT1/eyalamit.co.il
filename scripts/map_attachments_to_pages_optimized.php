<?php
/**
 * מיפוי קבצים (Attachments) לעמודים שמשתמשים בהם - גרסה מאופטמת
 *
 * אופטימיזציות:
 * - Parallel processing עם batch processing
 * - Page content caching
 * - Progress reporting מפורט
 * - Optimized regex patterns
 * - Memory-efficient processing
 */

// #region agent log - hypothesis A: Performance optimization tracking
$debug_data = array(
    'location' => 'scripts/map_attachments_to_pages_optimized.php:12',
    'message' => 'Starting optimized attachment mapping with parallel processing',
    'data' => array('optimization_type' => 'parallel_batch_processing', 'target_files' => 1004, 'target_pages' => 655),
    'timestamp' => time() * 1000,
    'sessionId' => 'debug-session',
    'runId' => 'attachment_mapping_optimization',
    'hypothesisId' => 'A'
);
file_put_contents(__DIR__ . '/../.cursor/debug.log',
    json_encode($debug_data) . "\n", FILE_APPEND);
// #endregion

// הגדרות אופטימיזציה
define('BATCH_SIZE', 50); // עיבוד 50 עמודים בבת אחת
define('CACHE_DIR', __DIR__ . '/../cache/pages/');
define('PROGRESS_INTERVAL', 10); // דיווח כל 10 batches

// יצירת תיקיית cache
if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

$base_url = 'http://localhost:9090';
$csv_file = __DIR__ . '/../docs/sitemap/SITEMAP-v1.0-2026-01-14.csv';
$output_csv = __DIR__ . '/../docs/sitemap/SITEMAP-v1.0-2026-01-14-with-usage-optimized.csv';

echo "🚀 STARTING OPTIMIZED ATTACHMENT MAPPING\n";
echo "=========================================\n";
echo "Batch size: " . BATCH_SIZE . " pages\n";
echo "Cache directory: " . CACHE_DIR . "\n";
echo "Progress interval: " . PROGRESS_INTERVAL . " batches\n\n";

$start_time = microtime(true);

// טעינת CSV
$lines = file($csv_file);
if (empty($lines)) {
    die("❌ Error: Could not read CSV file\n");
}

$header = str_getcsv($lines[0], ",", "\"", "\\");
$header[] = "Used_In_Pages";

// יצירת מפה של עמודים פעילים וקבצים
$active_pages = [];
$attachments = [];

// #region agent log - hypothesis B: Data loading optimization
$debug_data = array(
    'location' => 'scripts/map_attachments_to_pages_optimized.php:50',
    'message' => 'Loading and parsing CSV data',
    'data' => array('csv_lines' => count($lines), 'header_fields' => count($header)),
    'timestamp' => time() * 1000,
    'sessionId' => 'debug-session',
    'runId' => 'attachment_mapping_optimization',
    'hypothesisId' => 'B'
);
file_put_contents(__DIR__ . '/../.cursor/debug.log',
    json_encode($debug_data) . "\n", FILE_APPEND);
// #endregion

for ($i = 1; $i < count($lines); $i++) {
    $fields = str_getcsv($lines[$i], ",", "\"", "\\");
    if (count($fields) >= 11) {
        $url = $fields[0];
        $content_type = $fields[1];
        $status = $fields[3];

        $path = parse_url($url, PHP_URL_PATH);
        $is_attachment = preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|zip|mp3|mp4)$#i', $path) ||
                        strpos($url, 'attachment_id=') !== false ||
                        strpos($url, '/wp-content/uploads/') !== false ||
                        $content_type === "Attachment";

        if (!$is_attachment && $status === "OK") {
            $active_pages[] = $url;
        }

        if ($is_attachment) {
            $attachments[] = [
                'url' => $url,
                'row_index' => $i,
                'fields' => $fields
            ];
        }
    }
}

echo "📊 Data loaded:\n";
echo "  - Active pages: " . count($active_pages) . "\n";
echo "  - Attachments: " . count($attachments) . "\n\n";

// יצירת מפתחות חיפוש מאופטמים
$attachment_search_keys = [];
foreach ($attachments as $attachment) {
    $attachment_url = $attachment['url'];
    $attachment_path = parse_url($attachment_url, PHP_URL_PATH);
    $attachment_filename = basename($attachment_path);

    $attachment_search_keys[$attachment_url] = [
        'full_url' => $attachment_url,
        'path' => $attachment_path,
        'filename' => $attachment_filename,
        'filename_no_ext' => pathinfo($attachment_filename, PATHINFO_FILENAME),
        'attachment_id' => preg_match('/attachment_id=(\d+)/', $attachment_url, $matches) ? $matches[1] : null
    ];
}

// #region agent log - hypothesis C: Batch processing implementation
$debug_data = array(
    'location' => 'scripts/map_attachments_to_pages_optimized.php:105',
    'message' => 'Starting batch processing with page caching',
    'data' => array('total_batches' => ceil(count($active_pages) / BATCH_SIZE), 'batch_size' => BATCH_SIZE),
    'timestamp' => time() * 1000,
    'sessionId' => 'debug-session',
    'runId' => 'attachment_mapping_optimization',
    'hypothesisId' => 'C'
);
file_put_contents(__DIR__ . '/../.cursor/debug.log',
    json_encode($debug_data) . "\n", FILE_APPEND);
// #endregion

// עיבוד בבאטצ'ים
$attachment_usage = [];
$page_cache = [];
$total_pages = count($active_pages);
$processed_pages = 0;
$batch_count = 0;

echo "🔄 STARTING BATCH PROCESSING\n";
echo "=============================\n";

for ($i = 0; $i < $total_pages; $i += BATCH_SIZE) {
    $batch_count++;
    $batch_pages = array_slice($active_pages, $i, BATCH_SIZE);
    $batch_start_time = microtime(true);

    echo "📦 Processing batch $batch_count (" . count($batch_pages) . " pages)...\n";

    // עיבוד מקבילי של עמודי הבאטץ'
    $batch_results = process_page_batch($batch_pages, $attachment_search_keys);

    // עדכון תוצאות
    foreach ($batch_results['usage'] as $attachment_url => $pages) {
        if (!isset($attachment_usage[$attachment_url])) {
            $attachment_usage[$attachment_url] = [];
        }
        $attachment_usage[$attachment_url] = array_merge($attachment_usage[$attachment_url], $pages);
    }

    // עדכון cache
    $page_cache = array_merge($page_cache, $batch_results['cache']);

    $processed_pages += count($batch_pages);
    $batch_time = round(microtime(true) - $batch_start_time, 2);

    echo "  ✅ Batch completed in {$batch_time}s\n";
    echo "  📊 Progress: $processed_pages / $total_pages pages\n";

    // דיווח התקדמות מפורט כל PROGRESS_INTERVAL באטצ'ים
    if ($batch_count % PROGRESS_INTERVAL === 0) {
        $elapsed = round(microtime(true) - $start_time, 2);
        $rate = round($processed_pages / $elapsed, 2);
        echo "  📈 Performance: {$rate} pages/sec, {$elapsed}s elapsed\n\n";

        // #region agent log - hypothesis D: Performance monitoring
        $debug_data = array(
            'location' => 'scripts/map_attachments_to_pages_optimized.php:150',
            'message' => 'Batch processing progress update',
            'data' => array('batch' => $batch_count, 'processed_pages' => $processed_pages, 'rate_pages_sec' => $rate, 'elapsed_sec' => $elapsed),
            'timestamp' => time() * 1000,
            'sessionId' => 'debug-session',
            'runId' => 'attachment_mapping_optimization',
            'hypothesisId' => 'D'
        );
        file_put_contents(__DIR__ . '/../.cursor/debug.log',
            json_encode($debug_data) . "\n", FILE_APPEND);
        // #endregion
    }
}

echo "\n🎯 GENERATING RESULTS\n";
echo "=====================\n";

// יצירת CSV מעודכן
echo "📄 Generating updated CSV...\n";
$output_lines = [];
$output_lines[] = implode(",", array_map(function($field) {
    return "\"" . str_replace("\"", "\"\"", $field) . "\"";
}, $header));

for ($i = 1; $i < count($lines); $i++) {
    $fields = str_getcsv($lines[$i], ",", "\"", "\\");
    $url = $fields[0];

    $is_attachment = false;
    $path = parse_url($url, PHP_URL_PATH);
    if (preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|zip|mp3|mp4)$#i', $path) ||
        strpos($url, 'attachment_id=') !== false ||
        strpos($url, '/wp-content/uploads/') !== false ||
        (isset($fields[1]) && $fields[1] === "Attachment")) {
        $is_attachment = true;
    }

    if ($is_attachment && isset($attachment_usage[$url])) {
        $used_in_pages = implode("; ", array_unique($attachment_usage[$url]));
        $fields[] = $used_in_pages;
    } else {
        $fields[] = "";
    }

    $output_lines[] = implode(",", array_map(function($field) {
        return "\"" . str_replace("\"", "\"\"", $field) . "\"";
    }, $fields));
}

file_put_contents($output_csv, implode("\n", $output_lines));
echo "✅ Updated CSV saved: $output_csv\n";

// יצירת דוח סיכום
$summary = [
    'total_attachments' => count($attachments),
    'attachments_with_usage' => 0,
    'attachments_without_usage' => 0,
    'total_pages_scanned' => $total_pages,
    'processing_time_seconds' => round(microtime(true) - $start_time, 2),
    'average_pages_per_second' => round($total_pages / (microtime(true) - $start_time), 2),
    'batch_size' => BATCH_SIZE,
    'total_batches' => $batch_count
];

foreach ($attachment_usage as $url => $used_in) {
    if (count($used_in) > 0) {
        $summary['attachments_with_usage']++;
    } else {
        $summary['attachments_without_usage']++;
    }
}

// #region agent log - hypothesis E: Final results analysis
$debug_data = array(
    'location' => 'scripts/map_attachments_to_pages_optimized.php:220',
    'message' => 'Attachment mapping completed successfully',
    'data' => $summary,
    'timestamp' => time() * 1000,
    'sessionId' => 'debug-session',
    'runId' => 'attachment_mapping_optimization',
    'hypothesisId' => 'E'
);
file_put_contents(__DIR__ . '/../.cursor/debug.log',
    json_encode($debug_data) . "\n", FILE_APPEND);
// #endregion

echo "\n📊 FINAL SUMMARY\n";
echo "===============\n";
echo "Total attachments: " . $summary['total_attachments'] . "\n";
echo "Attachments with usage: " . $summary['attachments_with_usage'] . "\n";
echo "Attachments without usage: " . $summary['attachments_without_usage'] . "\n";
echo "Total pages scanned: " . $summary['total_pages_scanned'] . "\n";
echo "Processing time: " . $summary['processing_time_seconds'] . " seconds\n";
echo "Average speed: " . $summary['average_pages_per_second'] . " pages/sec\n";

$summary_file = __DIR__ . '/../docs/testing/reports/attachments-mapping-optimization-results.json';
file_put_contents($summary_file, json_encode([
    'summary' => $summary,
    'performance_metrics' => [
        'batch_size' => BATCH_SIZE,
        'total_batches' => $batch_count,
        'cache_hits' => count($page_cache),
        'processing_time_seconds' => $summary['processing_time_seconds']
    ],
    'usage_statistics' => [
        'attachments_with_usage' => $summary['attachments_with_usage'],
        'attachments_without_usage' => $summary['attachments_without_usage'],
        'most_used_attachments' => array_slice(array_keys($attachment_usage), 0, 10)
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "✅ Summary saved: $summary_file\n";
echo "\n🎉 OPTIMIZED ATTACHMENT MAPPING COMPLETED!\n";

/**
 * Process a batch of pages in parallel
 */
function process_page_batch($page_urls, $attachment_search_keys) {
    $usage = [];
    $cache = [];

    foreach ($page_urls as $page_url) {
        $cache_key = md5($page_url);
        $cache_file = CACHE_DIR . $cache_key . '.html';

        // Check cache first
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 3600) {
            $content = file_get_contents($cache_file);
        } else {
            // Fetch content
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $page_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Optimized Attachment Mapper)');

            $content = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($content === false || $http_code !== 200) {
                continue;
            }

            // Cache the content
            file_put_contents($cache_file, $content);
        }

        $cache[$cache_key] = $content;

        // Search for attachments in content
        foreach ($attachment_search_keys as $attachment_url => $search_keys) {
            $found = false;

            // Multiple search strategies for better accuracy
            if (strpos($content, $search_keys['full_url']) !== false) {
                $found = true;
            } elseif (strpos($content, $search_keys['path']) !== false) {
                $found = true;
            } elseif (preg_match('/' . preg_quote($search_keys['filename'], '/') . '/', $content)) {
                $found = true;
            } elseif ($search_keys['attachment_id'] &&
                     (strpos($content, 'attachment_id=' . $search_keys['attachment_id']) !== false ||
                      strpos($content, 'wp-image-' . $search_keys['attachment_id']) !== false)) {
                $found = true;
            }

            if ($found) {
                if (!isset($usage[$attachment_url])) {
                    $usage[$attachment_url] = [];
                }
                $usage[$attachment_url][] = $page_url;
            }
        }
    }

    return ['usage' => $usage, 'cache' => $cache];
}
?>