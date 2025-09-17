<?php
/**
 * Database Methods Test Script
 * Tests if all database methods used in the application are working
 */

require_once './config/config.php';
require_once 'db.php';

echo "<h1>🔬 Database Methods Test</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "<h2>✅ Database Connection Successful</h2>";
} catch (Exception $e) {
    die("<h2>❌ Database Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
}

// Test 1: addBranchIdColumnToRecordsTable method
echo "<h3>🧪 Test 1: addBranchIdColumnToRecordsTable()</h3>";
try {
    $result = $db->addBranchIdColumnToRecordsTable();
    if ($result) {
        echo "<p style='color: green;'>✅ Method executed successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Method returned false</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Method threw exception: " . $e->getMessage() . "</p>";
}

// Test 2: getAllFiltered method
echo "<h3>🧪 Test 2: getAllFiltered()</h3>";
try {
    $result = $db->getAllFiltered('all_branches', null);
    if (is_array($result)) {
        echo "<p style='color: green;'>✅ Method executed successfully - returned " . count($result) . " records</p>";
    } else {
        echo "<p style='color: red;'>❌ Method did not return array</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Method threw exception: " . $e->getMessage() . "</p>";
}

// Test 3: getBranches method
echo "<h3>🧪 Test 3: getBranches()</h3>";
try {
    $result = $db->getBranches();
    if (is_array($result)) {
        echo "<p style='color: green;'>✅ Method executed successfully - returned " . count($result) . " branches</p>";
    } else {
        echo "<p style='color: red;'>❌ Method did not return array</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Method threw exception: " . $e->getMessage() . "</p>";
}

// Test 4: Check if required tables exist
echo "<h3>🧪 Test 4: Required Tables Check</h3>";
$requiredTables = ['users', 'branches', 'records'];
$missingTables = [];

foreach ($requiredTables as $table) {
    try {
        $stmt = $db->executeSelectQuery("SHOW TABLES LIKE '$table'");
        if ($stmt && $stmt->fetch()) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
            $missingTables[] = $table;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error checking table '$table': " . $e->getMessage() . "</p>";
        $missingTables[] = $table;
    }
}

// Test 5: Check critical columns
echo "<h3>🧪 Test 5: Critical Columns Check</h3>";
$criticalColumns = [
    'records' => ['id', 'number', 'name', 'email', 'branch_id'],
    'users' => ['id', 'username', 'password', 'name'],
    'branches' => ['id', 'name']
];

foreach ($criticalColumns as $table => $columns) {
    echo "<p><strong>Table: $table</strong></p>";
    foreach ($columns as $column) {
        try {
            if ($db->checkColumnExists($table, $column)) {
                echo "<p style='color: green;'>✅ Column '$column' exists</p>";
            } else {
                echo "<p style='color: red;'>❌ Column '$column' missing</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error checking column '$column': " . $e->getMessage() . "</p>";
        }
    }
}

// Test 6: Simulate dashboard load
echo "<h3>🧪 Test 6: Dashboard Simulation</h3>";
try {
    // Simulate what dashboard.php does
    $db->addBranchIdColumnToRecordsTable();
    $userBranchAccessType = 'all_branches';
    $userAssignedBranches = null;
    $data = $db->getAllFiltered($userBranchAccessType, $userAssignedBranches);
    $branches = $db->getBranches();

    echo "<p style='color: green;'>✅ Dashboard simulation successful</p>";
    echo "<p>Data records: " . count($data) . "</p>";
    echo "<p>Branches: " . count($branches) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Dashboard simulation failed: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Recommendations
echo "<h1>💡 Recommendations</h1>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

if (!empty($missingTables)) {
    echo "<h3>🚨 Critical Issues Found:</h3>";
    echo "<ul>";
    foreach ($missingTables as $table) {
        echo "<li>Missing table: <code>$table</code></li>";
    }
    echo "</ul>";
    echo "<p><strong>Solution:</strong> Run <code>http://your-domain.com/setup_database.php</code></p>";
} else {
    echo "<h3>✅ All Critical Tables Present</h3>";
    echo "<p>Database structure looks good! If you're still getting 500 errors, check:</p>";
}

echo "<h3>🔧 Troubleshooting Steps:</h3>";
echo "<ol>";
echo "<li><strong>Run Database Setup:</strong> <code>http://your-domain.com/setup_database.php</code></li>";
echo "<li><strong>Verify Database:</strong> <code>http://your-domain.com/verify_database.php</code></li>";
echo "<li><strong>Check Server:</strong> <code>http://your-domain.com/check_server.php</code></li>";
echo "<li><strong>Enable Error Logging:</strong> Add <code>ini_set('display_errors', 1);</code> to PHP files</li>";
echo "<li><strong>Check Logs:</strong> Review web server and PHP error logs</li>";
echo "</ol>";

echo "<h3>📞 Quick Fix Commands:</h3>";
echo "<p><strong>If on Ubuntu/Debian:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px;'>";
echo "sudo apt-get install php8.1-mysql php8.1-json php8.1-mbstring\n";
echo "sudo systemctl restart apache2\n";
echo "mysql -u root -p -e \"CREATE DATABASE IF NOT EXISTS records;\"\n";
echo "mysql -u root -p -e \"GRANT ALL ON records.* TO 'your_user'@'localhost';\"\n";
echo "</pre>";

echo "</div>";
?>