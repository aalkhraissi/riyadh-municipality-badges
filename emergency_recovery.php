<?php
/**
 * Emergency Recovery Script for Riyadh Municipality Control System
 * Use this when the application is completely broken
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚨 Emergency Recovery - Riyadh Municipality System</h1>";
echo "<div style='font-family: monospace; background: #fff3cd; padding: 20px; border-radius: 5px; border: 1px solid #ffeaa7;'>";

// Step 1: Check if this is an emergency
echo "<h2>🔍 Step 1: System Diagnosis</h2>";
$issues = [];

// Check PHP version
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '<')) {
    $issues[] = "PHP version $phpVersion is too old (requires 7.4+)";
}

// Check critical files
$criticalFiles = [
    'index.php' => 'Login page',
    'config/config.php' => 'Configuration file',
    'db.php' => 'Database class'
];

foreach ($criticalFiles as $file => $description) {
    if (!file_exists($file)) {
        $issues[] = "$description ($file) is missing";
    } elseif (!is_readable($file)) {
        $issues[] = "$description ($file) is not readable";
    }
}

// Check database connection
$dbConnectionOk = false;
if (file_exists('config/config.php')) {
    try {
        require_once 'config/config.php';
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_usr, $db_password);
        $pdo->exec("USE `$db_name`");
        $dbConnectionOk = true;
    } catch (Exception $e) {
        $issues[] = "Database connection failed: " . $e->getMessage();
    }
} else {
    $issues[] = "Configuration file missing - cannot test database";
}

if (empty($issues)) {
    echo "<p style='color: green;'>✅ No critical issues found! The system might be working correctly.</p>";
    echo "<p><a href='index.php'>Go to login page</a></p>";
} else {
    echo "<p style='color: red;'>❌ Found " . count($issues) . " critical issues:</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
}

echo "</div>";

// Step 2: Quick fixes
echo "<h2>🛠️ Step 2: Quick Fixes</h2>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

// Fix 1: Create basic config if missing
if (!file_exists('config/config.php')) {
    echo "<h3>📝 Creating basic configuration file...</h3>";
    $configContent = "<?php
// Database configuration - UPDATE THESE VALUES!
\$base_url = 'https://your-domain.com';

\$db_host = 'localhost';      // Your MySQL host
\$db_name = 'records';        // Database name
\$db_usr = 'root';           // MySQL username
\$db_password = '';          // MySQL password

// IMPORTANT: Update these values for your server!
?>";

    if (!is_dir('config')) {
        mkdir('config', 0755, true);
    }

    if (file_put_contents('config/config.php', $configContent)) {
        echo "<p style='color: green;'>✅ Created config/config.php</p>";
        echo "<p style='color: red;'>⚠️ <strong>IMPORTANT:</strong> Edit config/config.php with your actual database credentials!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create config file - check permissions</p>";
    }
}

// Fix 2: Test database connection with new config
if (file_exists('config/config.php') && !$dbConnectionOk) {
    echo "<h3>🗄️ Testing database connection...</h3>";
    try {
        require_once 'config/config.php';
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_usr, $db_password);
        echo "<p style='color: green;'>✅ Database connection successful!</p>";

        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
        if ($stmt->fetch()) {
            echo "<p style='color: green;'>✅ Database '$db_name' exists!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Database '$db_name' does not exist</p>";
            echo "<p>💡 Run <code>setup_database.php</code> to create the database</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
        echo "<p>💡 Check your database credentials in config/config.php</p>";
    }
}

// Fix 3: Check file permissions
echo "<h3>📁 Checking file permissions...</h3>";
$writableDirs = ['config', 'js', 'css'];
foreach ($writableDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✅ $dir is writable</p>";
        } else {
            echo "<p style='color: red;'>❌ $dir is not writable</p>";
            echo "<p>💡 Run: <code>chmod 755 $dir</code></p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ $dir directory does not exist</p>";
    }
}

echo "</div>";

// Step 3: Recovery options
echo "<h2>🔄 Step 3: Recovery Options</h2>";
echo "<div style='font-family: monospace; background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

echo "<h3>🚀 Quick Recovery Steps:</h3>";
echo "<ol>";
echo "<li><strong>Update Configuration:</strong> Edit <code>config/config.php</code> with correct database details</li>";
echo "<li><strong>Setup Database:</strong> Run <code>http://your-domain.com/setup_database.php</code></li>";
echo "<li><strong>Check Permissions:</strong> Ensure web server can read/write files</li>";
echo "<li><strong>Test Login:</strong> Try accessing <code>http://your-domain.com/index.php</code></li>";
echo "</ol>";

echo "<h3>📞 Advanced Recovery:</h3>";
echo "<ul>";
echo "<li><strong>Server Check:</strong> Run <code>check_server.php</code> for detailed diagnostics</li>";
echo "<li><strong>Manual Setup:</strong> Use <code>setup_database_cli.php</code> for command-line setup</li>";
echo "<li><strong>Logs:</strong> Check web server and PHP error logs</li>";
echo "<li><strong>Backup:</strong> Restore from backup if available</li>";
echo "</ul>";

echo "<h3>🆘 Emergency Contacts:</h3>";
echo "<ul>";
echo "<li><strong>Hosting Support:</strong> Contact your hosting provider</li>";
echo "<li><strong>Server Admin:</strong> Contact your server administrator</li>";
echo "<li><strong>Developer:</strong> Contact the system developer</li>";
echo "</ul>";

echo "</div>";

// Step 4: System information
echo "<h2>ℹ️ Step 4: System Information</h2>";
echo "<div style='font-family: monospace; background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>PHP Version:</strong> $phpVersion</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Script:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s T') . "</p>";
echo "</div>";

// Footer
echo "<div style='margin-top: 30px; padding: 20px; background: #ffebee; border: 1px solid #f44336; border-radius: 5px;'>";
echo "<h3 style='color: #d32f2f;'>⚠️ Security Notice</h3>";
echo "<p><strong>Important:</strong> Delete this recovery script after fixing the issues to prevent unauthorized access.</p>";
echo "<p>Files to delete after recovery:</p>";
echo "<ul>";
echo "<li><code>emergency_recovery.php</code></li>";
echo "<li><code>check_server.php</code></li>";
echo "<li><code>setup_database.php</code></li>";
echo "<li><code>setup_database_cli.php</code></li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 20px; text-align: center;'>";
echo "<p><a href='index.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Login Page</a></p>";
echo "</div>";
?>