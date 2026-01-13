-- ========================================
-- WordPress Revisions Cleanup Script
-- Safely removes old post revisions while keeping recent ones
-- ========================================

USE deveyala_uprdb;

-- ========================================
-- STEP 1: Analyze Current Revisions
-- ========================================

-- Count total revisions
SELECT
    COUNT(*) as total_revisions,
    COUNT(DISTINCT post_parent) as posts_with_revisions
FROM wp_posts
WHERE post_type = 'revision';

-- Show revisions per post (top 20)
SELECT
    p.post_title,
    COUNT(r.ID) as revision_count,
    MIN(r.post_date) as oldest_revision,
    MAX(r.post_date) as newest_revision
FROM wp_posts p
LEFT JOIN wp_posts r ON p.ID = r.post_parent AND r.post_type = 'revision'
WHERE p.post_type IN ('post', 'page')
GROUP BY p.ID, p.post_title
HAVING revision_count > 0
ORDER BY revision_count DESC
LIMIT 20;

-- ========================================
-- STEP 2: Safe Cleanup Strategy
-- ========================================

-- Strategy 1: Keep only last 3 revisions per post
-- This is SAFEST but keeps more revisions

SET @max_revisions = 3;

DELETE r1 FROM wp_posts r1
INNER JOIN wp_posts r2
WHERE r1.post_parent = r2.post_parent
AND r1.post_type = 'revision'
AND r2.post_type = 'revision'
AND r1.post_date < r2.post_date
AND (
    SELECT COUNT(*)
    FROM wp_posts r3
    WHERE r3.post_parent = r1.post_parent
    AND r3.post_type = 'revision'
    AND r3.post_date >= r1.post_date
) > @max_revisions;

-- Strategy 2: Delete revisions older than 60 days
-- More aggressive cleanup

DELETE FROM wp_posts
WHERE post_type = 'revision'
AND post_date < DATE_SUB(NOW(), INTERVAL 60 DAY);

-- Strategy 3: Delete revisions for trashed posts
DELETE r FROM wp_posts r
INNER JOIN wp_posts p ON r.post_parent = p.ID
WHERE r.post_type = 'revision'
AND p.post_status = 'trash';

-- ========================================
-- STEP 3: Cleanup Orphaned Revision Meta
-- ========================================

-- Delete postmeta for deleted revisions
DELETE pm FROM wp_postmeta pm
LEFT JOIN wp_posts p ON pm.post_id = p.ID
WHERE p.ID IS NULL
AND pm.meta_key LIKE '_wp_%'; -- Revision-specific meta

-- ========================================
-- STEP 4: Optimize Tables After Cleanup
-- ========================================

OPTIMIZE TABLE wp_posts;
OPTIMIZE TABLE wp_postmeta;

-- ========================================
-- STEP 5: Verify Cleanup Results
-- ========================================

-- Count remaining revisions
SELECT
    COUNT(*) as remaining_revisions,
    COUNT(DISTINCT post_parent) as posts_with_revisions
FROM wp_posts
WHERE post_type = 'revision';

-- Show storage freed (approximate)
SELECT
    'Revisions cleanup completed' as status,
    NOW() as cleanup_time,
    (
        SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'wp_posts'
    ) as tables_optimized;

-- ========================================
-- CLEANUP COMPLETE
-- ========================================
-- Next steps:
-- 1. Check site functionality
-- 2. Monitor performance improvement
-- 3. Schedule regular cleanup (monthly)