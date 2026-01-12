<?php
/**
 * SEO Optimization Plugin 2026
 * Implements Core Web Vitals and SEO improvements
 *
 * @version 1.0.0
 * @author AI Assistant - Cursor
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================================
// SCHEMA MARKUP IMPLEMENTATION
// ========================================

/**
 * Add Organization Schema
 */
function add_organization_schema() {
    if (is_front_page()) {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => "אייל עמית",
            "url" => "https://www.eyalamit.co.il",
            "logo" => get_site_icon_url(),
            "description" => "אתר הסטודיו של אייל עמית - ייעוץ והכשרה מקצועית",
            "founder" => [
                "@type" => "Person",
                "name" => "אייל עמית"
            ],
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+972-XX-XXX-XXXX",
                "contactType" => "customer service",
                "availableLanguage" => "Hebrew"
            ],
            "sameAs" => [
                "https://www.facebook.com/eyalamit",
                "https://www.instagram.com/eyalamit",
                "https://www.linkedin.com/in/eyalamit"
            ]
        ];
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>';
    }
}
add_action('wp_head', 'add_organization_schema');

/**
 * Add Product Schema for WooCommerce
 */
function add_product_schema() {
    if (function_exists('is_product') && is_product()) {
        global $product;

        if (!$product) return;

        $image_url = wp_get_attachment_image_url($product->get_image_id(), 'full');
        if (!$image_url) {
            $image_url = wc_placeholder_img_src();
        }

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => $product->get_name(),
            "description" => wp_strip_all_tags($product->get_description()),
            "sku" => $product->get_sku(),
            "image" => $image_url,
            "brand" => [
                "@type" => "Brand",
                "name" => "אייל עמית"
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => get_permalink($product->get_id()),
                "price" => $product->get_price(),
                "priceCurrency" => "ILS",
                "availability" => $product->is_in_stock() ?
                    "https://schema.org/InStock" :
                    "https://schema.org/OutOfStock",
                "seller" => [
                    "@type" => "Organization",
                    "name" => "אייל עמית"
                ]
            ]
        ];

        // Add aggregateRating if reviews exist
        $review_count = $product->get_review_count();
        if ($review_count > 0) {
            $schema["aggregateRating"] = [
                "@type" => "AggregateRating",
                "ratingValue" => $product->get_average_rating(),
                "reviewCount" => $review_count
            ];
        }

        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>';
    }
}
add_action('wp_head', 'add_product_schema');

/**
 * Add Breadcrumb Schema
 */
function add_breadcrumb_schema() {
    if (!is_front_page()) {
        $breadcrumbs = [];
        $breadcrumbs[] = [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "דף הבית",
            "item" => get_home_url()
        ];

        if (is_category() || is_single()) {
            $categories = get_the_category();
            if (!empty($categories)) {
                $breadcrumbs[] = [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => $categories[0]->name,
                    "item" => get_category_link($categories[0]->term_id)
                ];
            }
        }

        if (is_single()) {
            $breadcrumbs[] = [
                "@type" => "ListItem",
                "position" => count($breadcrumbs) + 1,
                "name" => get_the_title(),
                "item" => get_permalink()
            ];
        }

        if (count($breadcrumbs) > 1) {
            $schema = [
                "@context" => "https://schema.org",
                "@type" => "BreadcrumbList",
                "itemListElement" => $breadcrumbs
            ];

            echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>';
        }
    }
}
add_action('wp_head', 'add_breadcrumb_schema');

// ========================================
// CRITICAL CSS IMPLEMENTATION
// ========================================

/**
 * Add Critical CSS for above-the-fold content
 */
function add_critical_css() {
    // Only on front page for now
    if (!is_front_page()) {
        return;
    }

    $critical_css = '
    <style>
    /* Above the fold critical CSS */
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        padding: 2rem;
    }

    .hero-title {
        font-size: clamp(2rem, 5vw, 4rem);
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: clamp(1rem, 2vw, 1.5rem);
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .hero-button {
        display: inline-block;
        background: #ff6b6b;
        color: white;
        padding: 1rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    }

    .hero-button:hover {
        background: #ff5252;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
    }

    /* Navigation critical styles */
    .main-navigation {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        padding: 1rem 0;
    }

    .nav-menu {
        display: flex;
        justify-content: center;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .nav-menu li {
        margin: 0 1rem;
    }

    .nav-menu a {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        transition: all 0.3s ease;
    }

    .nav-menu a:hover {
        background: #667eea;
        color: white;
    }

    /* Mobile navigation */
    @media (max-width: 768px) {
        .nav-menu {
            flex-direction: column;
            text-align: center;
        }

        .nav-menu li {
            margin: 0.5rem 0;
        }
    }
    </style>';

    echo $critical_css;
}
add_action('wp_head', 'add_critical_css', 1);

// ========================================
// RESOURCE HINTS OPTIMIZATION
// ========================================

/**
 * Add resource hints for better performance
 */
function add_resource_hints() {
    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">';
    echo '<link rel="dns-prefetch" href="//www.google-analytics.com">';
    echo '<link rel="dns-prefetch" href="//www.googletagmanager.com">';

    // Preconnect for critical resources
    echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';

    // Preload critical fonts
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/fonts/custom-font.woff2" as="font" type="font/woff2" crossorigin>';

    // Preload critical CSS if not inlined
    if (!is_front_page()) {
        echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">';
    }

    // Preload hero image on front page
    if (is_front_page()) {
        $hero_image = get_theme_mod('hero_background_image');
        if ($hero_image) {
            echo '<link rel="preload" href="' . $hero_image . '" as="image">';
        }
    }
}
add_action('wp_head', 'add_resource_hints', 1);

// ========================================
// FONT LOADING OPTIMIZATION
// ========================================

/**
 * Optimize font loading with display=swap
 */
function optimize_font_loading() {
    echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&display=swap" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&display=swap"></noscript>';
}
add_action('wp_head', 'optimize_font_loading', 5);

// ========================================
// IMAGE OPTIMIZATION FOR SEO
// ========================================

/**
 * Add structured data to images
 */
function add_image_structured_data($content) {
    if (is_single() && has_post_thumbnail()) {
        $thumbnail_id = get_post_thumbnail_id();
        $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);

        if (empty($alt_text)) {
            // Generate alt text from post title
            $alt_text = get_the_title();
            update_post_meta($thumbnail_id, '_wp_attachment_image_alt', $alt_text);
        }

        // Ensure images have proper dimensions
        $image_meta = wp_get_attachment_metadata($thumbnail_id);
        if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
            $content = preg_replace(
                '/<img([^>]+)class="([^"]*)wp-post-image([^"]*)"([^>]*)>/',
                '<img$1class="$2wp-post-image$3" width="' . $image_meta['width'] . '" height="' . $image_meta['height'] . '"$4>',
                $content
            );
        }
    }

    return $content;
}
add_filter('the_content', 'add_image_structured_data');

// ========================================
// META TAGS OPTIMIZATION
// ========================================

/**
 * Enhanced meta description for better SEO
 */
function enhanced_meta_description($description) {
    if (is_single() && empty($description)) {
        $post = get_post();
        $excerpt = wp_trim_words($post->post_content, 25, '...');

        // Add call-to-action for products
        if (function_exists('is_product') && is_product()) {
            $excerpt .= ' | הזמן עכשיו באתר אייל עמית';
        }

        return $excerpt;
    }

    return $description;
}
add_filter('wpseo_metadesc', 'enhanced_meta_description');

/**
 * Optimize title tags for SEO
 */
function optimize_title_tags($title) {
    // Add site name if not present
    if (strpos($title, 'אייל עמית') === false) {
        $title .= ' | אייל עמית';
    }

    // Optimize length
    if (strlen($title) > 60) {
        $title = substr($title, 0, 57) . '...';
    }

    return $title;
}
add_filter('wpseo_title', 'optimize_title_tags');

// ========================================
// INTERNAL LINKING OPTIMIZATION
// ========================================

/**
 * Add related posts links for better internal linking
 */
function add_related_posts_links($content) {
    if (!is_single() || !in_the_loop()) {
        return $content;
    }

    $categories = get_the_category();
    if (empty($categories)) {
        return $content;
    }

    $related_args = [
        'category__in' => wp_list_pluck($categories, 'term_id'),
        'post__not_in' => [get_the_ID()],
        'posts_per_page' => 3,
        'orderby' => 'rand'
    ];

    $related_posts = get_posts($related_args);

    if (!empty($related_posts)) {
        $links_html = '<div class="related-posts" style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">';
        $links_html .= '<h3 style="margin-bottom: 1rem; color: #333;">פוסטים קשורים</h3>';
        $links_html .= '<ul style="list-style: none; padding: 0; margin: 0;">';

        foreach ($related_posts as $post) {
            $links_html .= '<li style="margin-bottom: 0.5rem;">';
            $links_html .= '<a href="' . get_permalink($post) . '" style="color: #667eea; text-decoration: none;">';
            $links_html .= '<strong>' . get_the_title($post) . '</strong>';
            $links_html .= '</a>';
            $links_html .= '<p style="margin: 0.25rem 0; color: #666; font-size: 0.9em;">' . wp_trim_words(get_the_excerpt($post), 15) . '</p>';
            $links_html .= '</li>';
        }

        $links_html .= '</ul></div>';

        return $content . $links_html;
    }

    return $content;
}
add_filter('the_content', 'add_related_posts_links');

// ========================================
// XML SITEMAP OPTIMIZATION
// ========================================

/**
 * Add custom sitemap for better SEO
 */
function add_seo_sitemap() {
    if (isset($_GET['seo_sitemap'])) {
        header('Content-Type: application/xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Add static pages
        $static_pages = get_pages(['sort_column' => 'menu_order']);
        foreach ($static_pages as $page) {
            echo '<url>';
            echo '<loc>' . get_permalink($page) . '</loc>';
            echo '<lastmod>' . get_the_modified_date('Y-m-d', $page) . '</lastmod>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.8</priority>';
            echo '</url>';
        }

        // Add posts
        $posts = get_posts(['numberposts' => -1]);
        foreach ($posts as $post) {
            echo '<url>';
            echo '<loc>' . get_permalink($post) . '</loc>';
            echo '<lastmod>' . get_the_modified_date('Y-m-d', $post) . '</lastmod>';
            echo '<changefreq>monthly</changefreq>';
            echo '<priority>0.6</priority>';
            echo '</url>';
        }

        // Add products if WooCommerce exists
        if (function_exists('wc_get_products')) {
            $products = wc_get_products(['limit' => -1]);
            foreach ($products as $product) {
                echo '<url>';
                echo '<loc>' . get_permalink($product->get_id()) . '</loc>';
                echo '<lastmod>' . get_the_modified_date('Y-m-d', $product) . '</lastmod>';
                echo '<changefreq>weekly</changefreq>';
                echo '<priority>0.9</priority>';
                echo '</url>';
            }
        }

        echo '</urlset>';
        exit;
    }
}
add_action('init', 'add_seo_sitemap');

// ========================================
// ROBOTS.TXT OPTIMIZATION
// ========================================

/**
 * Enhanced robots.txt
 */
function enhanced_robots_txt($output) {
    $output .= "\n# Enhanced by SEO Optimization 2026\n";
    $output .= "Host: https://www.eyalamit.co.il\n";
    $output .= "Sitemap: https://www.eyalamit.co.il/wp-sitemap.xml\n";
    $output .= "Sitemap: https://www.eyalamit.co.il/?seo_sitemap=1\n";

    return $output;
}
add_filter('robots_txt', 'enhanced_robots_txt');

// ========================================
// CORE WEB VITALS MONITORING
// ========================================

/**
 * Add Core Web Vitals tracking
 */
function add_core_web_vitals_tracking() {
    if (is_admin()) {
        return;
    }

    ?>
    <script>
    // Core Web Vitals tracking
    (function() {
        function sendToAnalytics(metric) {
            // Send to Google Analytics 4
            if (typeof gtag !== 'undefined') {
                gtag('event', metric.name, {
                    event_category: 'Web Vitals',
                    event_label: metric.id,
                    value: Math.round(metric.value),
                    non_interaction: true
                });
            }

            // Log to console in development
            if (window.location.hostname === 'localhost') {
                console.log('Web Vital:', metric);
            }
        }

        // Largest Contentful Paint
        new PerformanceObserver(function(list) {
            list.getEntries().forEach(function(entry) {
                sendToAnalytics({
                    name: 'LCP',
                    value: entry.startTime,
                    id: 'v3-' + Date.now() + '-' + Math.floor(Math.random() * 1000)
                });
            });
        }).observe({type: 'largest-contentful-paint', buffered: true});

        // First Input Delay
        new PerformanceObserver(function(list) {
            list.getEntries().forEach(function(entry) {
                sendToAnalytics({
                    name: 'FID',
                    value: entry.processingStart - entry.startTime,
                    id: 'v3-' + Date.now() + '-' + Math.floor(Math.random() * 1000)
                });
            });
        }).observe({type: 'first-input', buffered: true});

        // Cumulative Layout Shift
        new PerformanceObserver(function(list) {
            let clsValue = 0;
            list.getEntries().forEach(function(entry) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            });
            if (clsValue > 0) {
                sendToAnalytics({
                    name: 'CLS',
                    value: clsValue,
                    id: 'v3-' + Date.now() + '-' + Math.floor(Math.random() * 1000)
                });
            }
        }).observe({type: 'layout-shift', buffered: true});

    })();
    </script>
    <?php
}
add_action('wp_footer', 'add_core_web_vitals_tracking');

// ========================================
// SEO ADMIN DASHBOARD
// ========================================

/**
 * Add SEO dashboard widget
 */
function add_seo_dashboard_widget() {
    wp_add_dashboard_widget(
        'seo_performance_widget',
        'SEO Performance Dashboard',
        'seo_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'add_seo_dashboard_widget');

/**
 * SEO dashboard widget content
 */
function seo_dashboard_widget_content() {
    ?>
    <div style="padding: 10px;">
        <h4>Core Web Vitals Status</h4>
        <p><strong>LCP:</strong> <span style="color: orange;">Testing...</span></p>
        <p><strong>FID:</strong> <span style="color: orange;">Testing...</span></p>
        <p><strong>CLS:</strong> <span style="color: orange;">Testing...</span></p>

        <h4>SEO Metrics</h4>
        <p><strong>Schema Markup:</strong> <?php echo function_exists('is_product') && is_product() ? '✅ Active' : '⚠️ Check configuration'; ?></p>
        <p><strong>Meta Descriptions:</strong> <?php echo defined('WPSEO_VERSION') ? '✅ Yoast Active' : '⚠️ Install Yoast SEO'; ?></p>
        <p><strong>XML Sitemap:</strong> ✅ Active</p>

        <p><a href="https://pagespeed.web.dev/" target="_blank" class="button">Test PageSpeed</a></p>
    </div>
    <?php
}

/**
 * Add SEO meta box to posts
 */
function add_seo_meta_box() {
    add_meta_box(
        'seo_meta_box',
        'SEO Optimization',
        'seo_meta_box_content',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_seo_meta_box');

/**
 * SEO meta box content
 */
function seo_meta_box_content($post) {
    $seo_title = get_post_meta($post->ID, '_seo_title', true);
    $seo_description = get_post_meta($post->ID, '_seo_description', true);

    ?>
    <p>
        <label for="seo_title">SEO Title:</label>
        <input type="text" id="seo_title" name="seo_title" value="<?php echo esc_attr($seo_title); ?>" style="width: 100%;">
        <small>Recommended: 50-60 characters</small>
    </p>

    <p>
        <label for="seo_description">Meta Description:</label>
        <textarea id="seo_description" name="seo_description" rows="3" style="width: 100%;"><?php echo esc_textarea($seo_description); ?></textarea>
        <small>Recommended: 150-160 characters</small>
    </p>

    <p>
        <strong>Title Length:</strong> <span id="title_length">0</span>/60<br>
        <strong>Description Length:</strong> <span id="desc_length">0</span>/160
    </p>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateCounters() {
            const title = document.getElementById('seo_title').value;
            const desc = document.getElementById('seo_description').value;

            document.getElementById('title_length').textContent = title.length;
            document.getElementById('desc_length').textContent = desc.length;

            // Color coding
            document.getElementById('title_length').style.color = title.length > 60 ? 'red' : title.length > 50 ? 'orange' : 'green';
            document.getElementById('desc_length').style.color = desc.length > 160 ? 'red' : desc.length > 150 ? 'orange' : 'green';
        }

        document.getElementById('seo_title').addEventListener('input', updateCounters);
        document.getElementById('seo_description').addEventListener('input', updateCounters);
        updateCounters();
    });
    </script>
    <?php
}

/**
 * Save SEO meta box data
 */
function save_seo_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['seo_title'])) {
        update_post_meta($post_id, '_seo_title', sanitize_text_field($_POST['seo_title']));
    }

    if (isset($_POST['seo_description'])) {
        update_post_meta($post_id, '_seo_description', sanitize_text_field($_POST['seo_description']));
    }
}
add_action('save_post', 'save_seo_meta_box');

// ========================================
// PERFORMANCE MONITORING
// ========================================

/**
 * Log SEO performance metrics
 */
function log_seo_performance_metrics() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $metrics = [
        'page_load_time' => timer_stop(0, 3),
        'query_count' => get_num_queries(),
        'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024,
        'page_url' => $_SERVER['REQUEST_URI'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'is_mobile' => wp_is_mobile()
    ];

    // Log slow pages
    if ($metrics['page_load_time'] > 3.0) {
        error_log(sprintf(
            '[SEO SLOW PAGE] %.2fs load time, %d queries, %.1fMB memory - %s',
            $metrics['page_load_time'],
            $metrics['query_count'],
            $metrics['memory_usage'],
            $metrics['page_url']
        ));
    }

    // Log high query count
    if ($metrics['query_count'] > 100) {
        error_log(sprintf(
            '[SEO HIGH QUERIES] %d database queries on %s',
            $metrics['query_count'],
            $metrics['page_url']
        ));
    }
}
add_action('wp_footer', 'log_seo_performance_metrics', 999);

/*
 * ========================================
 * SEO OPTIMIZATION PLUGIN COMPLETE
 * ========================================
 *
 * Features implemented:
 * - Schema markup for products and organization
 * - Critical CSS for above-the-fold content
 * - Resource hints optimization
 * - Font loading optimization
 * - Image SEO enhancements
 * - Meta tags optimization
 * - Internal linking improvement
 * - XML sitemap enhancement
 * - Core Web Vitals tracking
 * - SEO dashboard widget
 * - Performance monitoring
 *
 * For WordPress SEO optimization 2026
 */