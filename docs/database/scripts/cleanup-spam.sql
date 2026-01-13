-- ========================================
-- WordPress Spam & Comments Cleanup Script
-- Safely removes spam and old unapproved comments
-- ========================================

USE deveyala_uprdb;

-- ========================================
-- STEP 1: Analyze Current Comments
-- ========================================

-- Overall comment statistics
SELECT
    comment_approved,
    COUNT(*) as count,
    MIN(comment_date) as oldest,
    MAX(comment_date) as newest
FROM wp_comments
GROUP BY comment_approved
ORDER BY count DESC;

-- Spam by month (last 12 months)
SELECT
    DATE_FORMAT(comment_date, '%Y-%m') as month,
    COUNT(*) as spam_count
FROM wp_comments
WHERE comment_approved = 'spam'
AND comment_date > DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(comment_date, '%Y-%m')
ORDER BY month DESC;

-- Top spam IPs
SELECT
    comment_author_IP,
    COUNT(*) as spam_count,
    MAX(comment_date) as last_spam
FROM wp_comments
WHERE comment_approved = 'spam'
AND comment_author_IP != ''
GROUP BY comment_author_IP
ORDER BY spam_count DESC
LIMIT 20;

-- ========================================
-- STEP 2: Safe Spam Cleanup
-- ========================================

-- Delete spam older than 90 days (keep recent for analysis)
DELETE FROM wp_comments
WHERE comment_approved = 'spam'
AND comment_date < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Delete very old spam (older than 1 year)
DELETE FROM wp_comments
WHERE comment_approved = 'spam'
AND comment_date < DATE_SUB(NOW(), INTERVAL 365 DAY);

-- Delete spam with suspicious content patterns
DELETE FROM wp_comments
WHERE comment_approved = 'spam'
AND (
    comment_content LIKE '%viagra%' OR
    comment_content LIKE '%casino%' OR
    comment_content LIKE '%lottery%' OR
    comment_content LIKE '%pharmacy%' OR
    comment_content REGEXP '[0-9]{8,}'  -- Long numbers
);

-- ========================================
-- STEP 3: Cleanup Unapproved Comments
-- ========================================

-- Delete unapproved comments older than 6 months
DELETE FROM wp_comments
WHERE comment_approved = '0'
AND comment_date < DATE_SUB(NOW(), INTERVAL 180 DAY);

-- Delete unapproved comments from blocked IPs (if any)
-- Add known spam IPs here
DELETE FROM wp_comments
WHERE comment_approved = '0'
AND comment_author_IP IN (
    '192.168.1.1',  -- Add actual blocked IPs
    '10.0.0.1'      -- Add actual blocked IPs
);

-- ========================================
-- STEP 4: Cleanup Orphaned Comment Meta
-- ========================================

-- Delete meta for deleted comments
DELETE cm FROM wp_commentmeta cm
LEFT JOIN wp_comments c ON cm.comment_id = c.comment_id
WHERE c.comment_id IS NULL;

-- Delete empty or useless comment meta
DELETE FROM wp_commentmeta
WHERE meta_value = ''
OR meta_value IS NULL;

-- ========================================
-- STEP 5: Update Comment Counts
-- ========================================

-- Update comment counts for all posts
UPDATE wp_posts SET comment_count = (
    SELECT COUNT(*)
    FROM wp_comments
    WHERE wp_comments.comment_post_ID = wp_posts.ID
    AND comment_approved = '1'
);

-- ========================================
-- STEP 6: Optimize Tables
-- ========================================

OPTIMIZE TABLE wp_comments;
OPTIMIZE TABLE wp_commentmeta;

-- ========================================
-- STEP 7: Verify Cleanup Results
-- ========================================

-- Final comment statistics
SELECT
    comment_approved,
    COUNT(*) as count,
    ROUND(AVG(LENGTH(comment_content)), 0) as avg_content_length
FROM wp_comments
GROUP BY comment_approved
ORDER BY count DESC;

-- Show storage impact
SELECT
    'Comments cleanup completed' as status,
    NOW() as cleanup_time,
    (
        SELECT COUNT(*)
        FROM wp_comments
        WHERE comment_approved IN ('spam', '0')
    ) as remaining_moderated,
    (
        SELECT COUNT(*)
        FROM wp_comments
        WHERE comment_approved = '1'
    ) as approved_comments;

-- ========================================
-- STEP 8: Create Maintenance Schedule
-- ========================================

-- Create a view for monitoring comment spam
CREATE OR REPLACE VIEW wp_comment_spam_stats AS
SELECT
    DATE_FORMAT(comment_date, '%Y-%m') as month,
    COUNT(*) as total_comments,
    SUM(CASE WHEN comment_approved = 'spam' THEN 1 ELSE 0 END) as spam_count,
    ROUND(
        SUM(CASE WHEN comment_approved = 'spam' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2
    ) as spam_percentage
FROM wp_comments
WHERE comment_date > DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(comment_date, '%Y-%m')
ORDER BY month DESC;

-- ========================================
-- CLEANUP COMPLETE
-- ========================================
-- Results:
-- ✅ Spam comments cleaned
-- ✅ Orphaned meta removed
-- ✅ Comment counts updated
-- ✅ Tables optimized
--
-- Next steps:
-- 1. Monitor for new spam patterns
-- 2. Consider anti-spam plugins if needed
-- 3. Schedule monthly cleanup