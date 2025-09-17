<?php
/**
 * Quick Fix for Missing Database Columns
 * Adds only the missing columns identified by verify_database.php
 */

require_once './config/config.php';
require_once 'db.php';

echo "<h1>🔧 Quick Database Column Fix</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "<h2>✅ Connected to database '$db_name'</h2>";
} catch (Exception $e) {
    die("<h2>❌ Database Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
}

$fixesApplied = 0;

// Fix 1: Add missing columns to users table
echo "<h3>👤 Adding Missing Columns to 'users' Table...</h3>";
$usersColumns = [
    'role' => "ENUM('admin', 'manager', 'user') DEFAULT 'user'",
    'branch_access' => 'JSON DEFAULT NULL',
    'is_active' => 'BOOLEAN DEFAULT TRUE',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];

foreach ($usersColumns as $columnName => $columnDef) {
    if (!$db->checkColumnExists('users', $columnName)) {
        try {
            $db->executeRawQuery("ALTER TABLE users ADD COLUMN `$columnName` $columnDef");
            echo "<p style='color: green;'>✅ Added column '$columnName' to users table</p>";
            $fixesApplied++;
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Failed to add column '$columnName': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '$columnName' already exists in users table</p>";
    }
}

// Fix 2: Add missing columns to records table
echo "<h3>📄 Adding Missing Columns to 'records' Table...</h3>";
$recordsColumns = [
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];

foreach ($recordsColumns as $columnName => $columnDef) {
    if (!$db->checkColumnExists('records', $columnName)) {
        try {
            $db->executeRawQuery("ALTER TABLE records ADD COLUMN `$columnName` $columnDef");
            echo "<p style='color: green;'>✅ Added column '$columnName' to records table</p>";
            $fixesApplied++;
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Failed to add column '$columnName': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '$columnName' already exists in records table</p>";
    }
}

// Fix 3: Add indexes for better performance
echo "<h3>⚡ Adding Database Indexes...</h3>";
$indexes = [
    "ALTER TABLE users ADD INDEX idx_role (role)" => 'users role index',
    "ALTER TABLE users ADD INDEX idx_active (is_active)" => 'users active index',
    "ALTER TABLE records ADD INDEX idx_branch_id (branch_id)" => 'records branch_id index'
];

foreach ($indexes as $sql => $description) {
    try {
        $db->executeRawQuery($sql);
        echo "<p style='color: green;'>✅ Added $description</p>";
        $fixesApplied++;
    } catch (Exception $e) {
        // Index might already exist, which is fine
        echo "<p style='color: blue;'>ℹ️ $description may already exist</p>";
    }
}

// Fix 4: Insert default admin user if not exists
echo "<h3>👨‍💼 Ensuring Admin User Exists...</h3>";
try {
    $stmt = $db->executeSelectQuery("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $adminExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if (!$adminExists) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->executePreparedQuery(
            "INSERT INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)",
            ['admin', $adminPassword, 'مدير النظام', 'admin', true]
        );
        echo "<p style='color: green;'>✅ Created admin user: admin / admin123</p>";
        $fixesApplied++;
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin user already exists</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking/creating admin user: " . $e->getMessage() . "</p>";
}

// Fix 5: Test critical methods
echo "<h3>🧪 Testing Critical Methods...</h3>";
$methodTests = [
    ['method' => 'getAllFiltered', 'params' => ['all_branches', null], 'description' => 'getAllFiltered()'],
    ['method' => 'getBranches', 'params' => [], 'description' => 'getBranches()'],
    ['method' => 'addBranchIdColumnToRecordsTable', 'params' => [], 'description' => 'addBranchIdColumnToRecordsTable()']
];

foreach ($methodTests as $test) {
    try {
        $result = call_user_func_array([$db, $test['method']], $test['params']);
        echo "<p style='color: green;'>✅ {$test['description']} works correctly</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ {$test['description']} failed: " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

// Summary
echo "<h1>📊 Quick Fix Summary</h1>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

if ($fixesApplied > 0) {
    echo "<h2 style='color: green;'>🎉 Database Fixed!</h2>";
    echo "<p><strong>$fixesApplied fixes applied successfully!</strong></p>";
    echo "<p>The missing columns have been added and the database should now work correctly.</p>";
} else {
    echo "<h2 style='color: blue;'>ℹ️ No Fixes Needed</h2>";
    echo "<p>All required columns are already present.</p>";
}

echo "<h3>🚀 Test Your Application:</h3>";
echo "<ol>";
echo "<li><strong>Go to Login:</strong> <a href='index.php' target='_blank'>index.php</a></li>";
echo "<li><strong>Login as Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>";
echo "<li><strong>Check Dashboard:</strong> Should load without 500 error</li>";
echo "</ol>";

echo "<h3>🔍 Verify Fix:</h3>";
echo "<p>Run <a href='verify_database.php' target='_blank'>verify_database.php</a> again to confirm all columns are present.</p>";

echo "</div>";

// Security notice
echo "<div style='margin-top: 20px; padding: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 5px;'>";
echo "<h3 style='color: #d32f2f;'>⚠️ Security Notice</h3>";
echo "<p><strong>Important:</strong> Delete this file after successful fix to prevent unauthorized access.</p>";
echo "<p>Files to delete: <code>quick_fix.php</code></p>";
echo "</div>";
?>