<?php
/**
 * Debug Errors Script
 * Temporarily enables error display for debugging 500 errors
 */

// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Also enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_errors.log');

echo "<h1>🐛 Debug Mode Enabled</h1>";
echo "<div style='font-family: monospace; background: #ffebee; padding: 20px; border-radius: 5px; border: 1px solid #f44336;'>";

echo "<h2>✅ Error Display Settings</h2>";
echo "<p><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'Enabled' : 'Disabled') . "</p>";
echo "<p><strong>Display Startup Errors:</strong> " . (ini_get('display_startup_errors') ? 'Enabled' : 'Disabled') . "</p>";
echo "<p><strong>Error Reporting:</strong> " . ini_get('error_reporting') . " (E_ALL = " . E_ALL . ")</p>";
echo "<p><strong>Error Log:</strong> " . ini_get('error_log') . "</p>";

echo "<h2>🧪 Test Error Display</h2>";
echo "<p>Testing error display with a deliberate error:</p>";

// Test 1: Notice error
echo "<p><strong>Notice:</strong> ";
trigger_error("This is a test notice", E_USER_NOTICE);

// Test 2: Warning error
echo "<p><strong>Warning:</strong> ";
trigger_error("This is a test warning", E_USER_WARNING);

// Test 3: Try to access undefined variable
echo "<p><strong>Undefined variable:</strong> ";
@$undefined_variable;
echo "Should show notice above</p>";

// Test 4: Try database connection
echo "<h2>🗄️ Test Database Connection</h2>";
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
    require_once 'db.php';

    try {
        $db = new Database($db_host, $db_name, $db_usr, $db_password);
        echo "<p style='color: green;'>✅ Database connection successful</p>";

        // Test the problematic methods
        echo "<h3>🧪 Testing Problematic Methods</h3>";

        // Test 1: addBranchIdColumnToRecordsTable
        try {
            $result = $db->addBranchIdColumnToRecordsTable();
            echo "<p style='color: green;'>✅ addBranchIdColumnToRecordsTable() executed successfully</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ addBranchIdColumnToRecordsTable() failed: " . $e->getMessage() . "</p>";
        }

        // Test 2: getAllFiltered
        try {
            $result = $db->getAllFiltered('all_branches', null);
            echo "<p style='color: green;'>✅ getAllFiltered() executed successfully - returned " . count($result) . " records</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ getAllFiltered() failed: " . $e->getMessage() . "</p>";
        }

        // Test 3: getBranches
        try {
            $result = $db->getBranches();
            echo "<p style='color: green;'>✅ getBranches() executed successfully - returned " . count($result) . " branches</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ getBranches() failed: " . $e->getMessage() . "</p>";
        }

    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ config/config.php not found</p>";
}

echo "<h2>🚀 Test Application Pages</h2>";
echo "<p>Now try accessing your application pages - errors should be displayed:</p>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>Login Page</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Dashboard (requires login)</a></li>";
echo "<li><a href='list.php' target='_blank'>List Page (requires login)</a></li>";
echo "</ul>";

echo "<h2>📝 Error Log</h2>";
echo "<p>Errors are also being logged to: <code>" . __DIR__ . "/debug_errors.log</code></p>";
if (file_exists('debug_errors.log')) {
    echo "<p><strong>Current log contents:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 200px; overflow: auto;'>";
    echo htmlspecialchars(file_get_contents('debug_errors.log'));
    echo "</pre>";
} else {
    echo "<p>No errors logged yet.</p>";
}

echo "</div>";

echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>⚠️ Security Notice</h3>";
echo "<p><strong>Important:</strong> This debug mode exposes sensitive error information. Do not use in production!</p>";
echo "<p><strong>To disable:</strong> Delete this file or comment out the ini_set lines at the top.</p>";
echo "<p><strong>Production setting:</strong> Set <code>display_errors = Off</code> in php.ini</p>";
echo "</div>";
?>