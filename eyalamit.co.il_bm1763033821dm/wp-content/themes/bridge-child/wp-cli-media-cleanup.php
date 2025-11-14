<?php
/**
 * WP-CLI Command for Media Cleanup
 * 
 * שימוש:
 * wp media-cleanup analyze
 * wp media-cleanup cleanup --orphans --dry-run
 * wp media-cleanup cleanup --thumbnails --older-than=2020-01-01 --execute
 */

if (!defined('WP_CLI')) {
    return;
}

require_once(__DIR__ . '/media-cleanup.php');

/**
 * WP-CLI Command for Media Cleanup
 */
class Media_Cleanup_CLI {
    
    /**
     * ניתוח מדיה לא בשימוש
     * 
     * ## EXAMPLES
     * 
     *     # ניתוח בלבד
     *     $ wp media-cleanup analyze
     * 
     * @when after_wp_load
     */
    public function analyze($args, $assoc_args) {
        $cleanup = new Media_Cleanup();
        $cleanup->analyze('analyze');
    }
    
    /**
     * ניקוי מדיה לא בשימוש
     * 
     * ## OPTIONS
     * 
     * [--orphans]
     * : מחיקת קבצים ללא רשומה ב-DB
     * 
     * [--thumbnails]
     * : מחיקת thumbnails
     * 
     * [--unused]
     * : מחיקת attachments לא בשימוש
     * 
     * [--older-than=<date>]
     * : סינון לפי תאריך (YYYY-MM-DD)
     * 
     * [--execute]
     * : ביצוע בפועל (ללא זה זה dry run)
     * 
     * ## EXAMPLES
     * 
     *     # סימולציה - מחיקת קבצים ללא רשומה
     *     $ wp media-cleanup cleanup --orphans
     * 
     *     # מחיקה בפועל - thumbnails ישנים
     *     $ wp media-cleanup cleanup --thumbnails --older-than=2020-01-01 --execute
     * 
     *     # מחיקה בפועל - הכל
     *     $ wp media-cleanup cleanup --orphans --thumbnails --unused --older-than=2018-01-01 --execute
     * 
     * @when after_wp_load
     */
    public function cleanup($args, $assoc_args) {
        $options = array(
            'dry_run' => !isset($assoc_args['execute']),
            'delete_orphans' => isset($assoc_args['orphans']),
            'delete_thumbnails' => isset($assoc_args['thumbnails']),
            'delete_unused' => isset($assoc_args['unused']),
            'older_than' => isset($assoc_args['older-than']) ? $assoc_args['older-than'] : null
        );
        
        if (!$options['delete_orphans'] && !$options['delete_thumbnails'] && !$options['delete_unused']) {
            WP_CLI::error('יש לציין לפחות אחת מהאופציות: --orphans, --thumbnails, או --unused');
        }
        
        $cleanup = new Media_Cleanup();
        $result = $cleanup->cleanup($options);
        
        if ($options['dry_run']) {
            WP_CLI::success('סימולציה הושלמה. השתמש ב--execute לביצוע בפועל.');
        } else {
            WP_CLI::success(sprintf('נמחקו %d קבצים, שחרור של %s', 
                $result['deleted'], 
                $cleanup->format_bytes($result['size'])
            ));
        }
    }
}

WP_CLI::add_command('media-cleanup', 'Media_Cleanup_CLI');





