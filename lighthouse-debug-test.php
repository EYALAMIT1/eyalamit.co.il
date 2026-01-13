<?php
// Lighthouse Debug Test - Simplified version for performance testing
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to avoid interfering with Lighthouse

// Basic performance metrics
$start_time = microtime(true);

// Simple HTML output without external scripts
?>
<!DOCTYPE html>
<html lang="he-IL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Test - דיג'רידו</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1 { color: #333; }
        .content { max-width: 800px; margin: 0 auto; }
        .performance-metrics {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>מרכז לטיפול בדיג'רידו</h1>
        <p>סטודיו נשימה מעגלית - אייל עמית</p>

        <div class="performance-metrics">
            <h3>Performance Test Metrics</h3>
            <p>Page loaded successfully</p>
            <p>Load time: <?php echo round((microtime(true) - $start_time) * 1000, 2); ?>ms</p>
            <p>Timestamp: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div style="background: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2>תוכן לדוגמה</h2>
            <p>זהו עמוד בדיקת ביצועים פשוט ללא סקריפטים חיצוניים.</p>
            <p>העמוד נטען בהצלחה ובמהירות.</p>
        </div>
    </div>
</body>
</html>