-- ========================================
-- WordPress Tables Optimization Script
-- Repairs, optimizes, and analyzes all WordPress tables
-- ========================================

USE deveyala_uprdb;

-- ========================================
-- STEP 1: Pre-Optimization Analysis
-- ========================================

-- Create analysis table to track improvements
CREATE TABLE IF NOT EXISTS wp_optimization_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100),
    operation VARCHAR(50),
    before_size DECIMAL(10,2),
    after_size DECIMAL(10,2),
    improvement DECIMAL(5,2),
    execution_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Record table sizes before optimization
INSERT INTO wp_optimization_log (table_name, operation, before_size)
SELECT
    TABLE_NAME,
    'pre_optimization',
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2)
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_%';

-- Check table fragmentation
SELECT
    TABLE_NAME as 'Table',
    ROUND(DATA_LENGTH / 1024 / 1024, 2) as 'Data_MB',
    ROUND(INDEX_LENGTH / 1024 / 1024, 2) as 'Index_MB',
    ROUND(DATA_FREE / 1024 / 1024, 2) as 'Free_MB',
    ROUND(
        (DATA_FREE / (DATA_LENGTH + INDEX_LENGTH)) * 100, 2
    ) as 'Fragmentation_%'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_%'
ORDER BY (DATA_FREE / (DATA_LENGTH + INDEX_LENGTH)) DESC;

-- ========================================
-- STEP 2: Repair Tables (Safety First)
-- ========================================

-- Repair all WordPress tables
REPAIR TABLE wp_commentmeta;
REPAIR TABLE wp_comments;
REPAIR TABLE wp_links;
REPAIR TABLE wp_options;
REPAIR TABLE wp_postmeta;
REPAIR TABLE wp_posts;
REPAIR TABLE wp_term_relationships;
REPAIR TABLE wp_term_taxonomy;
REPAIR TABLE wp_terms;
REPAIR TABLE wp_usermeta;
REPAIR TABLE wp_users;
REPAIR TABLE wp_woocommerce_sessions;
REPAIR TABLE wp_woocommerce_order_items;
REPAIR TABLE wp_woocommerce_order_itemmeta;

-- ========================================
-- STEP 3: Optimize Tables
-- ========================================

-- Optimize all WordPress tables
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
OPTIMIZE TABLE wp_woocommerce_sessions;
OPTIMIZE TABLE wp_woocommerce_order_items;
OPTIMIZE TABLE wp_woocommerce_order_itemmeta;

-- ========================================
-- STEP 4: Analyze Table Structure
-- ========================================

-- Analyze all tables for better query optimization
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
ANALYZE TABLE wp_woocommerce_sessions;
ANALYZE TABLE wp_woocommerce_order_items;
ANALYZE TABLE wp_woocommerce_order_itemmeta;

-- ========================================
-- STEP 5: Post-Optimization Analysis
-- ========================================

-- Record table sizes after optimization
INSERT INTO wp_optimization_log (table_name, operation, after_size)
SELECT
    TABLE_NAME,
    'post_optimization',
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2)
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_%';

-- Calculate improvements
UPDATE wp_optimization_log w1
JOIN wp_optimization_log w2 ON w1.table_name = w2.table_name
SET w1.improvement = ROUND(((w1.before_size - w2.after_size) / w1.before_size) * 100, 2)
WHERE w1.operation = 'pre_optimization'
AND w2.operation = 'post_optimization';

-- ========================================
-- STEP 6: Check Optimization Results
-- ========================================

-- Show optimization results
SELECT
    table_name as 'Table',
    before_size as 'Before_MB',
    after_size as 'After_MB',
    CONCAT(improvement, '%') as 'Improvement'
FROM wp_optimization_log
WHERE operation = 'pre_optimization'
ORDER BY improvement DESC;

-- Show current table status
SELECT
    TABLE_NAME as 'Table',
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as 'Total_Size_MB',
    ROUND(DATA_FREE / 1024 / 1024, 2) as 'Free_MB',
    TABLE_ROWS as 'Rows'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_%'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;

-- ========================================
-- STEP 7: Database Health Check
-- ========================================

-- Check for common issues
SELECT
    'Database Health Check' as status,
    NOW() as check_time,
    (
        SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME LIKE 'wp_%'
    ) as total_tables,
    (
        SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2)
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME LIKE 'wp_%'
    ) as total_size_mb,
    (
        SELECT COUNT(*)
        FROM wp_posts
        WHERE post_type = 'revision'
    ) as active_revisions,
    (
        SELECT COUNT(*)
        FROM wp_comments
        WHERE comment_approved = 'spam'
    ) as active_spam;

-- ========================================
-- STEP 8: Create Optimization Schedule
-- ========================================

-- Create stored procedure for regular optimization
DELIMITER //

CREATE PROCEDURE weekly_table_optimization()
BEGIN
    -- Log optimization start
    INSERT INTO wp_optimization_log (table_name, operation)
    VALUES ('system', 'weekly_optimization_start');

    -- Optimize main tables
    OPTIMIZE TABLE wp_posts;
    OPTIMIZE TABLE wp_postmeta;
    OPTIMIZE TABLE wp_comments;
    OPTIMIZE TABLE wp_options;

    -- Clean old optimization logs (keep last 30 days)
    DELETE FROM wp_optimization_log
    WHERE execution_time < DATE_SUB(NOW(), INTERVAL 30 DAY);

    -- Log optimization end
    INSERT INTO wp_optimization_log (table_name, operation)
    VALUES ('system', 'weekly_optimization_complete');
END //

DELIMITER ;

-- ========================================
-- STEP 9: Setup Automated Maintenance
-- ========================================

-- Create event for weekly optimization (requires EVENT privilege)
-- Uncomment and run if you have EVENT privileges:
/*
CREATE EVENT weekly_db_maintenance
ON SCHEDULE EVERY 1 WEEK STARTS '2026-01-18 02:00:00'
DO
    CALL weekly_table_optimization();
*/

-- Alternative: Use WordPress cron
-- Add to functions.php:
-- if (!wp_next_scheduled('weekly_db_optimization')) {
--     wp_schedule_event(strtotime('next sunday 2am'), 'weekly', 'weekly_db_optimization');
-- }
-- add_action('weekly_db_optimization', 'weekly_table_optimization');

-- ========================================
-- OPTIMIZATION COMPLETE
-- ========================================
-- Results:
-- ✅ Tables repaired
-- ✅ Tables optimized
-- ✅ Table structure analyzed
-- ✅ Optimization logged
-- ✅ Maintenance procedure created
--
-- Next steps:
-- 1. Monitor query performance
-- 2. Check site speed improvement
-- 3. Schedule regular optimization