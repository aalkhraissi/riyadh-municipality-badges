<?php
/**
 * Server Compatibility Check Script
 * Run this to diagnose 500 errors on production server
 */

echo "<h1>🔍 Riyadh Municipality - Server Compatibility Check</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

// PHP Version Check
echo "<h2>📋 PHP Version Check</h2>";
$phpVersion = phpversion();
$requiredVersion = '7.4.0';
$versionOk = version_compare($phpVersion, $requiredVersion, '>=');

echo "Current PHP Version: <strong>$phpVersion</strong><br>";
echo "Required Version: <strong>$requiredVersion+</strong><br>";
echo "Status: " . ($versionOk ? "<span style='color: green;'>✅ Compatible</span>" : "<span style='color: red;'>❌ Upgrade Required</span>") . "<br><br>";

// PHP Extensions Check
echo "<h2>🔧 PHP Extensions Check</h2>";
$requiredExtensions = [
    'pdo' => 'PDO Database Access',
    'pdo_mysql' => 'MySQL PDO Driver',
    'json' => 'JSON Support',
    'mbstring' => 'Multibyte String Support',
    'openssl' => 'SSL/TLS Support',
    'session' => 'Session Support',
    'curl' => 'cURL Support (for AJAX)',
    'fileinfo' => 'File Information',
    'zip' => 'ZIP Archive Support'
];

$missingExtensions = [];
foreach ($requiredExtensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    echo "$description ($ext): " . ($loaded ? "<span style='color: green;'>✅ Loaded</span>" : "<span style='color: red;'>❌ Missing</span>") . "<br>";
    if (!$loaded) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "<br><strong>❌ Missing Extensions:</strong> " . implode(', ', $missingExtensions) . "<br>";
    echo "<strong>💡 Solution:</strong> Install missing extensions or contact your hosting provider<br>";
}

// PHP Configuration Check
echo "<h2>⚙️ PHP Configuration Check</h2>";
$phpConfig = [
    'memory_limit' => ['value' => ini_get('memory_limit'), 'recommended' => '128M', 'description' => 'Memory Limit'],
    'max_execution_time' => ['value' => ini_get('max_execution_time'), 'recommended' => '300', 'description' => 'Max Execution Time'],
    'upload_max_filesize' => ['value' => ini_get('upload_max_filesize'), 'recommended' => '10M', 'description' => 'Upload Max Filesize'],
    'post_max_size' => ['value' => ini_get('post_max_size'), 'recommended' => '10M', 'description' => 'POST Max Size'],
    'max_file_uploads' => ['value' => ini_get('max_file_uploads'), 'recommended' => '20', 'description' => 'Max File Uploads']
];

foreach ($phpConfig as $key => $config) {
    echo "{$config['description']}: <strong>{$config['value']}</strong> (Recommended: {$config['recommended']})<br>";
}

// File Permissions Check
echo "<h2>📁 File Permissions Check</h2>";
$filesToCheck = [
    'config/config.php' => 'Configuration File',
    'db.php' => 'Database Class',
    'index.php' => 'Login Page',
    'list.php' => 'Main List Page',
    'dashboard.php' => 'Dashboard Page'
];

foreach ($filesToCheck as $file => $description) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        $readable = is_readable($file);
        $writable = is_writable($file);

        echo "$description ($file): ";
        echo "Permissions: <strong>$perms</strong> | ";
        echo "Readable: " . ($readable ? "<span style='color: green;'>✅</span>" : "<span style='color: red;'>❌</span>") . " | ";
        echo "Writable: " . ($writable ? "<span style='color: green;'>✅</span>" : "<span style='color: red;'>❌</span>") . "<br>";
    } else {
        echo "$description ($file): <span style='color: red;'>❌ File not found</span><br>";
    }
}

// Database Connection Test
echo "<h2>🗄️ Database Connection Test</h2>";
if (file_exists('config/config.php')) {
    require_once 'config/config.php';

    try {
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_usr, $db_password);
        echo "✅ MySQL Connection: <span style='color: green;'>Successful</span><br>";

        // Test database existence
        $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
        $dbExists = $stmt->fetch();

        if ($dbExists) {
            echo "✅ Database '$db_name': <span style='color: green;'>Exists</span><br>";

            // Test database access
            $pdo->exec("USE `$db_name`");
            echo "✅ Database Access: <span style='color: green;'>Granted</span><br>";
        } else {
            echo "⚠️ Database '$db_name': <span style='color: orange;'>Does not exist</span><br>";
            echo "<strong>💡 Solution:</strong> Run setup_database.php to create the database<br>";
        }

    } catch (PDOException $e) {
        echo "❌ Database Connection: <span style='color: red;'>Failed</span><br>";
        echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>💡 Solutions:</strong><br>";
        echo "- Check database credentials in config/config.php<br>";
        echo "- Verify MySQL server is running<br>";
        echo "- Check firewall settings<br>";
        echo "- Contact hosting provider for database access<br>";
    }
} else {
    echo "❌ Configuration File: <span style='color: red;'>config/config.php not found</span><br>";
    echo "<strong>💡 Solution:</strong> Create config/config.php with database credentials<br>";
}

// Error Reporting Check
echo "<h2>🐛 Error Reporting</h2>";
$errorReporting = ini_get('display_errors');
$errorLog = ini_get('error_log');

echo "Display Errors: <strong>$errorReporting</strong><br>";
echo "Error Log: <strong>$errorLog</strong><br>";

if ($errorReporting == '0' || $errorReporting == 'Off') {
    echo "<strong>⚠️ Warning:</strong> Error display is disabled. Enable for debugging:<br>";
    echo "<code>ini_set('display_errors', 1);</code> in your PHP files<br>";
}

// Server Information
echo "<h2>ℹ️ Server Information</h2>";
echo "Server Software: <strong>" . $_SERVER['SERVER_SOFTWARE'] . "</strong><br>";
echo "Document Root: <strong>" . $_SERVER['DOCUMENT_ROOT'] . "</strong><br>";
echo "Script Path: <strong>" . __FILE__ . "</strong><br>";
echo "Current Directory: <strong>" . __DIR__ . "</strong><br>";

// Test file creation
echo "<h2>✏️ File Creation Test</h2>";
$testFile = 'test_write.tmp';
$writeTest = @file_put_contents($testFile, 'Test content');

if ($writeTest !== false) {
    echo "✅ File Creation: <span style='color: green;'>Successful</span><br>";
    unlink($testFile); // Clean up
} else {
    echo "❌ File Creation: <span style='color: red;'>Failed</span><br>";
    echo "<strong>💡 Solution:</strong> Check directory permissions (should be 755 or 775)<br>";
}

echo "</div>";

// Summary and Recommendations
echo "<h1>📊 Summary & Recommendations</h1>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

$issues = [];

if (!$versionOk) $issues[] = "PHP version too old";
if (!empty($missingExtensions)) $issues[] = "Missing PHP extensions";
if (!file_exists('config/config.php')) $issues[] = "Missing configuration file";

if (empty($issues)) {
    echo "<h2 style='color: green;'>✅ Server appears compatible!</h2>";
    echo "<p>If you're still getting 500 errors, check:</p>";
    echo "<ul>";
    echo "<li>PHP error logs on your server</li>";
    echo "<li>Web server error logs (Apache/Nginx)</li>";
    echo "<li>File permissions (should be 644 for files, 755 for directories)</li>";
    echo "<li>Database connection details</li>";
    echo "<li>Memory limits and execution time</li>";
    echo "</ul>";
} else {
    echo "<h2 style='color: red;'>❌ Issues Found:</h2>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "<p><strong>💡 Fix these issues before proceeding.</strong></p>";
}

echo "<h3>🚀 Next Steps:</h3>";
echo "<ol>";
echo "<li>Fix any issues listed above</li>";
echo "<li>Run <code>setup_database.php</code> to create database tables</li>";
echo "<li>Test the application at <code>index.php</code></li>";
echo "<li>Delete setup files for security</li>";
echo "</ol>";

echo "<h3>🔧 Quick Fixes:</h3>";
echo "<ul>";
echo "<li><strong>Enable error display:</strong> Add <code>ini_set('display_errors', 1);</code> to your PHP files</li>";
echo "<li><strong>Check file permissions:</strong> Files should be 644, directories 755</li>";
echo "<li><strong>Database issues:</strong> Verify credentials in config/config.php</li>";
echo "<li><strong>Memory issues:</strong> Increase memory_limit in php.ini</li>";
echo "</ul>";

echo "</div>";
?>