<?php
/**
 * Extract all URLs from WordPress Sitemap
 * Usage: php extract_sitemap_urls.php
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
    die("Error: Could not fetch sitemap from $sitemap_index\n");
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
        echo "Fetching: $sub_sitemap_url\n";
        
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

// Remove duplicates and sort
$all_urls = array_unique($all_urls);
sort($all_urls);

// Output JSON
echo json_encode([
    'total_urls' => count($all_urls),
    'sitemap_url' => $sitemap_index,
    'urls' => $all_urls
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
