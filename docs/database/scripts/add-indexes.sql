-- ========================================
-- WordPress Database Indexes Optimization
-- Adds missing indexes for better query performance
-- ========================================

USE deveyala_uprdb;

-- ========================================
-- STEP 1: Analyze Current Indexes
-- ========================================

-- Show existing indexes on main tables
SHOW INDEX FROM wp_posts;
SHOW INDEX FROM wp_postmeta;
SHOW INDEX FROM wp_comments;
SHOW INDEX FROM wp_options;
SHOW INDEX FROM wp_terms;
SHOW INDEX FROM wp_term_relationships;

-- Check for missing indexes by analyzing slow queries
SELECT
    sql_text,
    exec_count,
    avg_timer_wait/1000000000 as avg_time_sec,
    rows_examined
FROM performance_schema.events_statements_summary_by_digest
WHERE schema_name = DATABASE()
AND avg_timer_wait > 1000000000  -- > 1 second
ORDER BY avg_timer_wait DESC
LIMIT 10;

-- ========================================
-- STEP 2: Add Essential Indexes
-- ========================================

-- Posts table indexes
CREATE INDEX IF NOT EXISTS idx_post_type_status_date
ON wp_posts (post_type, post_status, post_date);

CREATE INDEX IF NOT EXISTS idx_post_author_date
ON wp_posts (post_author, post_date);

CREATE INDEX IF NOT EXISTS idx_post_parent
ON wp_posts (post_parent);

CREATE INDEX IF NOT EXISTS idx_post_name
ON wp_posts (post_name(100));

-- Postmeta table indexes (most critical)
CREATE INDEX IF NOT EXISTS idx_meta_key_value
ON wp_postmeta (meta_key(50), meta_value(100));

CREATE INDEX IF NOT EXISTS idx_post_id_meta_key
ON wp_postmeta (post_id, meta_key(50));

-- Comments table indexes
CREATE INDEX IF NOT EXISTS idx_comment_post_approved_date
ON wp_comments (comment_post_ID, comment_approved, comment_date);

CREATE INDEX IF NOT EXISTS idx_comment_author_ip
ON wp_comments (comment_author_IP);

CREATE INDEX IF NOT EXISTS idx_comment_approved_date
ON wp_comments (comment_approved, comment_date);

-- Options table indexes
CREATE INDEX IF NOT EXISTS idx_option_name_autoload
ON wp_options (option_name(100), autoload);

-- Terms and taxonomy indexes
CREATE INDEX IF NOT EXISTS idx_term_taxonomy_taxonomy
ON wp_term_taxonomy (taxonomy);

CREATE INDEX IF NOT EXISTS idx_term_relationships_object_taxonomy
ON wp_term_relationships (object_id, term_taxonomy_id);

-- Users table indexes
CREATE INDEX IF NOT EXISTS idx_user_login
ON wp_users (user_login);

CREATE INDEX IF NOT EXISTS idx_user_email
ON wp_users (user_email);

-- ========================================
-- STEP 3: WooCommerce Specific Indexes
-- ========================================

-- WooCommerce order indexes
CREATE INDEX IF NOT EXISTS idx_wc_order_status_date
ON wp_posts (post_type, post_status, post_date)
WHERE post_type IN ('shop_order', 'shop_order_refund');

-- WooCommerce product indexes
CREATE INDEX IF NOT EXISTS idx_wc_product_type
ON wp_posts (post_type, menu_order)
WHERE post_type IN ('product', 'product_variation');

-- WooCommerce order items
CREATE INDEX IF NOT EXISTS idx_wc_order_item_order_id
ON wp_woocommerce_order_items (order_id);

CREATE INDEX IF NOT EXISTS idx_wc_order_item_meta_order_item_id
ON wp_woocommerce_order_itemmeta (order_item_id);

-- WooCommerce sessions
CREATE INDEX IF NOT EXISTS idx_wc_session_customer_id
ON wp_woocommerce_sessions (session_key(50));

-- ========================================
-- STEP 4: Plugin Specific Indexes
-- ========================================

-- Yoast SEO indexes
CREATE INDEX IF NOT EXISTS idx_yoast_primary_focus_keyword
ON wp_postmeta (meta_key(50))
WHERE meta_key = '_yoast_wpseo_focuskw';

-- LayerSlider indexes (if exists)
-- CREATE INDEX IF NOT EXISTS idx_layerslider_id ON wp_layerslider (id);

-- Envira Gallery indexes (if exists)
-- CREATE INDEX IF NOT EXISTS idx_envira_gallery_id ON wp_envira_galleries (id);

-- ========================================
-- STEP 5: Analyze Index Effectiveness
-- ========================================

-- Check index usage
SELECT
    object_schema,
    object_name,
    index_name,
    count_read,
    count_fetch,
    count_insert,
    count_update,
    count_delete
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE object_schema = DATABASE()
AND index_name IS NOT NULL
ORDER BY (count_read + count_fetch) DESC
LIMIT 20;

-- Check for unused indexes
SELECT
    object_schema,
    object_name,
    index_name,
    count_read,
    count_fetch
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE object_schema = DATABASE()
AND index_name IS NOT NULL
AND count_read = 0
AND count_fetch = 0
ORDER BY object_name;

-- ========================================
-- STEP 6: Index Maintenance
-- ========================================

-- Analyze table indexes
ANALYZE TABLE wp_posts;
ANALYZE TABLE wp_postmeta;
ANALYZE TABLE wp_comments;
ANALYZE TABLE wp_options;
ANALYZE TABLE wp_terms;
ANALYZE TABLE wp_term_relationships;

-- Check index fragmentation
SELECT
    TABLE_NAME as 'Table',
    INDEX_NAME as 'Index',
    PAGE as 'Page',
    SIZE as 'Size',
    RECORDS as 'Records',
    FRAG as 'Fragmentation_%'
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_%'
AND SEQ_IN_INDEX = 1
ORDER BY FRAG DESC;

-- ========================================
-- STEP 7: Cleanup and Verification
-- ========================================

-- Verify indexes were created
SELECT
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    INDEX_TYPE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_%'
ORDER BY TABLE_NAME, SEQ_IN_INDEX;

-- Performance impact check
SELECT
    'Index optimization completed' as status,
    NOW() as completion_time,
    (
        SELECT COUNT(*)
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME LIKE 'wp_%'
    ) as total_indexes;

-- ========================================
-- STEP 8: Monitoring Setup
-- ========================================

-- Create index usage monitoring
CREATE TABLE IF NOT EXISTS wp_index_usage_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100),
    index_name VARCHAR(100),
    usage_count INT DEFAULT 0,
    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Log index usage (requires MySQL 8.0+ or MariaDB 10.3+)
-- This is a simplified version - in production, use performance schema

DELIMITER //

CREATE PROCEDURE monitor_index_usage()
BEGIN
    -- This would be more complex in production
    -- For now, just log that monitoring is active
    INSERT INTO wp_index_usage_log (table_name, index_name, usage_count)
    SELECT 'system', 'monitoring_active', 1
    ON DUPLICATE KEY UPDATE usage_count = usage_count + 1;
END //

DELIMITER ;

-- ========================================
-- INDEXES OPTIMIZATION COMPLETE
-- ========================================
-- Added indexes:
-- ✅ Post type and status combinations
-- ✅ Postmeta key-value pairs
-- ✅ Comment relationships
-- ✅ WooCommerce orders and products
-- ✅ User login and email
--
-- Next steps:
-- 1. Monitor query performance improvement
-- 2. Check for unused indexes quarterly
-- 3. Add new indexes as needed based on slow queries