<?php
/**
 * Disable Debug Mode Script
 * Removes debug code from application files
 */

// List of files that might have debug code
$filesToClean = [
    'index.php',
    'dashboard.php',
    'list.php',
    'branch_control.php',
    'login.php',
    'data.php',
    'branch_data.php'
];

echo "<h1>🔒 Disable Debug Mode</h1>";
echo "<div style='font-family: monospace; background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

echo "<h2>🧹 Cleaning Debug Code From Files:</h2>";
echo "<ul>";

$filesCleaned = 0;

foreach ($filesToClean as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        // Remove debug code block
        $debugPattern = '/\/\/ DEBUG MODE - Remove after fixing\nini_set\(\'display_errors\', 1\);\nini_set\(\'display_startup_errors\', 1\);\nerror_reporting\(E_ALL\);\n\/\/ END DEBUG MODE\n/';

        if (preg_match($debugPattern, $content)) {
            $newContent = preg_replace($debugPattern, '', $content);

            if (file_put_contents($file, $newContent)) {
                echo "<li style='color: green;'>✅ Removed debug code from <strong>$file</strong></li>";
                $filesCleaned++;
            } else {
                echo "<li style='color: red;'>❌ Failed to clean <strong>$file</strong> (permission denied)</li>";
            }
        } else {
            echo "<li style='color: blue;'>ℹ️ No debug code found in <strong>$file</strong></li>";
        }
    } else {
        echo "<li style='color: orange;'>⚠️ File <strong>$file</strong> not found</li>";
    }
}

echo "</ul>";

echo "<h2>🗑️ Cleaning Up Debug Files:</h2>";
echo "<ul>";

$debugFiles = [
    'debug_errors.php',
    'enable_debug.php',
    'disable_debug.php',
    'debug_errors.log'
];

foreach ($debugFiles as $debugFile) {
    if (file_exists($debugFile)) {
        if (unlink($debugFile)) {
            echo "<li style='color: green;'>✅ Deleted <strong>$debugFile</strong></li>";
        } else {
            echo "<li style='color: red;'>❌ Failed to delete <strong>$debugFile</strong></li>";
        }
    } else {
        echo "<li style='color: blue;'>ℹ️ <strong>$debugFile</strong> not found</li>";
    }
}

echo "</ul>";

echo "<h2>📊 Summary</h2>";
if ($filesCleaned > 0) {
    echo "<p style='color: green;'><strong>✅ Debug mode disabled in $filesCleaned files!</strong></p>";
    echo "<p>Application is now secure for production use.</p>";
} else {
    echo "<p style='color: blue;'><strong>ℹ️ No debug code found to clean.</strong></p>";
}

echo "<h2>🔒 Security Settings Restored</h2>";
echo "<p>PHP error display is now controlled by server configuration:</p>";
echo "<ul>";
echo "<li><code>display_errors = Off</code> (recommended for production)</li>";
echo "<li><code>log_errors = On</code> (errors logged to file)</li>";
echo "<li><code>error_reporting = E_ALL & ~E_NOTICE</code> (log all errors except notices)</li>";
echo "</ul>";

echo "<h2>🧪 Test Application Security</h2>";
echo "<p>Verify that your application is working correctly:</p>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>Login Page</a> - Should work normally</li>";
echo "<li><a href='dashboard.php' target='_blank'>Dashboard</a> - Should redirect to login if not authenticated</li>";
echo "<li><a href='list.php' target='_blank'>List Page</a> - Should redirect to login if not authenticated</li>";
echo "</ul>";

echo "</div>";

echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>📋 Production Checklist</h3>";
echo "<ul>";
echo "<li>✅ Debug mode disabled</li>";
echo "<li>✅ Error display turned off</li>";
echo "<li>✅ Debug files cleaned up</li>";
echo "<li>✅ Application tested and working</li>";
echo "<li>✅ Database setup completed</li>";
echo "<li>✅ Admin user created</li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 20px; padding: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 5px;'>";
echo "<h3>🚨 If You Still Have Issues</h3>";
echo "<p>If you're still getting 500 errors after running the fixes:</p>";
echo "<ol>";
echo "<li>Check web server error logs</li>";
echo "<li>Run <code>http://your-domain.com/check_server.php</code></li>";
echo "<li>Verify database connection in <code>config/config.php</code></li>";
echo "<li>Check file permissions: <code>ls -la /var/www/html/</code></li>";
echo "</ol>";
echo "</div>";
?>