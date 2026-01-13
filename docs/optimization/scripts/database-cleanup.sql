-- ========================================
-- WordPress Database Cleanup Script 2026
-- ========================================
-- Run this script to optimize database performance
-- BACKUP YOUR DATABASE BEFORE RUNNING!

-- 1. Clean old post revisions (keep last 5 per post)
-- ========================================
DELETE a
FROM wp_posts a
LEFT JOIN (
    SELECT ID, post_parent
    FROM wp_posts
    WHERE post_type = 'revision'
    ORDER BY post_date DESC
) b ON a.ID = b.ID AND a.post_parent = b.post_parent
WHERE a.post_type = 'revision'
AND a.ID NOT IN (
    SELECT * FROM (
        SELECT ID FROM wp_posts
        WHERE post_type = 'revision'
        ORDER BY post_date DESC
        LIMIT 999999999999
    ) tmp
);

-- 2. Clean spam comments
-- ========================================
DELETE FROM wp_comments WHERE comment_approved = 'spam';

-- 3. Clean unapproved comments older than 30 days
-- ========================================
DELETE FROM wp_comments
WHERE comment_approved = '0'
AND comment_date < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- 4. Clean orphaned comment meta
-- ========================================
DELETE cm
FROM wp_commentmeta cm
LEFT JOIN wp_comments c ON cm.comment_id = c.comment_id
WHERE c.comment_id IS NULL;

-- 5. Clean expired transients
-- ========================================
DELETE FROM wp_options
WHERE option_name LIKE '_transient_%'
AND option_value IS NULL;

DELETE FROM wp_options
WHERE option_name LIKE '_transient_timeout_%'
AND option_value < UNIX_TIMESTAMP();

-- 6. Clean orphaned post meta
-- ========================================
DELETE pm
FROM wp_postmeta pm
LEFT JOIN wp_posts p ON pm.post_id = p.ID
WHERE p.ID IS NULL;

-- 7. Clean orphaned term relationships
-- ========================================
DELETE tr
FROM wp_term_relationships tr
LEFT JOIN wp_posts p ON tr.object_id = p.ID
WHERE p.ID IS NULL;

-- 8. Clean unused terms
-- ========================================
DELETE t
FROM wp_terms t
LEFT JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
LEFT JOIN wp_term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
WHERE tt.term_taxonomy_id IS NULL
AND tr.term_taxonomy_id IS NULL;

-- 9. Clean duplicate post meta
-- ========================================
DELETE pm1
FROM wp_postmeta pm1
INNER JOIN wp_postmeta pm2
WHERE pm1.post_id = pm2.post_id
AND pm1.meta_key = pm2.meta_key
AND pm1.meta_id > pm2.meta_id;

-- 10. Optimize all tables
-- ========================================
OPTIMIZE TABLE wp_commentmeta;
OPTIMIZE TABLE wp_comments;
OPTIMIZE TABLE wp_links;
OPTIMIZE TABLE wp_options;
OPTIMIZE TABLE wp_postmeta;
OPTIMIZE TABLE wp_posts;
OPTIMIZE TABLE wp_term_relationships;
OPTIMIZE TABLE wp_term_taxonomy;
OPTIMIZE TABLE wp_terms;
OPTIMIZE TABLE wp_usermeta;
OPTIMIZE TABLE wp_users;

-- 11. Analyze table structure
-- ========================================
ANALYZE TABLE wp_commentmeta;
ANALYZE TABLE wp_comments;
ANALYZE TABLE wp_links;
ANALYZE TABLE wp_options;
ANALYZE TABLE wp_postmeta;
ANALYZE TABLE wp_posts;
ANALYZE TABLE wp_term_relationships;
ANALYZE TABLE wp_term_taxonomy;
ANALYZE TABLE wp_terms;
ANALYZE TABLE wp_usermeta;
ANALYZE TABLE wp_users;

-- ========================================
-- CLEANUP COMPLETE
-- Run: SHOW TABLE STATUS; to check results
-- ========================================