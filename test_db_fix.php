<?php
/**
 * Test Database Fix Script
 * Verifies that all database queries work with the corrected column names
 */

require_once './config/config.php';
require_once 'db.php';

echo "<h1>🧪 Database Column Fix Test</h1>";
echo "<div style='font-family: monospace; background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "<h2>✅ Connected to database '$db_name'</h2>";
} catch (Exception $e) {
    die("<h2>❌ Database Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
}

$testsPassed = 0;
$totalTests = 0;

// Test 1: getUserByName method
echo "<h3>🧪 Test 1: getUserByName() Method</h3>";
$totalTests++;
try {
    $user = $db->getUserByName('admin');
    if ($user) {
        echo "<p style='color: green;'>✅ getUserByName('admin') returned user data</p>";
        echo "<p>User ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}</p>";
        $testsPassed++;
    } else {
        echo "<p style='color: red;'>❌ getUserByName('admin') returned null</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getUserByName() failed: " . $e->getMessage() . "</p>";
}

// Test 2: getAllUsers method
echo "<h3>🧪 Test 2: getAllUsers() Method</h3>";
$totalTests++;
try {
    $users = $db->getAllUsers();
    if (is_array($users)) {
        echo "<p style='color: green;'>✅ getAllUsers() returned " . count($users) . " users</p>";
        $testsPassed++;
    } else {
        echo "<p style='color: red;'>❌ getAllUsers() did not return array</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getAllUsers() failed: " . $e->getMessage() . "</p>";
}

// Test 3: getUserById method
echo "<h3>🧪 Test 3: getUserById() Method</h3>";
$totalTests++;
try {
    $user = $db->getUserById(1);
    if ($user) {
        echo "<p style='color: green;'>✅ getUserById(1) returned user data</p>";
        echo "<p>User: {$user['username']}, Role: {$user['role']}, Active: " . ($user['is_active'] ? 'Yes' : 'No') . "</p>";
        $testsPassed++;
    } else {
        echo "<p style='color: orange;'>⚠️ getUserById(1) returned null (user may not exist)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getUserById() failed: " . $e->getMessage() . "</p>";
}

// Test 4: authenticateUser method
echo "<h3>🧪 Test 4: authenticateUser() Method</h3>";
$totalTests++;
try {
    $authResult = $db->authenticateUser('admin', 'admin123');
    if ($authResult) {
        echo "<p style='color: green;'>✅ authenticateUser('admin', 'admin123') succeeded</p>";
        $testsPassed++;
    } else {
        echo "<p style='color: red;'>❌ authenticateUser('admin', 'admin123') failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ authenticateUser() failed: " . $e->getMessage() . "</p>";
}

// Test 5: getAllFiltered method
echo "<h3>🧪 Test 5: getAllFiltered() Method</h3>";
$totalTests++;
try {
    $records = $db->getAllFiltered('all_branches', null);
    if (is_array($records)) {
        echo "<p style='color: green;'>✅ getAllFiltered() returned " . count($records) . " records</p>";
        $testsPassed++;
    } else {
        echo "<p style='color: red;'>❌ getAllFiltered() did not return array</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getAllFiltered() failed: " . $e->getMessage() . "</p>";
}

// Test 6: getBranches method
echo "<h3>🧪 Test 6: getBranches() Method</h3>";
$totalTests++;
try {
    $branches = $db->getBranches();
    if (is_array($branches)) {
        echo "<p style='color: green;'>✅ getBranches() returned " . count($branches) . " branches</p>";
        $testsPassed++;
    } else {
        echo "<p style='color: red;'>❌ getBranches() did not return array</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getBranches() failed: " . $e->getMessage() . "</p>";
}

// Test 7: addUser method (test with a temporary user)
echo "<h3>🧪 Test 7: addUser() Method</h3>";
$totalTests++;
try {
    $testUsername = 'test_user_' . time();
    $result = $db->addUser($testUsername, 'testpass123', 'Test User', 'user', null, true);
    if ($result) {
        echo "<p style='color: green;'>✅ addUser() method works correctly</p>";
        $testsPassed++;

        // Clean up test user
        $stmt = $db->executePreparedQuery("DELETE FROM users WHERE username = ?", [$testUsername]);
        echo "<p style='color: blue;'>ℹ️ Cleaned up test user</p>";
    } else {
        echo "<p style='color: red;'>❌ addUser() method failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ addUser() failed: " . $e->getMessage() . "</p>";
}

// Test Results Summary
echo "</div>";
echo "<h1>📊 Test Results Summary</h1>";
echo "<div style='background: " . ($testsPassed == $totalTests ? '#e8f5e8' : '#ffebee') . "; padding: 20px; border-radius: 5px; border: 1px solid " . ($testsPassed == $totalTests ? '#4caf50' : '#f44336') . ";'>";

echo "<h2>" . ($testsPassed == $totalTests ? '🎉 All Tests Passed!' : '⚠️ Some Tests Failed') . "</h2>";
echo "<p><strong>Tests Passed:</strong> $testsPassed / $totalTests</p>";
echo "<p><strong>Success Rate:</strong> " . round(($testsPassed / $totalTests) * 100, 1) . "%</p>";

if ($testsPassed == $totalTests) {
    echo "<h3>✅ Database Column Fix Successful!</h3>";
    echo "<p>All database queries are now working with the corrected column names.</p>";
    echo "<p>Your application should no longer have the 'Unknown column' error.</p>";
} else {
    echo "<h3>⚠️ Some Tests Failed</h3>";
    echo "<p>Some database methods are still having issues. Check the error messages above.</p>";
}

echo "</div>";

// Next Steps
echo "<h2>🚀 Next Steps</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; border: 1px solid #ffeaa7;'>";

if ($testsPassed == $totalTests) {
    echo "<h3>✅ Ready to Test Application</h3>";
    echo "<ol>";
    echo "<li><strong>Test Login:</strong> <a href='index.php' target='_blank'>index.php</a></li>";
    echo "<li><strong>Login Credentials:</strong> admin / admin123</li>";
    echo "<li><strong>Check Dashboard:</strong> Should load without 500 error</li>";
    echo "<li><strong>Test Features:</strong> Try user management, branch control, etc.</li>";
    echo "</ol>";
} else {
    echo "<h3>🔧 Additional Fixes Needed</h3>";
    echo "<ol>";
    echo "<li>Review the failed test error messages above</li>";
    echo "<li>Check database table structure</li>";
    echo "<li>Run <code>http://your-domain.com/quick_fix.php</code> again</li>";
    echo "<li>Verify column names match the queries</li>";
    echo "</ol>";
}

echo "<h3>🛡️ Security Cleanup</h3>";
echo "<p>After successful testing, delete these debug files:</p>";
echo "<ul>";
echo "<li><code>test_db_fix.php</code></li>";
echo "<li><code>debug_errors.php</code></li>";
echo "<li><code>enable_debug.php</code></li>";
echo "<li><code>disable_debug.php</code></li>";
echo "</ul>";
echo "</div>";
?>