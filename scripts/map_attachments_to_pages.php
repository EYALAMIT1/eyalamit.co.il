<?php
/**
 * מיפוי קבצים (Attachments) לעמודים שמשתמשים בהם
 * 
 * מטרה: לסרוק את כל העמודים הפעילים ולאתר את הקבצים שבאמת נחוצים להם
 * 
 * פלט: עדכון CSV עם עמודה חדשה - "Used_In_Pages" שמכילה קישורים לעמודים שמשתמשים בקובץ
 */

// הגדרת base URL
$base_url = 'http://localhost:9090';

$csv_file = __DIR__ . '/../docs/sitemap/SITEMAP-v1.0-2026-01-14.csv';
$output_csv = __DIR__ . '/../docs/sitemap/SITEMAP-v1.0-2026-01-14-with-usage.csv';

// טעינת CSV
$lines = file($csv_file);
if (empty($lines)) {
    die("Error: Could not read CSV file\n");
}

$header = str_getcsv($lines[0], ",", "\"", "\\");
$header[] = "Used_In_Pages"; // הוספת עמודה חדשה

// יצירת מפה של כל ה-URLs (רק עמודים פעילים - לא קבצים)
$active_pages = [];
$attachments = [];

// קריאת כל השורות
for ($i = 1; $i < count($lines); $i++) {
    $fields = str_getcsv($lines[$i], ",", "\"", "\\");
    if (count($fields) >= 11) {
        $url = $fields[0];
        $content_type = $fields[1];
        $status = $fields[3];
        
        // זיהוי קבצים לפי URL או Content Type
        $is_attachment = false;
        $path = parse_url($url, PHP_URL_PATH);
        
        // בדיקה אם זה קובץ לפי URL
        if (preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|zip|mp3|mp4)$#i', $path) ||
            strpos($url, 'attachment_id=') !== false ||
            strpos($url, '/wp-content/uploads/') !== false ||
            $content_type === "Attachment") {
            $is_attachment = true;
        }
        
        // רק עמודים פעילים (לא קבצים)
        if (!$is_attachment && $status === "OK") {
            $active_pages[] = $url;
        }
        
        // רק קבצים
        if ($is_attachment) {
            $attachments[] = [
                'url' => $url,
                'row_index' => $i,
                'fields' => $fields
            ];
        }
    }
}

echo "Found " . count($active_pages) . " active pages\n";
echo "Found " . count($attachments) . " attachments\n";
echo "Scanning pages for attachments...\n\n";

// יצירת מפתחות חיפוש לכל קובץ
$attachment_search_keys = [];
foreach ($attachments as $attachment) {
    $attachment_url = $attachment['url'];
    $attachment_path = parse_url($attachment_url, PHP_URL_PATH);
    $attachment_filename = basename($attachment_path);
    
    // יצירת מפתחות חיפוש שונים
    $search_keys = [
        'full_url' => $attachment_url,
        'path' => $attachment_path,
        'filename' => $attachment_filename,
        'filename_no_ext' => pathinfo($attachment_filename, PATHINFO_FILENAME),
        'attachment_id' => null
    ];
    
    // חילוץ attachment_id אם יש
    if (preg_match('/attachment_id=(\d+)/', $attachment_url, $matches)) {
        $search_keys['attachment_id'] = $matches[1];
    }
    
    $attachment_search_keys[$attachment_url] = $search_keys;
}

// מיפוי קבצים לעמודים - סריקה יעילה יותר
$attachment_usage = [];
$page_cache = []; // cache לתוכן עמודים

$total_pages = count($active_pages);
$processed = 0;

foreach ($active_pages as $page_url) {
    $processed++;
    if ($processed % 50 === 0) {
        echo "Processed $processed / $total_pages pages...\n";
    }
    
    // טעינת תוכן העמוד
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $page_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Attachment Mapper)');
    
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($content === false || $http_code !== 200) {
        continue;
    }
    
    // חיפוש כל הקבצים בתוכן העמוד
    foreach ($attachment_search_keys as $attachment_url => $search_keys) {
        $found = false;
        
        // חיפוש לפי URL מלא
        if (strpos($content, $search_keys['full_url']) !== false) {
            $found = true;
        }
        // חיפוש לפי נתיב
        elseif (strpos($content, $search_keys['path']) !== false) {
            $found = true;
        }
        // חיפוש לפי שם קובץ
        elseif (strpos($content, $search_keys['filename']) !== false) {
            $found = true;
        }
        // חיפוש לפי attachment_id
        elseif ($search_keys['attachment_id'] && strpos($content, 'attachment_id=' . $search_keys['attachment_id']) !== false) {
            $found = true;
        }
        // חיפוש בתגיות img/src/href
        elseif (preg_match('#(src|href)=["\']([^"\']*' . preg_quote($search_keys['filename'], '#') . '[^"\']*)["\']#i', $content)) {
            $found = true;
        }
        
        if ($found) {
            if (!isset($attachment_usage[$attachment_url])) {
                $attachment_usage[$attachment_url] = [];
            }
            $attachment_usage[$attachment_url][] = $page_url;
        }
    }
}

// הדפסת תוצאות
foreach ($attachments as $attachment) {
    $attachment_url = $attachment['url'];
    $attachment_filename = basename(parse_url($attachment_url, PHP_URL_PATH));
    $used_in = $attachment_usage[$attachment_url] ?? [];
    
    if (count($used_in) > 0) {
        echo "✓ {$attachment_filename}: Found in " . count($used_in) . " page(s)\n";
    } else {
        echo "✗ {$attachment_filename}: Not found in any page\n";
    }
}

echo "\nGenerating updated CSV...\n";

// יצירת CSV מעודכן
$output_lines = [];
$output_lines[] = implode(",", array_map(function($field) {
    return "\"" . str_replace("\"", "\"\"", $field) . "\"";
}, $header));

// עדכון כל השורות
for ($i = 1; $i < count($lines); $i++) {
    $fields = str_getcsv($lines[$i], ",", "\"", "\\");
    $url = $fields[0];
    $content_type = $fields[1];
    
    // זיהוי אם זה קובץ
    $is_attachment = false;
    $path = parse_url($url, PHP_URL_PATH);
    if (preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|zip|mp3|mp4)$#i', $path) ||
        strpos($url, 'attachment_id=') !== false ||
        strpos($url, '/wp-content/uploads/') !== false ||
        $content_type === "Attachment") {
        $is_attachment = true;
    }
    
    // הוספת עמודה חדשה
    if ($is_attachment && isset($attachment_usage[$url])) {
        $used_in_pages = implode("; ", $attachment_usage[$url]);
        $fields[] = $used_in_pages;
    } else {
        $fields[] = ""; // ריק לעמודים שאינם קבצים
    }
    
    $output_lines[] = implode(",", array_map(function($field) {
        return "\"" . str_replace("\"", "\"\"", $field) . "\"";
    }, $fields));
}

// שמירת CSV מעודכן
file_put_contents($output_csv, implode("\n", $output_lines));

echo "Updated CSV saved to: $output_csv\n";

// יצירת דוח סיכום
$summary = [];
$summary['total_attachments'] = count($attachments);
$summary['attachments_with_usage'] = 0;
$summary['attachments_without_usage'] = 0;
$summary['total_pages_scanned'] = count($active_pages);

foreach ($attachment_usage as $url => $used_in) {
    if (count($used_in) > 0) {
        $summary['attachments_with_usage']++;
    } else {
        $summary['attachments_without_usage']++;
    }
}

echo "\n=== Summary ===\n";
echo "Total attachments: " . $summary['total_attachments'] . "\n";
echo "Attachments with usage: " . $summary['attachments_with_usage'] . "\n";
echo "Attachments without usage: " . $summary['attachments_without_usage'] . "\n";
echo "Total pages scanned: " . $summary['total_pages_scanned'] . "\n";

// שמירת דוח סיכום
$summary_file = __DIR__ . '/../docs/testing/reports/attachments-usage-summary.json';
file_put_contents($summary_file, json_encode([
    'summary' => $summary,
    'attachment_usage' => $attachment_usage
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\nSummary saved to: $summary_file\n";
