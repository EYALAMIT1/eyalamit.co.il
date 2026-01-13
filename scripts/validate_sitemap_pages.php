<?php
/**
 * Validate all pages from sitemap
 * Checks HTTP status, response time, and errors
 * Usage: php validate_sitemap_pages.php
 */

$base_url = 'http://localhost:9090';
$sitemap_urls = [];

// Try WordPress Core Sitemap first
$sitemap_index = $base_url . '/sitemap.xml';
$content = @file_get_contents($sitemap_index);

if ($content === false) {
    // Try Yoast SEO Sitemap
    $sitemap_index = $base_url . '/sitemap_index.xml';
    $content = @file_get_contents($sitemap_index);
}

if ($content === false) {
    die("Error: Could not fetch sitemap\n");
}

// Parse sitemap index
$xml = simplexml_load_string($content);
if ($xml === false) {
    die("Error: Could not parse XML\n");
}

$all_urls = [];

// Check if this is a sitemap index (contains <sitemap> tags)
if (isset($xml->sitemap)) {
    // This is a sitemap index, fetch all sub-sitemaps
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
    // This is a direct sitemap
    foreach ($xml->url as $url) {
        $all_urls[] = (string)$url->loc;
    }
}

// Remove duplicates
$all_urls = array_unique($all_urls);
sort($all_urls);

echo "Found " . count($all_urls) . " URLs in sitemap\n";
echo "Starting validation...\n\n";

$results = [];
$success_count = 0;
$error_count = 0;

foreach ($all_urls as $index => $url) {
    $progress = round((($index + 1) / count($all_urls)) * 100, 1);
    echo "[$progress%] Checking: $url ... ";
    
    $start_time = microtime(true);
    
    // Use cURL for better control
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $error = curl_error($ch);
    curl_close($ch);
    
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2); // ms
    
    $status = 'success';
    if ($http_code >= 400 || !empty($error)) {
        $status = 'error';
        $error_count++;
    } else {
        $success_count++;
    }
    
    // Check for common issues in response
    $has_errors = false;
    $error_details = [];
    
    if ($http_code >= 400) {
        $has_errors = true;
        $error_details[] = "HTTP $http_code";
    }
    
    if (!empty($error)) {
        $has_errors = true;
        $error_details[] = "cURL Error: $error";
    }
    
    // Check response body for common error patterns
    if ($response !== false) {
        $body = substr($response, strpos($response, "\r\n\r\n") + 4);
        if (stripos($body, 'fatal error') !== false) {
            $has_errors = true;
            $error_details[] = "PHP Fatal Error detected";
        }
        if (stripos($body, 'database error') !== false) {
            $has_errors = true;
            $error_details[] = "Database Error detected";
        }
        if (stripos($body, '500 internal server error') !== false) {
            $has_errors = true;
            $error_details[] = "500 Error in body";
        }
    }
    
    $results[] = [
        'url' => $url,
        'http_code' => $http_code,
        'response_time_ms' => $response_time,
        'status' => $status,
        'has_errors' => $has_errors,
        'error_details' => $error_details,
        'size_bytes' => strlen($response)
    ];
    
    if ($status === 'success') {
        echo "✅ OK ($http_code, {$response_time}ms)\n";
    } else {
        echo "❌ ERROR ($http_code, " . implode(', ', $error_details) . ")\n";
    }
}

echo "\n";
echo "=== Validation Summary ===\n";
echo "Total URLs: " . count($all_urls) . "\n";
echo "Success: $success_count\n";
echo "Errors: $error_count\n";
echo "Success Rate: " . round(($success_count / count($all_urls)) * 100, 2) . "%\n";

// Output detailed results as JSON
$output_file = __DIR__ . '/../docs/testing/reports/sitemap-validation-results.json';
file_put_contents($output_file, json_encode([
    'validation_date' => date('Y-m-d H:i:s'),
    'sitemap_url' => $sitemap_index,
    'total_urls' => count($all_urls),
    'success_count' => $success_count,
    'error_count' => $error_count,
    'success_rate' => round(($success_count / count($all_urls)) * 100, 2),
    'results' => $results
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "\nDetailed results saved to: $output_file\n";
