<?php
$serialized = 'a:36:{i:0;s:27:"LayerSlider/layerslider.php";i:1;s:33:"admin-menu-editor/menu-editor.php";i:2;s:19:"akismet/akismet.php";i:3;s:36:"contact-form-7/wp-contact-form-7.php";i:4;s:39:"disable-gutenberg/disable-gutenberg.php";i:5;s:45:"disable-wordpress-updates/disable-updates.php";i:6;s:33:"duplicate-post/duplicate-post.php";i:7;s:23:"elementor/elementor.php";i:8;s:31:"envato-market/envato-market.php";i:9;s:34:"envato-wordpress-toolkit/index.php";i:10;s:31:"envira-albums/envira-albums.php";i:11;s:39:"envira-fullscreen/envira-fullscreen.php";i:12;s:47:"envira-gallery-themes/envira-gallery-themes.php";i:13;s:33:"envira-gallery/envira-gallery.php";i:14;s:31:"envira-social/envira-social.php";i:15;s:41:"envira-woocommerce/envira-woocommerce.php";i:16;s:35:"google-site-kit/google-site-kit.php";i:17;s:21:"hello-dolly/hello.php";i:18;s:27:"js_composer/js_composer.php";i:19;s:22:"layouts/dd-layouts.php";i:20;s:34:"ltrrtl-admin-content/ltr-admin.php";i:21;s:37:"post-types-order/post-types-order.php";i:22;s:47:"regenerate-thumbnails/regenerate-thumbnails.php";i:23;s:51:"simple-google-recaptcha/simple-google-recaptcha.php";i:24;s:23:"timetable/timetable.php";i:25;s:45:"tiny-compress-images/tiny-compress-images.php";i:26;s:36:"toolset-maps/toolset-maps-loader.php";i:27;s:14:"types/wpcf.php";i:28;s:91:"woocommerce-gateway-paypal-express-checkout/woocommerce-gateway-paypal-express-checkout.php";i:29;s:39:"woocommerce-views/views-woocommerce.php";i:30;s:27:"woocommerce/woocommerce.php";i:31;s:24:"wordpress-seo/wp-seo.php";i:32;s:51:"wp-accessibility-helper/wp-accessibility-helper.php";i:33;s:23:"wp-rocket/wp-rocket.php";i:34;s:33:"wp-user-avatar/wp-user-avatar.php";i:35;s:21:"wp-views/wp-views.php";}';

$plugins = unserialize($serialized);

echo "=== ACTIVE PLUGINS LIST (36 total) ===\n\n";
foreach ($plugins as $index => $plugin) {
    $plugin_name = dirname($plugin);
    echo sprintf("%2d. %s\n", $index + 1, $plugin_name);
}

echo "\n=== PLUGIN FILES ===\n\n";
foreach ($plugins as $index => $plugin) {
    echo sprintf("%2d. %s\n", $index + 1, $plugin);
}
?>