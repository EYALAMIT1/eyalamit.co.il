<?php
/**
 * יצירת קובץ CSV ממפת אתר
 * כולל כל הנתונים לניתוח
 */

$base_url = 'http://localhost:9090';
$sitemap_index = $base_url . '/sitemap_index.xml';

// טעינת validation results אם קיים
$validation_file = __DIR__ . '/../docs/testing/reports/sitemap-validation-results.json';
$validation_data = [];
if (file_exists($validation_file)) {
    $validation_json = json_decode(file_get_contents($validation_file), true);
    if (isset($validation_json['results'])) {
        foreach ($validation_json['results'] as $result) {
            $validation_data[$result['url']] = $result;
        }
    }
}

// טעינת sitemap
$content = @file_get_contents($sitemap_index);
if ($content === false) {
    die("Error: Could not fetch sitemap from $sitemap_index\n");
}

$xml = simplexml_load_string($content);
if ($xml === false) {
    die("Error: Could not parse XML\n");
}

$all_urls = [];
$attachment_sitemap_urls = []; // שמירת URLs מ-attachment sitemaps

// טעינת כל ה-URLs מה-sitemap
if (isset($xml->sitemap)) {
    foreach ($xml->sitemap as $sitemap) {
        $sub_sitemap_url = (string)$sitemap->loc;
        $is_attachment_sitemap = (strpos($sub_sitemap_url, 'attachment') !== false);
        
        $sub_content = @file_get_contents($sub_sitemap_url);
        if ($sub_content !== false) {
            $sub_xml = simplexml_load_string($sub_content);
            if ($sub_xml !== false && isset($sub_xml->url)) {
                foreach ($sub_xml->url as $url) {
                    $url_str = (string)$url->loc;
                    $all_urls[] = $url_str;
                    
                    // שמירת URLs מ-attachment sitemaps
                    if ($is_attachment_sitemap) {
                        $attachment_sitemap_urls[] = $url_str;
                    }
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

echo "Found " . count($all_urls) . " URLs\n";
echo "Creating CSV...\n";

// יצירת CSV
$csv = [];
$csv[] = "URL,Content_Type,Category,Status,HTTP_Code,Response_Time_MS,Has_Errors,Error_Details,Size_Bytes,Path,First_Path_Segment";

foreach ($all_urls as $url) {
    $path = parse_url($url, PHP_URL_PATH);
    $path_clean = trim($path, "/");
    $path_parts = explode("/", $path_clean);
    $first_segment = !empty($path_parts[0]) ? urldecode($path_parts[0]) : "";
    
    // זיהוי סוג תוכן
    $content_type = "Other";
    $category = "";
    
    // בדיקה אם זה URL מ-attachment sitemap
    $is_from_attachment_sitemap = in_array($url, $attachment_sitemap_urls);
    
    // זיהוי קבצים (Attachments) - קודם כל!
    if ($is_from_attachment_sitemap ||
        preg_match("#\.(jpg|jpeg|png|gif|webp|svg|pdf|doc|docx|zip|mp3|mp4)$#i", $path) ||
        strpos($url, 'attachment_id=') !== false ||
        strpos($url, '/wp-content/uploads/') !== false) {
        $content_type = "Attachment";
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ["jpg", "jpeg", "png", "gif", "webp", "svg"])) {
            $category = "Image";
        } elseif (!empty($ext)) {
            $category = "File";
        } else {
            // אם אין סיומת אבל יש attachment_id או זה נראה כמו עמוד קובץ - זה כנראה תמונה
            $category = "Image";
        }
    } elseif (empty($path_clean)) {
        $content_type = "Homepage";
        $category = "Homepage";
    } elseif (preg_match("#^Blog/#i", $path)) {
        $content_type = "Blog Post";
        $category = "Blog";
    } elseif (preg_match("#^shop/#i", $path)) {
        $content_type = "Shop";
        $category = "WooCommerce";
    } elseif (preg_match("#^qr/#i", $path)) {
        $content_type = "QR Code";
        $category = "QR";
    } elseif (preg_match("#portfolio#i", $path)) {
        $content_type = "Portfolio";
        $category = "Portfolio";
    } elseif (preg_match("#category|%d7%9e%d7%90%d7%92%d7%93|%d7%a7%d7%98%d7%92%d7%95%d7%a8%d7%99%d7%94#i", $path)) {
        $content_type = "Category";
        $category = "Category";
    } elseif (preg_match("#tag|%d7%aa%d7%92|%d7%aa%d7%92%d7%99%d7%aa#i", $path)) {
        $content_type = "Tag";
        $category = "Tag";
    } elseif (preg_match("#author|%d7%9e%d7%97%d7%91%d7%a8#i", $path)) {
        $content_type = "Author";
        $category = "Author";
    } elseif (preg_match("#testimonial#i", $path)) {
        $content_type = "Testimonial";
        $category = "Testimonial";
    } elseif (preg_match("#^[^/]+$#", $path_clean) && !preg_match("#\.(jpg|jpeg|png|gif|pdf|doc)#i", $path)) {
        $content_type = "Page";
        $category = "Page";
    }
    
    // נתוני בדיקה
    $validation_result = $validation_data[$url] ?? null;
    $status = "UNKNOWN";
    $http_code = "";
    $response_time = "";
    $has_errors = "";
    $error_details = "";
    $size_bytes = "";
    
    if ($validation_result) {
        $status = $validation_result["status"] === "success" ? "OK" : "ERROR";
        $http_code = $validation_result["http_code"] ?? "";
        $response_time = $validation_result["response_time_ms"] ?? "";
        $has_errors = $validation_result["has_errors"] ? "Yes" : "No";
        $error_details = implode("; ", $validation_result["error_details"] ?? []);
        $size_bytes = $validation_result["size_bytes"] ?? "";
    }
    
    // יצירת שורה ב-CSV
    $row = [
        $url,
        $content_type,
        $category,
        $status,
        $http_code,
        $response_time,
        $has_errors,
        str_replace(["\n", "\r", "\""], [" ", " ", "\"\""], $error_details),
        $size_bytes,
        $path,
        $first_segment
    ];
    
    $csv[] = implode(",", array_map(function($field) {
        return "\"" . str_replace("\"", "\"\"", $field) . "\"";
    }, $row));
}

$output_file = __DIR__ . '/../docs/sitemap/SITEMAP-v1.0-2026-01-14.csv';
file_put_contents($output_file, implode("\n", $csv));

echo "CSV created: " . count($csv) . " rows (including header)\n";
echo "File: $output_file\n";
echo "Size: " . filesize($output_file) . " bytes\n";
