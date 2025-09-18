<?php
/**
 * Enable Debug Mode Script
 * Temporarily enables error display in main application files
 */

// List of main application files to enable debugging in
$filesToDebug = [
    'index.php',
    'dashboard.php',
    'list.php',
    'branch_control.php',
    'login.php',
    'data.php',
    'branch_data.php'
];

echo "<h1>🔧 Enable Debug Mode</h1>";
echo "<div style='font-family: monospace; background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

echo "<h2>📝 Files to Enable Debug Mode In:</h2>";
echo "<ul>";

$debugCode = "\n// DEBUG MODE - Remove after fixing\nini_set('display_errors', 1);\nini_set('display_startup_errors', 1);\nerror_reporting(E_ALL);\n// END DEBUG MODE\n";

$filesModified = 0;

foreach ($filesToDebug as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        // Check if debug code is already present
        if (strpos($content, '// DEBUG MODE - Remove after fixing') === false) {
            // Add debug code after opening PHP tag
            $content = preg_replace('/^<\?php\s*/', "<?php\n" . $debugCode, $content);

            if (file_put_contents($file, $content)) {
                echo "<li style='color: green;'>✅ Added debug mode to <strong>$file</strong></li>";
                $filesModified++;
            } else {
                echo "<li style='color: red;'>❌ Failed to modify <strong>$file</strong> (permission denied)</li>";
            }
        } else {
            echo "<li style='color: blue;'>ℹ️ Debug mode already enabled in <strong>$file</strong></li>";
        }
    } else {
        echo "<li style='color: orange;'>⚠️ File <strong>$file</strong> not found</li>";
    }
}

echo "</ul>";

echo "<h2>📊 Summary</h2>";
if ($filesModified > 0) {
    echo "<p style='color: green;'><strong>✅ Debug mode enabled in $filesModified files!</strong></p>";
    echo "<p>Now try accessing your application - PHP errors will be displayed.</p>";
} else {
    echo "<p style='color: blue;'><strong>ℹ️ Debug mode already enabled in all files.</strong></p>";
}

echo "<h2>🧪 Test Your Application</h2>";
echo "<p>Try these URLs to see if errors are now displayed:</p>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>Login Page</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Dashboard (after login)</a></li>";
echo "<li><a href='list.php' target='_blank'>List Page (after login)</a></li>";
echo "</ul>";

echo "<h2>📋 What to Look For</h2>";
echo "<p>When you get a 500 error, you should now see the actual PHP error message instead of a blank page.</p>";
echo "<p>Common error messages to look for:</p>";
echo "<ul>";
echo "<li><code>Fatal error: Call to undefined method</code> - Missing database method</li>";
echo "<li><code>Fatal error: Class 'Database' not found</code> - Missing db.php file</li>";
echo "<li><code>Warning: mysqli_connect() failed</code> - Database connection issue</li>";
echo "<li><code>Fatal error: Cannot redeclare function</code> - Function conflict</li>";
echo "</ul>";

echo "</div>";

echo "<div style='margin-top: 20px; padding: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 5px;'>";
echo "<h3>⚠️ Security Warning</h3>";
echo "<p><strong>Important:</strong> Debug mode exposes sensitive error information to users.</p>";
echo "<p><strong>Do not use in production!</strong></p>";
echo "<p><strong>To disable:</strong> Run <code>disable_debug.php</code> or manually remove the debug code from files.</p>";
echo "</div>";

echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>🔄 Next Steps</h3>";
echo "<ol>";
echo "<li>Try accessing your application pages</li>";
echo "<li>Note down any error messages you see</li>";
echo "<li>Fix the specific errors found</li>";
echo "<li>Run <code>disable_debug.php</code> to turn off debug mode</li>";
echo "</ol>";
echo "</div>";
?>