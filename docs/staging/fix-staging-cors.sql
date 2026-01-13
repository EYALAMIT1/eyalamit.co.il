-- ===========================================
-- EMERGENCY FIX: CORS Policy Issues in Staging
-- ===========================================
-- Problem: Resources loading from localhost:9090 instead of staging URL
-- Target: http://eyalamit-co-il-2026.s887.upress.link
-- Date: 2026-01-14
-- ===========================================

-- CRITICAL: Replace all localhost:9090 references
UPDATE wp_posts SET post_content = REPLACE(post_content, 'http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link');
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link');
UPDATE wp_options SET option_value = REPLACE(option_value, 'http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link') WHERE option_name LIKE '%url%';
UPDATE wp_usermeta SET meta_value = REPLACE(meta_value, 'http://localhost:9090', 'http://eyalamit-co-il-2026.s887.upress.link') WHERE meta_key LIKE '%url%';

-- Also fix any remaining localhost references (general)
UPDATE wp_posts SET post_content = REPLACE(post_content, 'http://localhost', 'http://eyalamit-co-il-2026.s887.upress.link');
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'http://localhost', 'http://eyalamit-co-il-2026.s887.upress.link');

-- Verification queries
SELECT
    'Posts with localhost' as check_type,
    COUNT(*) as count
FROM wp_posts
WHERE post_content LIKE '%localhost%'

UNION ALL

SELECT
    'Postmeta with localhost',
    COUNT(*)
FROM wp_postmeta
WHERE meta_value LIKE '%localhost%'

UNION ALL

SELECT
    'Options with localhost',
    COUNT(*)
FROM wp_options
WHERE option_value LIKE '%localhost%';

-- Current site URLs verification
SELECT 'siteurl' as setting, option_value as value FROM wp_options WHERE option_name = 'siteurl'
UNION ALL
SELECT 'home', option_value FROM wp_options WHERE option_name = 'home';