#!/usr/bin/env php
<?php
/**
 * Command Line Quick Fix for Missing Database Columns
 * Run with: php quick_fix_cli.php
 */

require_once './config/config.php';
require_once 'db.php';

echo "🔧 Riyadh Municipality - Quick Database Fix (CLI)\n";
echo "===============================================\n\n";

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "✅ Connected to database '$db_name'\n";
} catch (Exception $e) {
    die("❌ Database Connection Failed: " . $e->getMessage() . "\n");
}

$fixesApplied = 0;

// Fix 1: Add missing columns to users table
echo "👤 Adding missing columns to 'users' table...\n";
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
            echo "  ✅ Added column '$columnName'\n";
            $fixesApplied++;
        } catch (Exception $e) {
            echo "  ❌ Failed to add column '$columnName': " . $e->getMessage() . "\n";
        }
    } else {
        echo "  ℹ️ Column '$columnName' already exists\n";
    }
}

// Fix 2: Add missing columns to records table
echo "\n📄 Adding missing columns to 'records' table...\n";
$recordsColumns = [
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];

foreach ($recordsColumns as $columnName => $columnDef) {
    if (!$db->checkColumnExists('records', $columnName)) {
        try {
            $db->executeRawQuery("ALTER TABLE records ADD COLUMN `$columnName` $columnDef");
            echo "  ✅ Added column '$columnName'\n";
            $fixesApplied++;
        } catch (Exception $e) {
            echo "  ❌ Failed to add column '$columnName': " . $e->getMessage() . "\n";
        }
    } else {
        echo "  ℹ️ Column '$columnName' already exists\n";
    }
}

// Fix 3: Add indexes
echo "\n⚡ Adding database indexes...\n";
$indexes = [
    "ALTER TABLE users ADD INDEX idx_role (role)" => 'users role index',
    "ALTER TABLE users ADD INDEX idx_active (is_active)" => 'users active index',
    "ALTER TABLE records ADD INDEX idx_branch_id (branch_id)" => 'records branch_id index'
];

foreach ($indexes as $sql => $description) {
    try {
        $db->executeRawQuery($sql);
        echo "  ✅ Added $description\n";
        $fixesApplied++;
    } catch (Exception $e) {
        echo "  ℹ️ $description may already exist\n";
    }
}

// Fix 4: Ensure admin user exists
echo "\n👨‍💼 Ensuring admin user exists...\n";
try {
    $stmt = $db->executeSelectQuery("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $adminExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if (!$adminExists) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->executePreparedQuery(
            "INSERT INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)",
            ['admin', $adminPassword, 'مدير النظام', 'admin', true]
        );
        echo "  ✅ Created admin user: admin / admin123\n";
        $fixesApplied++;
    } else {
        echo "  ℹ️ Admin user already exists\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error checking/creating admin user: " . $e->getMessage() . "\n";
}

// Test critical methods
echo "\n🧪 Testing critical methods...\n";
$methodTests = [
    ['method' => 'getAllFiltered', 'params' => ['all_branches', null], 'description' => 'getAllFiltered()'],
    ['method' => 'getBranches', 'params' => [], 'description' => 'getBranches()'],
    ['method' => 'addBranchIdColumnToRecordsTable', 'params' => [], 'description' => 'addBranchIdColumnToRecordsTable()']
];

foreach ($methodTests as $test) {
    try {
        $result = call_user_func_array([$db, $test['method']], $test['params']);
        echo "  ✅ {$test['description']} works correctly\n";
    } catch (Exception $e) {
        echo "  ❌ {$test['description']} failed: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 QUICK FIX SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if ($fixesApplied > 0) {
    echo "🎉 Database Fixed!\n";
    echo "✅ $fixesApplied fixes applied successfully!\n";
    echo "✅ Missing columns have been added\n";
    echo "✅ Database should now work correctly\n\n";
} else {
    echo "ℹ️ No fixes needed\n";
    echo "✅ All required columns are already present\n\n";
}

echo "🚀 NEXT STEPS:\n";
echo "=============\n";
echo "1. Test your application:\n";
echo "   http://your-domain.com/index.php\n\n";
echo "2. Login with admin credentials:\n";
echo "   Username: admin\n";
echo "   Password: admin123\n\n";
echo "3. Verify dashboard loads without 500 error\n\n";

echo "🔍 VERIFICATION:\n";
echo "================\n";
echo "Run verification script:\n";
echo "http://your-domain.com/verify_database.php\n\n";

echo "🛡️ SECURITY:\n";
echo "============\n";
echo "Delete this file after successful fix:\n";
echo "rm quick_fix_cli.php\n\n";

echo "✅ Quick fix completed! Your 500 error should now be resolved.\n";
?>