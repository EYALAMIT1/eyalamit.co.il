<?php
/**
 * Media Cleanup Script - ניקוי מדיה לא בשימוש
 * 
 * סקריפט לזיהוי ומחיקה של תמונות ומדיה לא בשימוש ב-WordPress
 * 
 * שימוש:
 * 1. דרך WP-CLI: wp eval-file wp-content/themes/bridge-child/media-cleanup.php --mode=analyze
 * 2. דרך דף admin: הוסף ל-functions.php או הרץ ישירות
 * 
 * @package Bridge Child Theme
 * @version 1.0
 */

// בטיחות - רק אם WordPress נטען
if (!defined('ABSPATH')) {
    // אם רצים דרך WP-CLI או ישירות
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
}

class Media_Cleanup {
    
    private $uploads_dir;
    private $uploads_url;
    private $report_dir;
    private $stats = array(
        'total_attachments' => 0,
        'total_files' => 0,
        'orphan_files' => 0,
        'unused_attachments' => 0,
        'thumbnails' => 0,
        'total_size' => 0,
        'orphan_size' => 0,
        'unused_size' => 0,
        'thumbnail_size' => 0
    );
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->uploads_dir = $upload_dir['basedir'];
        $this->uploads_url = $upload_dir['baseurl'];
        $this->report_dir = $this->uploads_dir . '/media-cleanup-reports';
        
        // יצירת תיקיית דוחות
        if (!file_exists($this->report_dir)) {
            wp_mkdir_p($this->report_dir);
        }
    }
    
    /**
     * מחיקה מבוקרת של קבצים
     */
    public function delete_files($files, $type = 'orphan', $dry_run = true) {
        $deleted = 0;
        $failed = 0;
        $total_size = 0;
        
        echo "\n=== מחיקת קבצים (Type: {$type}, Dry Run: " . ($dry_run ? 'Yes' : 'No') . ") ===\n\n";
        
        foreach ($files as $file) {
            $filepath = isset($file['path']) ? $file['path'] : (isset($file['relative_path']) ? $this->uploads_dir . '/' . $file['relative_path'] : '');
            
            if (empty($filepath) || !file_exists($filepath)) {
                $failed++;
                continue;
            }
            
            $size = isset($file['size']) ? $file['size'] : filesize($filepath);
            
            if ($dry_run) {
                echo "[DRY RUN] היה נמחק: {$filepath} (" . $this->format_bytes($size) . ")\n";
            } else {
                if (@unlink($filepath)) {
                    echo "נמחק: {$filepath} (" . $this->format_bytes($size) . ")\n";
                    $deleted++;
                    $total_size += $size;
                } else {
                    echo "שגיאה במחיקת: {$filepath}\n";
                    $failed++;
                }
            }
        }
        
        echo "\nסיכום מחיקה:\n";
        echo "נמחקו: {$deleted} קבצים\n";
        echo "נכשלו: {$failed} קבצים\n";
        if (!$dry_run) {
            echo "גודל שחרור: " . $this->format_bytes($total_size) . "\n";
        }
        
        return array('deleted' => $deleted, 'failed' => $failed, 'size' => $total_size);
    }
    
    /**
     * מחיקת attachments לא בשימוש מבסיס הנתונים
     */
    public function delete_unused_attachments($attachments, $dry_run = true) {
        global $wpdb;
        
        $deleted = 0;
        $failed = 0;
        
        echo "\n=== מחיקת attachments לא בשימוש (Dry Run: " . ($dry_run ? 'Yes' : 'No') . ") ===\n\n";
        
        foreach ($attachments as $attachment) {
            $attachment_id = $attachment['ID'];
            
            if ($dry_run) {
                echo "[DRY RUN] היה נמחק attachment ID: {$attachment_id} - {$attachment['post_title']}\n";
            } else {
                // מחיקת ה-attachment (כולל הקבצים הקשורים)
                $result = wp_delete_attachment($attachment_id, true);
                
                if ($result) {
                    echo "נמחק attachment ID: {$attachment_id} - {$attachment['post_title']}\n";
                    $deleted++;
                } else {
                    echo "שגיאה במחיקת attachment ID: {$attachment_id}\n";
                    $failed++;
                }
            }
        }
        
        echo "\nסיכום מחיקה:\n";
        echo "נמחקו: {$deleted} attachments\n";
        echo "נכשלו: {$failed} attachments\n";
        
        return array('deleted' => $deleted, 'failed' => $failed);
    }
    
    /**
     * פונקציה ראשית - ניתוח המדיה
     */
    public function analyze($mode = 'analyze') {
        echo "=== ניתוח מדיה לא בשימוש ===\n";
        echo "תאריך: " . date('Y-m-d H:i:s') . "\n";
        echo "תיקיית uploads: " . $this->uploads_dir . "\n\n";
        
        // שלב 1: איסוף מידע מבסיס הנתונים
        echo "שלב 1: איסוף attachments מבסיס הנתונים...\n";
        $attachments = $this->get_all_attachments();
        $this->stats['total_attachments'] = count($attachments);
        echo "נמצאו " . count($attachments) . " attachments בבסיס הנתונים\n\n";
        
        // שלב 2: איסוף קבצים פיזיים
        echo "שלב 2: סריקת קבצים פיזיים...\n";
        $physical_files = $this->get_physical_files();
        $this->stats['total_files'] = count($physical_files);
        echo "נמצאו " . count($physical_files) . " קבצים פיזיים\n\n";
        
        // שלב 3: זיהוי קבצים ללא רשומה ב-DB (orphan files)
        echo "שלב 3: זיהוי קבצים ללא רשומה ב-DB...\n";
        $orphan_files = $this->find_orphan_files($attachments, $physical_files);
        $this->stats['orphan_files'] = count($orphan_files);
        $this->stats['orphan_size'] = $this->calculate_total_size($orphan_files);
        echo "נמצאו " . count($orphan_files) . " קבצים ללא רשומה (" . $this->format_bytes($this->stats['orphan_size']) . ")\n\n";
        
        // שלב 4: זיהוי thumbnails
        echo "שלב 4: זיהוי thumbnails...\n";
        $thumbnails = $this->find_thumbnails($physical_files);
        $this->stats['thumbnails'] = count($thumbnails);
        $this->stats['thumbnail_size'] = $this->calculate_total_size($thumbnails);
        echo "נמצאו " . count($thumbnails) . " thumbnails (" . $this->format_bytes($this->stats['thumbnail_size']) . ")\n\n";
        
        // שלב 5: זיהוי attachments לא בשימוש
        echo "שלב 5: בדיקת שימוש ב-attachments...\n";
        $unused_attachments = $this->find_unused_attachments($attachments);
        $this->stats['unused_attachments'] = count($unused_attachments);
        $unused_files = $this->get_files_for_attachments($unused_attachments);
        $this->stats['unused_size'] = $this->calculate_total_size($unused_files);
        echo "נמצאו " . count($unused_attachments) . " attachments לא בשימוש (" . $this->format_bytes($this->stats['unused_size']) . ")\n\n";
        
        // שלב 6: יצירת דוחות
        echo "שלב 6: יצירת דוחות...\n";
        $this->generate_reports($orphan_files, $thumbnails, $unused_attachments, $unused_files);
        
        // סיכום
        $this->print_summary();
        
        return array(
            'orphan_files' => $orphan_files,
            'thumbnails' => $thumbnails,
            'unused_attachments' => $unused_attachments,
            'stats' => $this->stats
        );
    }
    
    /**
     * איסוף כל ה-attachments מבסיס הנתונים
     */
    private function get_all_attachments() {
        global $wpdb;
        
        $attachments = $wpdb->get_results(
            "SELECT 
                p.ID,
                p.post_title,
                p.post_date,
                p.guid,
                pm_file.meta_value AS file_path,
                pm_metadata.meta_value AS metadata
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_file ON p.ID = pm_file.post_id AND pm_file.meta_key = '_wp_attached_file'
            LEFT JOIN {$wpdb->postmeta} pm_metadata ON p.ID = pm_metadata.post_id AND pm_metadata.meta_key = '_wp_attachment_metadata'
            WHERE p.post_type = 'attachment'
            ORDER BY p.post_date DESC",
            ARRAY_A
        );
        
        return $attachments;
    }
    
    /**
     * איסוף כל הקבצים הפיזיים מתיקיית uploads
     */
    private function get_physical_files() {
        $files = array();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->uploads_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relative_path = str_replace($this->uploads_dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relative_path = str_replace('\\', '/', $relative_path); // Normalize path separators
                
                // דילוג על תיקיות מיוחדות
                if (strpos($relative_path, 'media-cleanup-reports') !== false) {
                    continue;
                }
                
                $files[] = array(
                    'path' => $file->getPathname(),
                    'relative_path' => $relative_path,
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime()
                );
            }
        }
        
        return $files;
    }
    
    /**
     * זיהוי קבצים ללא רשומה ב-DB
     */
    private function find_orphan_files($attachments, $physical_files) {
        // יצירת מפת קבצים שקשורים ל-attachments (יותר מדויק)
        $attachment_files = array();
        $attachment_basenames = array();
        
        foreach ($attachments as $attachment) {
            if (!empty($attachment['file_path'])) {
                $file_path = $attachment['file_path'];
                // נרמול נתיבים
                $file_path = str_replace('\\', '/', $file_path);
                $attachment_files[$file_path] = true;
                $attachment_basenames[basename($file_path)] = true;
                
                // הוספת thumbnails מהמטא-דאטה
                if (!empty($attachment['metadata'])) {
                    $metadata = maybe_unserialize($attachment['metadata']);
                    if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                        $base_dir = dirname($file_path);
                        foreach ($metadata['sizes'] as $size) {
                            if (isset($size['file'])) {
                                $thumb_path = ($base_dir && $base_dir !== '.' ? $base_dir . '/' : '') . $size['file'];
                                $thumb_path = str_replace('\\', '/', $thumb_path);
                                $attachment_files[$thumb_path] = true;
                                $attachment_basenames[basename($thumb_path)] = true;
                            }
                        }
                    }
                }
            }
            
            // גם בדיקה לפי GUID
            if (!empty($attachment['guid'])) {
                $guid_path = $this->guid_to_path($attachment['guid']);
                if ($guid_path) {
                    $guid_path = str_replace('\\', '/', $guid_path);
                    $attachment_files[$guid_path] = true;
                    $attachment_basenames[basename($guid_path)] = true;
                }
            }
        }
        
        // מציאת קבצים שלא ברשימה
        $orphan_files = array();
        $total = count($physical_files);
        $processed = 0;
        
        foreach ($physical_files as $file) {
            $relative_path = str_replace('\\', '/', $file['relative_path']);
            $basename = basename($relative_path);
            
            // דילוג על קבצים מיוחדים
            if (strpos($relative_path, 'media-cleanup-reports') !== false) {
                continue;
            }
            
            // בדיקה מדויקת יותר
            $found = false;
            
            // בדיקה 1: התאמה מדויקת של הנתיב
            if (isset($attachment_files[$relative_path])) {
                $found = true;
            }
            
            // בדיקה 2: בדיקה לפי basename (רק אם יש התאמה אחת)
            if (!$found && isset($attachment_basenames[$basename])) {
                // בדיקה נוספת - האם יש attachment עם אותו basename
                foreach ($attachment_files as $att_path => $val) {
                    if (basename($att_path) === $basename) {
                        // בדיקה אם זה באותה תיקייה או תיקייה דומה
                        $att_dir = dirname($att_path);
                        $file_dir = dirname($relative_path);
                        if ($att_dir === $file_dir || 
                            strpos($relative_path, $att_dir) === 0 ||
                            strpos($att_path, $file_dir) === 0) {
                            $found = true;
                            break;
                        }
                    }
                }
            }
            
            // בדיקה 3: בדיקה אם זה thumbnail של קובץ קיים
            if (!$found) {
                // הסרת סיומת thumbnail מהשם
                $base_name = preg_replace('/-\d+x\d+\.(jpg|jpeg|png|gif|webp)$/i', '', $basename);
                $base_name = preg_replace('/-\d+w\.(jpg|jpeg|png|gif|webp)$/i', '', $base_name);
                $base_name = preg_replace('/-scaled\.(jpg|jpeg|png|gif|webp)$/i', '', $base_name);
                
                if ($base_name !== $basename) {
                    // זה נראה כמו thumbnail - בדוק אם יש קובץ מקור
                    $file_dir = dirname($relative_path);
                    foreach ($attachment_files as $att_path => $val) {
                        $att_basename = basename($att_path);
                        $att_base = preg_replace('/-\d+x\d+\.(jpg|jpeg|png|gif|webp)$/i', '', $att_basename);
                        $att_base = preg_replace('/-\d+w\.(jpg|jpeg|png|gif|webp)$/i', '', $att_base);
                        $att_base = preg_replace('/-scaled\.(jpg|jpeg|png|gif|webp)$/i', '', $att_base);
                        
                        if ($att_base === $base_name && dirname($att_path) === $file_dir) {
                            $found = true;
                            break;
                        }
                    }
                }
            }
            
            if (!$found) {
                $orphan_files[] = $file;
            }
            
            // Progress indicator
            $processed++;
            if ($processed % 1000 === 0) {
                echo "  נבדקו " . $processed . " מתוך " . $total . " קבצים...\n";
            }
        }
        
        return $orphan_files;
    }
    
    /**
     * זיהוי thumbnails (גרסאות של תמונות)
     */
    private function find_thumbnails($physical_files) {
        $thumbnails = array();
        $thumbnail_patterns = array(
            '/-\d+x\d+\.(jpg|jpeg|png|gif|webp)$/i', // פורמט: image-300x200.jpg
            '/-\d+w\.(jpg|jpeg|png|gif|webp)$/i',     // פורמט: image-300w.jpg
            '/-scaled\.(jpg|jpeg|png|gif|webp)$/i',   // WordPress scaled images
        );
        
        foreach ($physical_files as $file) {
            $filename = basename($file['relative_path']);
            
            foreach ($thumbnail_patterns as $pattern) {
                if (preg_match($pattern, $filename)) {
                    $thumbnails[] = $file;
                    break;
                }
            }
        }
        
        return $thumbnails;
    }
    
    /**
     * זיהוי attachments לא בשימוש
     */
    private function find_unused_attachments($attachments) {
        global $wpdb;
        
        $unused = array();
        
        foreach ($attachments as $attachment) {
            $attachment_id = $attachment['ID'];
            $is_used = false;
            
            // בדיקה 1: האם ה-attachment מקושר לפוסט/עמוד
            $parent_id = $wpdb->get_var($wpdb->prepare(
                "SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d",
                $attachment_id
            ));
            
            if ($parent_id > 0) {
                $is_used = true;
            }
            
            // בדיקה 2: חיפוש בתוכן פוסטים/עמודים
            if (!$is_used) {
                $guid = $attachment['guid'];
                $file_path = !empty($attachment['file_path']) ? $attachment['file_path'] : '';
                $filename = basename($file_path);
                
                // חיפוש ב-post_content
                $found_in_content = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts} 
                    WHERE post_content LIKE %s 
                    AND post_type IN ('post', 'page', 'product', 'attachment')",
                    '%' . $wpdb->esc_like($guid) . '%'
                ));
                
                if ($found_in_content > 0) {
                    $is_used = true;
                }
                
                // חיפוש לפי שם קובץ
                if (!$is_used && $filename) {
                    $found_by_filename = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->posts} 
                        WHERE post_content LIKE %s 
                        AND post_type IN ('post', 'page', 'product', 'attachment')",
                        '%' . $wpdb->esc_like($filename) . '%'
                    ));
                    
                    if ($found_by_filename > 0) {
                        $is_used = true;
                    }
                }
            }
            
            // בדיקה 3: חיפוש ב-meta fields
            if (!$is_used) {
                $found_in_meta = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->postmeta} 
                    WHERE meta_value LIKE %s 
                    OR meta_value = %d",
                    '%' . $wpdb->esc_like($attachment_id) . '%',
                    $attachment_id
                ));
                
                if ($found_in_meta > 0) {
                    $is_used = true;
                }
            }
            
            // בדיקה 4: חיפוש ב-options (featured images, logos, וכו')
            if (!$is_used) {
                $found_in_options = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->options} 
                    WHERE option_value LIKE %s 
                    OR option_value = %d",
                    '%' . $wpdb->esc_like($attachment_id) . '%',
                    $attachment_id
                ));
                
                if ($found_in_options > 0) {
                    $is_used = true;
                }
            }
            
            // בדיקה 5: featured image (thumbnail_id)
            if (!$is_used) {
                $found_as_featured = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_thumbnail_id' 
                    AND meta_value = %d",
                    $attachment_id
                ));
                
                if ($found_as_featured > 0) {
                    $is_used = true;
                }
            }
            
            // בדיקה 6: custom post types (כבר נבדק בשלב 2, אבל נוסיף בדיקה נוספת)
            // הבדיקה הזו כבר כלולה בשלב 2, אז נדלג עליה כאן
            
            if (!$is_used) {
                $unused[] = $attachment;
            }
            
            // Progress indicator
            if (count($unused) % 100 === 0 && count($unused) > 0) {
                echo "  נבדקו " . count($unused) . " attachments לא בשימוש עד כה...\n";
            }
        }
        
        return $unused;
    }
    
    /**
     * המרת GUID לנתיב קובץ
     */
    private function guid_to_path($guid) {
        $parsed = parse_url($guid);
        if (isset($parsed['path'])) {
            $path = $parsed['path'];
            // הסרת wp-content/uploads מהנתיב
            $uploads_pos = strpos($path, '/wp-content/uploads/');
            if ($uploads_pos !== false) {
                return substr($path, $uploads_pos + strlen('/wp-content/uploads/'));
            }
        }
        return null;
    }
    
    /**
     * קבלת קבצים עבור attachments
     */
    private function get_files_for_attachments($attachments) {
        $files = array();
        
        foreach ($attachments as $attachment) {
            if (!empty($attachment['file_path'])) {
                $file_path = $this->uploads_dir . '/' . $attachment['file_path'];
                if (file_exists($file_path)) {
                    $files[] = array(
                        'path' => $file_path,
                        'relative_path' => $attachment['file_path'],
                        'size' => filesize($file_path),
                        'modified' => filemtime($file_path)
                    );
                }
            }
        }
        
        return $files;
    }
    
    /**
     * חישוב גודל כולל של קבצים
     */
    private function calculate_total_size($files) {
        $total = 0;
        foreach ($files as $file) {
            $total += isset($file['size']) ? $file['size'] : 0;
        }
        return $total;
    }
    
    /**
     * פורמט של bytes ל-readable format
     */
    public function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * יצירת דוחות
     */
    private function generate_reports($orphan_files, $thumbnails, $unused_attachments, $unused_files) {
        $timestamp = date('Y-m-d_H-i-s');
        
        // דוח 1: קבצים ללא רשומה
        if (!empty($orphan_files)) {
            $this->save_report('orphan_files_' . $timestamp . '.csv', $orphan_files);
        }
        
        // דוח 2: thumbnails
        if (!empty($thumbnails)) {
            $this->save_report('thumbnails_' . $timestamp . '.csv', $thumbnails);
        }
        
        // דוח 3: attachments לא בשימוש
        if (!empty($unused_attachments)) {
            $this->save_report('unused_attachments_' . $timestamp . '.csv', $unused_attachments);
        }
        
        // דוח 4: סיכום JSON
        $summary = array(
            'timestamp' => $timestamp,
            'stats' => $this->stats,
            'summary' => array(
                'orphan_files_count' => count($orphan_files),
                'orphan_files_size' => $this->format_bytes($this->stats['orphan_size']),
                'thumbnails_count' => count($thumbnails),
                'thumbnails_size' => $this->format_bytes($this->stats['thumbnail_size']),
                'unused_attachments_count' => count($unused_attachments),
                'unused_attachments_size' => $this->format_bytes($this->stats['unused_size']),
                'total_potential_savings' => $this->format_bytes(
                    $this->stats['orphan_size'] + $this->stats['thumbnail_size'] + $this->stats['unused_size']
                )
            )
        );
        
        file_put_contents(
            $this->report_dir . '/summary_' . $timestamp . '.json',
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        echo "דוחות נשמרו ב: " . $this->report_dir . "\n";
    }
    
    /**
     * שמירת דוח ל-CSV
     */
    private function save_report($filename, $data) {
        $filepath = $this->report_dir . '/' . $filename;
        $fp = fopen($filepath, 'w');
        
        if (empty($data)) {
            fclose($fp);
            return;
        }
        
        // כתיבת header
        $headers = array_keys($data[0]);
        fputcsv($fp, $headers);
        
        // כתיבת נתונים
        foreach ($data as $row) {
            $csv_row = array();
            foreach ($headers as $header) {
                $value = isset($row[$header]) ? $row[$header] : '';
                // המרת arrays/objects ל-JSON
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $csv_row[] = $value;
            }
            fputcsv($fp, $csv_row);
        }
        
        fclose($fp);
    }
    
    /**
     * הדפסת סיכום
     */
    private function print_summary() {
        echo "\n=== סיכום ===\n\n";
        echo "סה\"כ attachments בבסיס הנתונים: " . $this->stats['total_attachments'] . "\n";
        echo "סה\"כ קבצים פיזיים: " . $this->stats['total_files'] . "\n\n";
        
        echo "קבצים ללא רשומה ב-DB:\n";
        echo "  - כמות: " . $this->stats['orphan_files'] . "\n";
        echo "  - גודל: " . $this->format_bytes($this->stats['orphan_size']) . "\n\n";
        
        echo "Thumbnails:\n";
        echo "  - כמות: " . $this->stats['thumbnails'] . "\n";
        echo "  - גודל: " . $this->format_bytes($this->stats['thumbnail_size']) . "\n\n";
        
        echo "Attachments לא בשימוש:\n";
        echo "  - כמות: " . $this->stats['unused_attachments'] . "\n";
        echo "  - גודל: " . $this->format_bytes($this->stats['unused_size']) . "\n\n";
        
        $total_savings = $this->stats['orphan_size'] + $this->stats['thumbnail_size'] + $this->stats['unused_size'];
        echo "סה\"כ פוטנציאל שחרור: " . $this->format_bytes($total_savings) . "\n\n";
    }
    
    /**
     * פונקציה ראשית - מחיקה מבוקרת
     */
    public function cleanup($options = array()) {
        $dry_run = isset($options['dry_run']) ? $options['dry_run'] : true;
        $delete_orphans = isset($options['delete_orphans']) ? $options['delete_orphans'] : false;
        $delete_thumbnails = isset($options['delete_thumbnails']) ? $options['delete_thumbnails'] : false;
        $delete_unused = isset($options['delete_unused']) ? $options['delete_unused'] : false;
        $older_than = isset($options['older_than']) ? $options['older_than'] : null; // YYYY-MM-DD
        
        echo "=== ניקוי מדיה לא בשימוש ===\n";
        echo "Dry Run: " . ($dry_run ? 'Yes' : 'No') . "\n\n";
        
        // ניתוח ראשוני
        $results = $this->analyze('cleanup');
        
        $total_deleted = 0;
        $total_size = 0;
        
        // מחיקת orphan files
        if ($delete_orphans && !empty($results['orphan_files'])) {
            $orphans = $results['orphan_files'];
            
            // סינון לפי תאריך אם נדרש
            if ($older_than) {
                $cutoff = strtotime($older_than);
                $orphans = array_filter($orphans, function($file) use ($cutoff) {
                    return isset($file['modified']) && $file['modified'] < $cutoff;
                });
            }
            
            if (!empty($orphans)) {
                $result = $this->delete_files($orphans, 'orphan', $dry_run);
                $total_deleted += $result['deleted'];
                $total_size += $result['size'];
            }
        }
        
        // מחיקת thumbnails ישנים
        if ($delete_thumbnails && !empty($results['thumbnails'])) {
            $thumbnails = $results['thumbnails'];
            
            // סינון לפי תאריך אם נדרש
            if ($older_than) {
                $cutoff = strtotime($older_than);
                $thumbnails = array_filter($thumbnails, function($file) use ($cutoff) {
                    return isset($file['modified']) && $file['modified'] < $cutoff;
                });
            }
            
            if (!empty($thumbnails)) {
                $result = $this->delete_files($thumbnails, 'thumbnail', $dry_run);
                $total_deleted += $result['deleted'];
                $total_size += $result['size'];
            }
        }
        
        // מחיקת unused attachments
        if ($delete_unused && !empty($results['unused_attachments'])) {
            $unused = $results['unused_attachments'];
            
            // סינון לפי תאריך אם נדרש
            if ($older_than) {
                $cutoff = strtotime($older_than);
                $unused = array_filter($unused, function($att) use ($cutoff) {
                    return isset($att['post_date']) && strtotime($att['post_date']) < $cutoff;
                });
            }
            
            if (!empty($unused)) {
                $result = $this->delete_unused_attachments($unused, $dry_run);
                $total_deleted += $result['deleted'];
            }
        }
        
        echo "\n=== סיכום כולל ===\n";
        echo "סה\"כ קבצים שנמחקו: {$total_deleted}\n";
        if (!$dry_run) {
            echo "סה\"כ גודל שחרור: " . $this->format_bytes($total_size) . "\n";
        }
        
        return array('deleted' => $total_deleted, 'size' => $total_size);
    }
}

// הרצה אם נקרא ישירות
if (php_sapi_name() === 'cli' || (isset($_GET['run_media_cleanup']) && current_user_can('manage_options'))) {
    $cleanup = new Media_Cleanup();
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'analyze';
    
    if ($mode === 'cleanup') {
        $options = array(
            'dry_run' => !isset($_GET['execute']),
            'delete_orphans' => isset($_GET['orphans']),
            'delete_thumbnails' => isset($_GET['thumbnails']),
            'delete_unused' => isset($_GET['unused']),
            'older_than' => isset($_GET['older_than']) ? $_GET['older_than'] : null
        );
        $cleanup->cleanup($options);
    } else {
        $cleanup->analyze($mode);
    }
}

