-- 🚨 CRITICAL: Smart Quotes Sanitization for WPBakery Shortcodes
-- Authority: CEO Eyal Amit - Payload v6.8
-- Issue: WPBakery rendering failure due to smart quotes in shortcodes
-- Execute with BACKUP FIRST - Evidence required: Rows affected count

-- Query 1: Replace left double quotation mark with straight quotes
UPDATE wp_posts
SET post_content = REPLACE(post_content, '"', '"')
WHERE post_content LIKE '%vc_%';

-- Query 2: Replace right double quotation mark with straight quotes
UPDATE wp_posts
SET post_content = REPLACE(post_content, '"', '"')
WHERE post_content LIKE '%vc_%';

-- Query 3: Replace left single quotation mark with straight quotes
UPDATE wp_posts
SET post_content = REPLACE(post_content, ''', "'")
WHERE post_content LIKE '%vc_%';

-- Verification Query: Count affected posts
SELECT COUNT(*) as total_posts_with_vc_shortcodes
FROM wp_posts
WHERE post_content LIKE '%vc_%';