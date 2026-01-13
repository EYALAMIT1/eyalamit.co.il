# Plugin Usage Analysis Report - 36 Active Plugins Deep Dive
**Date:** January 14, 2026
**Tester:** Team 2 (QA & Monitor)
**Status:** 🟡 ANALYSIS_COMPLETE - 33 Ghost Plugins Identified, 3 Active Plugins Mapped
**Reference:** Response to Team 3 (Gatekeeper) plugin analysis request

## Executive Summary

Comprehensive analysis of all 36 plugins marked as "active" in the WordPress database reveals a critical situation: **only 3 plugins actually exist physically and are actively used**, while **33 plugins are "ghost plugins"** - marked active in the database but non-existent on the filesystem.

**Critical Findings:**
- **Physical Plugins:** 3/36 (8.3%) - elementor, google-site-kit, wordpress-seo
- **Ghost Plugins:** 33/36 (91.7%) - Active in database but files don't exist
- **Active Usage:** Only WooCommerce, Envira Gallery, Yoast SEO, and Elementor show actual usage
- **Risk Level:** 🔴 HIGH - 33 ghost plugins can cause PHP errors and performance issues

## Database vs Filesystem Analysis

### Active Plugins in Database (36 total)
```
 1. LayerSlider                 19. js_composer
 2. admin-menu-editor           20. layouts
 3. akismet                     21. ltrrtl-admin-content
 4. contact-form-7              22. post-types-order
 5. disable-gutenberg           23. regenerate-thumbnails
 6. disable-wordpress-updates   24. simple-google-recaptcha
 7. duplicate-post              25. timetable
 8. elementor                   26. tiny-compress-images
 9. envato-market               27. toolset-maps
10. envato-wordpress-toolkit    28. types
11. envira-albums              29. woocommerce-gateway-paypal-express-checkout
12. envira-fullscreen          30. woocommerce-views
13. envira-gallery-themes      31. woocommerce
14. envira-gallery             32. wordpress-seo
15. envira-social              33. wp-accessibility-helper
16. envira-woocommerce         34. wp-rocket
17. google-site-kit            35. wp-user-avatar
18. hello-dolly                36. wp-views
```

### Physical Plugins Directory (3 existing)
- ✅ **elementor/** - 2,510 files
- ✅ **google-site-kit/** - 1,770 files
- ✅ **wordpress-seo/** - 2,046 files

### Ghost Plugins (33 missing)
**Envira Gallery Suite (6 plugins):**
- envira-albums, envira-fullscreen, envira-gallery-themes, envira-gallery, envira-social, envira-woocommerce

**Toolset Suite (3 plugins):**
- toolset-maps, types, wp-views

**WooCommerce Extensions (3 plugins):**
- woocommerce-gateway-paypal-express-checkout, woocommerce-views, wp-user-avatar

**Development/Management (9 plugins):**
- LayerSlider, admin-menu-editor, envato-market, envato-wordpress-toolkit, layouts, post-types-order, regenerate-thumbnails, wp-accessibility-helper

**Security/Performance (6 plugins):**
- akismet, contact-form-7, simple-google-recaptcha, tiny-compress-images, wp-rocket

**Content Management (4 plugins):**
- disable-gutenberg, disable-wordpress-updates, duplicate-post, hello-dolly

**RTL/Language (2 plugins):**
- js_composer, ltrrtl-admin-content

**Scheduling (1 plugin):**
- timetable

## Active Plugin Usage Analysis

### 1. WooCommerce - HEAVILY USED ✅
**Status:** Active and Critical
**Physical Files:** ❌ Missing
**Database Tables:** ✅ 10+ WooCommerce tables exist
**Usage Evidence:**
- 5+ WooCommerce pages (Cart, Checkout, My Account)
- Product post type with 5+ products
- WooCommerce shortcodes in 3 posts/pages: `[woocommerce_cart]`, `[woocommerce_checkout]`, `[woocommerce_my_account]`
- Theme integration: 30+ WooCommerce template files in Bridge theme
**Risk:** 🔴 CRITICAL - Core e-commerce functionality depends on this plugin

### 2. Envira Gallery - MODERATELY USED ✅
**Status:** Active and Used
**Physical Files:** ❌ Missing (all 6 Envira plugins missing)
**Database Tables:** ❌ None found
**Usage Evidence:**
- Envira shortcodes in 4 posts: `[envira-gallery id="16833"]`, `[envira-gallery id="17793"]`, etc.
- Used in content pages: "צבע בכחול וזרוק לים", "כושי בלאנטיס", "וכתבתָּ", "qr32 - נשימה מעגלית"
**Risk:** 🟡 MEDIUM - Gallery functionality broken on affected pages

### 3. Yoast SEO - HEAVILY USED ✅
**Status:** Active and Critical
**Physical Files:** ✅ Exists (wordpress-seo/)
**Database Tables:** ❌ None (uses options/postmeta)
**Usage Evidence:**
- Postmeta data on 190+ posts: `_yoast_wpseo_content_score`, `_yoast_wpseo_primary_category`
- SEO metadata on 30+ posts: descriptions, focus keywords, titles
- Primary categories set for 80+ posts
**Risk:** 🟢 LOW - Plugin exists and functional

### 4. Elementor - LIGHTLY USED ✅
**Status:** Active but Minimal Usage
**Physical Files:** ✅ Exists (elementor/)
**Database Tables:** ❌ None
**Usage Evidence:**
- Postmeta data on 2 posts: `_elementor_edit_mode`, `_elementor_css`
- Elementor transients in options table
**Risk:** 🟢 LOW - Plugin exists, minimal usage

### 5. Google Site Kit - UNKNOWN USAGE ⚠️
**Status:** Active but Usage Uncertain
**Physical Files:** ✅ Exists (google-site-kit/)
**Database Tables:** ❌ None
**Usage Evidence:**
- Google Site Kit transients in options table
- No clear content usage detected
**Risk:** 🟡 MEDIUM - May be configured for analytics but not verified

## Ghost Plugin Impact Assessment

### Immediate Risks
1. **PHP Fatal Errors:** Attempting to load non-existent plugin files
2. **Admin Dashboard Issues:** Broken plugin management interface
3. **Performance Degradation:** WordPress trying to load 33 missing plugins
4. **Update Conflicts:** WordPress trying to update non-existent plugins

### Functional Impact
- **E-commerce:** WooCommerce core missing - checkout/payments broken
- **Galleries:** Envira Gallery shortcodes not rendering on 4+ pages
- **SEO:** Yoast functionality intact (plugin exists)
- **Page Building:** Elementor intact but minimally used

## Specific Page Mapping

### WooCommerce Pages (Critical)
- **Cart Page (ID: 12035, 14804):** `[woocommerce_cart]` shortcode
- **Checkout Page (ID: 12036, 14805):** `[woocommerce_checkout]` shortcode
- **My Account (ID: 12037, 14806):** `[woocommerce_my_account]` shortcode
- **Product Posts:** 5 products exist in database

### Envira Gallery Pages (Broken)
- **"צבע בכחול וזרוק לים" (ID: 14866):** `[envira-gallery id="16833"]`
- **"כושי בלאנטיס" (ID: 15139):** `[envira-gallery id="16834"]`
- **"וכתבתָּ" (ID: 15140):** `[envira-gallery id="16704"]`
- **"qr32 - נשימה מעגלית" (ID: 15002):** `[envira-gallery id="17793"]`

### Elementor Pages (Functional)
- **2 posts** with Elementor edit mode enabled
- **Minimal usage** - likely not critical for site function

## Recommendations - Action Plan

### Phase 1: Critical Fixes (Immediate - 1-2 hours)
**Priority:** 🔴 URGENT

1. **Reinstall WooCommerce:**
   - Download and install WooCommerce plugin
   - Verify all e-commerce functionality restored
   - Test checkout process with existing products

2. **Reinstall Envira Gallery:**
   - Install Envira Gallery core plugin
   - Restore gallery functionality on affected pages
   - Verify shortcodes render correctly

3. **Clean Ghost Plugins:**
   - Deactivate all 33 missing plugins via database or WP-CLI
   - Remove from active_plugins array
   - Clear any related transients

### Phase 2: Optimization (Post-Critical - 2-4 hours)
**Priority:** 🟡 HIGH

1. **Audit Remaining Active Plugins:**
   - Verify Google Site Kit configuration and usage
   - Assess Elementor necessity vs usage
   - Check if any other "missing but needed" plugins exist

2. **Performance Cleanup:**
   - Remove unused plugin transients
   - Clean up orphaned postmeta data
   - Optimize autoload options

3. **Security Review:**
   - Ensure only necessary plugins remain active
   - Update all plugins to latest versions
   - Remove any potentially vulnerable ghost plugins

### Phase 3: Long-term Strategy (Planning)
**Priority:** 🟢 MEDIUM

1. **Plugin Management Policy:**
   - Implement proper plugin installation/removal procedures
   - Regular plugin audit schedule
   - Documentation of plugin dependencies

2. **Alternative Solutions:**
   - Consider native WordPress gallery features vs Envira
   - Evaluate lightweight e-commerce alternatives if WooCommerce issues persist
   - Implement proper backup/restore procedures for plugins

## Technical Implementation Notes

### Database Cleanup Commands
```sql
-- Deactivate all ghost plugins (example for one plugin)
UPDATE wp_options SET option_value = REPLACE(option_value, 'a:36:{i:0;s:27:"LayerSlider/layerslider.php";', 'a:3:{i:0;s:23:"elementor/elementor.php";') WHERE option_name = 'active_plugins';

-- Remove plugin transients
DELETE FROM wp_options WHERE option_name LIKE '%_transient_%LayerSlider%';
DELETE FROM wp_options WHERE option_name LIKE '%_transient_%envira%';
```

### Files to Monitor Post-Fix
- `/wp-content/debug.log` - PHP errors from missing plugins
- Browser console - JavaScript errors from broken shortcodes
- Site performance - Loading time improvements

## Conclusion

**This analysis reveals a critical plugin management issue:** 91.7% of "active" plugins are ghost plugins causing potential system instability. Only 4 plugins show actual usage (WooCommerce, Envira Gallery, Yoast SEO, Elementor), with WooCommerce being mission-critical for e-commerce functionality.

**Immediate Action Required:** Reinstall WooCommerce and Envira Gallery, clean up ghost plugins from the database.

**Expected Outcome:** Restored e-commerce functionality, working galleries, improved performance, eliminated PHP errors from missing plugins.

---

**Report Generated By:** Team 2 (QA & Monitor)
**Analysis Method:** Database queries, filesystem verification, content analysis, theme integration review
**Tools Used:** MySQL queries, file system analysis, content grep, WordPress database inspection
**Plugin Status:** 3 Physical / 33 Ghost / 4 Actively Used
**Risk Assessment:** 🔴 CRITICAL - Core functionality broken due to missing plugins