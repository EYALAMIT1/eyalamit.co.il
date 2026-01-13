<?php
/**
 * Schema JSON-LD Implementation - Person and Specialist
 * Professional Schema markup for Eyal Amit Didgeridoo Therapy Center
 *
 * @package Bridge Child Theme
 * @version 1.0.0
 * @author Team 1 (Development)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Person Schema for Eyal Amit
 */
function ea_add_person_schema() {
    // Person Schema for Eyal Amit
    $person_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => 'אייל עמית',
        'alternateName' => 'Eyal Amit',
        'jobTitle' => 'מומחה לריפוי בדיגרידו ומורה נשימה מעגלית',
        'description' => 'מומחה בריפוי באמצעות דיגרידו, מטפל בנשימה מעגלית, מורה מוסמך לנשימה מעגלית בדיגרידו עם 25 שנות ניסיון',
        'url' => home_url('/'),
        'sameAs' => array(
            'https://www.instagram.com/eyalamit.co.il/',
            'https://www.youtube.com/channel/UCgEL2IVMZXY3mJtfJmdGP6Q',
            'https://www.facebook.com/didgeridoo.studio.eyal.amit'
        ),
        'knowsAbout' => array(
            'דיגרידו',
            'נשימה מעגלית',
            'ריפוי באמצעות דיגרידו',
            'טיפול בבעיות נשימה',
            'הפחתת לחץ וחרדה',
            'שחרור סטרס כרוני'
        ),
        'hasOccupation' => array(
            '@type' => 'Occupation',
            'name' => 'מומחה דיגרידו ומורה נשימה',
            'occupationLocation' => array(
                '@type' => 'Place',
                'address' => array(
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'פרדס חנה',
                    'addressCountry' => 'IL'
                )
            )
        ),
        'image' => array(
            '@type' => 'ImageObject',
            'url' => home_url('/wp-content/uploads/2016/08/fav.png'),
            'width' => 64,
            'height' => 64
        ),
        'telephone' => '052-4822842',
        'email' => 'info@eyalamit.co.il'
    );

    // Output Person schema with unique identifier
    echo '<!-- ea-person-schema -->' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($person_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'ea_add_person_schema', 5);

/**
 * Add HealthAndBeautyBusiness Schema for the therapy center
 */
function ea_add_specialist_schema() {
    // Specialist Schema
    $specialist_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'HealthAndBeautyBusiness',
        'name' => 'מרכז לטיפול בדיגרידו - סטודיו נשימה מעגלית',
        'alternateName' => 'Didgeridoo Therapy Center - Circular Breathing Studio',
        'description' => 'מרכז מקצועי לריפוי באמצעות דיגרידו ולמידת נשימה מעגלית. שיטת טיפול ייחודית עם 25 שנות ניסיון.',
        'url' => home_url('/'),
        'telephone' => '052-4822842',
        'email' => 'info@eyalamit.co.il',
        'address' => array(
            '@type' => 'PostalAddress',
            'streetAddress' => 'רחוב העצמאות 15',
            'addressLocality' => 'פרדס חנה',
            'addressRegion' => 'חיפה',
            'postalCode' => '3700000',
            'addressCountry' => 'IL'
        ),
        'geo' => array(
            '@type' => 'GeoCoordinates',
            'latitude' => '32.4723',
            'longitude' => '34.9828'
        ),
        'openingHours' => 'Mo-Su 08:00-20:00',
        'priceRange' => '₪₪₪',
        'paymentAccepted' => array('Cash', 'Credit Card', 'Bank Transfer'),
        'currenciesAccepted' => 'ILS',
        'serviceArea' => array(
            '@type' => 'Place',
            'name' => 'ישראל'
        ),
        'hasOfferCatalog' => array(
            '@type' => 'OfferCatalog',
            'name' => 'שירותי ריפוי בדיגרידו',
            'itemListElement' => array(
                array(
                    '@type' => 'Offer',
                    'itemOffered' => array(
                        '@type' => 'Service',
                        'name' => 'טיפול בדיגרידו',
                        'description' => 'טיפול ריפוי באמצעות דיגרידו ונשימה מעגלית'
                    )
                ),
                array(
                    '@type' => 'Offer',
                    'itemOffered' => array(
                        '@type' => 'Service',
                        'name' => 'שיעורי דיגרידו',
                        'description' => 'לימוד נגינה בדיגרידו ונשימה מעגלית'
                    )
                ),
                array(
                    '@type' => 'Offer',
                    'itemOffered' => array(
                        '@type' => 'Service',
                        'name' => 'סדנאות קבוצתיות',
                        'description' => 'סדנאות דיגרידו לקבוצות ויחידים'
                    )
                )
            )
        ),
        'founder' => array(
            '@type' => 'Person',
            'name' => 'אייל עמית'
        )
    );

    // Output Specialist schema with unique identifier
    echo '<!-- ea-specialist-schema -->' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($specialist_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'ea_add_specialist_schema', 6);

/**
 * Add FAQ Schema for homepage
 */
function ea_add_faq_schema() {
    // Only add FAQ schema on homepage
    if (!is_front_page()) {
        // Debug: log why FAQ schema is not loading
        return;
    }


    // FAQ questions about didgeridoo therapy
    $faq_questions = array(
        array(
            'question' => 'מה זה דיגרידו?',
            'answer' => 'דיגרידו הוא כלי נגינה עתיק יומין המקורי לאוסטרליה, המשמש גם ככלי ריפוי וטיפול. הנגינה בדיגרידו דורשת טכניקת נשימה מיוחדת הנקראת "נשימה מעגלית".'
        ),
        array(
            'question' => 'מה זה נשימה מעגלית?',
            'answer' => 'נשימה מעגלית היא טכניקת נשימה מיוחדת המאפשרת נגינה רציפה בדיגרידו ללא הפסקות. טכניקה זו משמשת גם ככלי טיפולי להפחתת לחץ, חרדה ושיפור בריאות הנשימה.'
        ),
        array(
            'question' => 'מי יכול ללמוד לנגן בדיגרידו?',
            'answer' => 'כל אדם יכול ללמוד לנגן בדיגרידו, ללא צורך בכישרון מוזיקלי קודם או גיל מסוים. השיטה מתאימה למבוגרים וילדים כאחד, ומתמקדת בלימוד טכניקת הנשימה המעגלית.'
        ),
        array(
            'question' => 'מהם היתרונות הטיפוליים של דיגרידו?',
            'answer' => 'הדיגרידו מספק יתרונות טיפוליים רבים: שיפור תפקודי הנשימה, הפחתת לחץ וחרדה, חיזוק שרירי הבטן והסרעפת, שיפור הריכוז והרוגע, וטיפול בבעיות נשימה שונות.'
        ),
        array(
            'question' => 'כמה זמן לוקח ללמוד לנגן בדיגרידו?',
            'answer' => 'רוב התלמידים לומדים את יסודות הנשימה המעגלית תוך 3-5 שיעורים. המשך הלמידה תלוי ברצון האישי והזמן המושקע בתרגול. מטרת השיטה היא להעניק כלים לכל החיים.'
        )
    );

    $faq_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array()
    );

    foreach ($faq_questions as $faq) {
        $faq_schema['mainEntity'][] = array(
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text' => $faq['answer']
            )
        );
    }

    // Output FAQ schema with unique identifier
    echo '<!-- ea-faq-schema -->' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'ea_add_faq_schema', 7);

/**
 * Debug function - log schema implementation status
 */
function ea_debug_schema_implementation() {
    if (isset($_GET['ea_debug_schema'])) {
        echo '<pre>';
        echo "Schema Implementation Debug:\n";
        echo "- ea_add_person_schema function: " . (function_exists('ea_add_person_schema') ? "EXISTS" : "MISSING") . "\n";
        echo "- ea_add_specialist_schema function: " . (function_exists('ea_add_specialist_schema') ? "EXISTS" : "MISSING") . "\n";
        echo "- ea_add_faq_schema function: " . (function_exists('ea_add_faq_schema') ? "EXISTS" : "MISSING") . "\n";
        echo "- Current page is front page: " . (is_front_page() ? "YES" : "NO") . "\n";
        echo '</pre>';
    }
}
add_action('wp_head', 'ea_debug_schema_implementation', 1);

/**
 * Test function for automated validation
 */
function ea_test_schema_implementation() {
    $results = array(
        'file_loaded' => true,
        'functions_exist' => array(
            'ea_add_person_schema' => function_exists('ea_add_person_schema'),
            'ea_add_specialist_schema' => function_exists('ea_add_specialist_schema'),
            'ea_add_faq_schema' => function_exists('ea_add_faq_schema')
        ),
        'hooks_registered' => array(
            'person_schema' => has_action('wp_head', 'ea_add_person_schema'),
            'specialist_schema' => has_action('wp_head', 'ea_add_specialist_schema'),
            'faq_schema' => has_action('wp_head', 'ea_add_faq_schema')
        )
    );

    return $results;
}
?>