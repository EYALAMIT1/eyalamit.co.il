<?php
/**
 * ניתוח מפורט של בעיות במפת אתר
 */

$data = json_decode(file_get_contents(__DIR__ . '/../docs/testing/reports/sitemap-validation-results.json'), true);
$errors = array_filter($data["results"], function($r) { return $r["status"] === "error"; });

// ניתוח מפורט
$analysis = [
    'by_error_type' => [],
    'by_path' => [],
    'by_http_code' => [],
    'by_url_pattern' => [],
    'redirects_301' => [],
    'other_errors' => []
];

foreach ($errors as $error) {
    $url = $error["url"];
    $path = parse_url($url, PHP_URL_PATH);
    $parts = array_filter(explode("/", trim($path, "/")));
    
    // HTTP Code
    $code = $error["http_code"];
    if (!isset($analysis['by_http_code'][$code])) {
        $analysis['by_http_code'][$code] = [];
    }
    $analysis['by_http_code'][$code][] = [
        'url' => $url,
        'error_details' => $error["error_details"]
    ];
    
    // סוג בעיה
    $error_type = implode(", ", $error["error_details"]);
    if (!isset($analysis['by_error_type'][$error_type])) {
        $analysis['by_error_type'][$error_type] = [];
    }
    $analysis['by_error_type'][$error_type][] = $url;
    
    // לפי נתיב ראשון
    if (count($parts) > 0) {
        $first_part = $parts[0];
        if (!isset($analysis['by_path'][$first_part])) {
            $analysis['by_path'][$first_part] = [];
        }
        $analysis['by_path'][$first_part][] = $url;
    }
    
    // 301 Redirects
    if ($code == 301) {
        $analysis['redirects_301'][] = [
            'url' => $url,
            'path' => $path,
            'error_details' => $error["error_details"]
        ];
    } else {
        $analysis['other_errors'][] = [
            'url' => $url,
            'path' => $path,
            'http_code' => $code,
            'error_details' => $error["error_details"]
        ];
    }
    
    // דפוסי URL
    if (preg_match('/^\/[^\/]+/', $path, $matches)) {
        $pattern = $matches[0];
        if (!isset($analysis['by_url_pattern'][$pattern])) {
            $analysis['by_url_pattern'][$pattern] = [];
        }
        $analysis['by_url_pattern'][$pattern][] = $url;
    }
}

// מיון
arsort($analysis['by_path']);
arsort($analysis['by_url_pattern']);

// שמירה
file_put_contents(
    __DIR__ . '/../docs/testing/reports/sitemap-errors-analysis.json',
    json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);

echo "ניתוח הושלם. תוצאות נשמרו ב: docs/testing/reports/sitemap-errors-analysis.json\n";
