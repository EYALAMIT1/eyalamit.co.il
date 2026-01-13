<?php
/**
 * DEBUG CSV PARSING - Check page identification logic
 */

$csv_file = __DIR__ . '/docs/sitemap/SITEMAP-v1.0-2026-01-14.csv';

// טעינת CSV
$lines = file($csv_file);
if (empty($lines)) {
    die("Error: Could not read CSV file\n");
}

$header = str_getcsv($lines[0], ",", "\"", "\\");
echo "CSV Header: " . implode(", ", $header) . "\n\n";

$active_pages = [];
$attachments = [];
$errors = [];

echo "Analyzing first 20 lines:\n";
echo "======================\n";

for ($i = 1; $i < min(21, count($lines)); $i++) {
    $fields = str_getcsv($lines[$i], ",", "\"", "\\");
    if (count($fields) >= 11) {
        $url = $fields[0];
        $content_type = $fields[1];
        $status = $fields[3];

        echo "Line $i: URL=$url, Content_Type=$content_type, Status=$status\n";

        // בדיקת קובץ
        $path = parse_url($url, PHP_URL_PATH);
        $is_attachment = false;

        // בדיקה אם זה קובץ לפי URL
        if (preg_match('#\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|zip|mp3|mp4)$#i', $path) ||
            strpos($url, 'attachment_id=') !== false ||
            strpos($url, '/wp-content/uploads/') !== false ||
            $content_type === "Attachment") {
            $is_attachment = true;
            echo "  → IDENTIFIED AS ATTACHMENT\n";
        } else {
            echo "  → NOT AN ATTACHMENT\n";
        }

        // בדיקה אם זה עמוד פעיל
        if (!$is_attachment && $status === "OK") {
            $active_pages[] = $url;
            echo "  → ADDED AS ACTIVE PAGE\n";
        } elseif ($is_attachment) {
            $attachments[] = $url;
            echo "  → ADDED AS ATTACHMENT\n";
        } else {
            echo "  → SKIPPED (Status: $status, IsAttachment: " . ($is_attachment ? 'YES' : 'NO') . ")\n";
        }

        echo "\n";
    }
}

echo "SUMMARY:\n";
echo "========\n";
echo "Active pages found: " . count($active_pages) . "\n";
echo "Attachments found: " . count($attachments) . "\n";
echo "Total lines processed: " . (count($lines) - 1) . "\n";
?>