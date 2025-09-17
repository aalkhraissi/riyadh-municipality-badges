<?php
/**
 * Database Fix Script
 * Automatically fixes common database issues causing 500 errors
 */

require_once './config/config.php';
require_once 'db.php';

echo "<h1>🔧 Database Auto-Fix Script</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "<h2>✅ Connected to database '$db_name'</h2>";
} catch (Exception $e) {
    die("<h2>❌ Database Connection Failed:</h2><p>" . $e->getMessage() . "</p><p>Check your database credentials in config/config.php</p>");
}

$fixesApplied = 0;

// Fix 1: Ensure all required tables exist
echo "<h3>📋 Creating Missing Tables...</h3>";
$requiredTables = [
    'users' => "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'manager', 'user') DEFAULT 'user',
            branch_access JSON DEFAULT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_role (role),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'branches' => "
        CREATE TABLE IF NOT EXISTS branches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            location VARCHAR(255),
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'records' => "
        CREATE TABLE IF NOT EXISTS records (
            id VARCHAR(32) PRIMARY KEY,
            number INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            department VARCHAR(255),
            general_administration VARCHAR(255),
            administration VARCHAR(255),
            branch_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_number (number),
            INDEX idx_name (name),
            INDEX idx_email (email),
            INDEX idx_department (department),
            INDEX idx_general_administration (general_administration),
            INDEX idx_administration (administration),
            INDEX idx_branch_id (branch_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'roles' => "
        CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            permissions JSON DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'user_sessions' => "
        CREATE TABLE IF NOT EXISTS user_sessions (
            id VARCHAR(128) PRIMARY KEY,
            user_id INT NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'audit_log' => "
        CREATE TABLE IF NOT EXISTS audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            table_name VARCHAR(50),
            record_id VARCHAR(32),
            old_values JSON DEFAULT NULL,
            new_values JSON DEFAULT NULL,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_table_name (table_name),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($requiredTables as $tableName => $createSQL) {
    if (!$db->checkTableExists($tableName)) {
        try {
            $db->createTable($createSQL);
            echo "<p style='color: green;'>✅ Created table '$tableName'</p>";
            $fixesApplied++;
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Failed to create table '$tableName': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Table '$tableName' already exists</p>";
    }
}

// Fix 2: Add missing columns to records table
echo "<h3>🔧 Adding Missing Columns...</h3>";
$missingColumns = [
    'branch_id' => 'INT DEFAULT NULL'
];

foreach ($missingColumns as $columnName => $columnDef) {
    if (!$db->checkColumnExists('records', $columnName)) {
        try {
            $db->executeRawQuery("ALTER TABLE records ADD COLUMN `$columnName` $columnDef");
            echo "<p style='color: green;'>✅ Added column '$columnName' to records table</p>";
            $fixesApplied++;
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Failed to add column '$columnName': " . $e->getMessage() . "</p>";
        }
    }
}

// Fix 3: Add foreign key constraints
echo "<h3>🔗 Adding Foreign Key Constraints...</h3>";
try {
    // Check if foreign key already exists
    $stmt = $db->executeSelectQuery("
        SELECT CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_NAME = 'records'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND CONSTRAINT_NAME = 'fk_records_branch_id'
    ");

    if (!$stmt || !$stmt->fetch()) {
        $db->executeRawQuery("ALTER TABLE records ADD CONSTRAINT fk_records_branch_id FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL");
        echo "<p style='color: green;'>✅ Added foreign key constraint for branch_id</p>";
        $fixesApplied++;
    } else {
        echo "<p style='color: blue;'>ℹ️ Foreign key constraint already exists</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ Foreign key constraint may already exist or table structure issue: " . $e->getMessage() . "</p>";
}

try {
    // Check if foreign key already exists for user_sessions
    $stmt = $db->executeSelectQuery("
        SELECT CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_NAME = 'user_sessions'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND CONSTRAINT_NAME = 'fk_user_sessions_user_id'
    ");

    if (!$stmt || !$stmt->fetch()) {
        $db->executeRawQuery("ALTER TABLE user_sessions ADD CONSTRAINT fk_user_sessions_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Added foreign key constraint for user_sessions.user_id</p>";
        $fixesApplied++;
    } else {
        echo "<p style='color: blue;'>ℹ️ Foreign key constraint for user_sessions already exists</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ Foreign key constraint for user_sessions may already exist: " . $e->getMessage() . "</p>";
}

try {
    // Check if foreign key already exists for audit_log
    $stmt = $db->executeSelectQuery("
        SELECT CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_NAME = 'audit_log'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND CONSTRAINT_NAME = 'fk_audit_log_user_id'
    ");

    if (!$stmt || !$stmt->fetch()) {
        $db->executeRawQuery("ALTER TABLE audit_log ADD CONSTRAINT fk_audit_log_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
        echo "<p style='color: green;'>✅ Added foreign key constraint for audit_log.user_id</p>";
        $fixesApplied++;
    } else {
        echo "<p style='color: blue;'>ℹ️ Foreign key constraint for audit_log already exists</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ Foreign key constraint for audit_log may already exist: " . $e->getMessage() . "</p>";
}

// Fix 4: Insert default data
echo "<h3>📥 Inserting Default Data...</h3>";

// Insert default roles
try {
    $stmt = $db->executeSelectQuery("SELECT COUNT(*) as count FROM roles");
    $roleCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($roleCount == 0) {
        $rolesData = [
            ['name' => 'admin', 'description' => 'Full system access', 'permissions' => json_encode(['*'])],
            ['name' => 'manager', 'description' => 'Branch management access', 'permissions' => json_encode(['read', 'write', 'manage_branch'])],
            ['name' => 'user', 'description' => 'Basic user access', 'permissions' => json_encode(['read'])]
        ];

        foreach ($rolesData as $role) {
            $stmt = $db->executePreparedQuery("INSERT IGNORE INTO roles (name, description, permissions) VALUES (?, ?, ?)", [$role['name'], $role['description'], $role['permissions']]);
        }
        echo "<p style='color: green;'>✅ Default roles inserted</p>";
        $fixesApplied++;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error inserting roles: " . $e->getMessage() . "</p>";
}

// Insert admin user
try {
    $stmt = $db->executeSelectQuery("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($userCount == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->executePreparedQuery("INSERT INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)", ['admin', $adminPassword, 'مدير النظام', 'admin', true]);
        echo "<p style='color: green;'>✅ Admin user created: admin / admin123</p>";
        $fixesApplied++;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating admin user: " . $e->getMessage() . "</p>";
}

// Insert sample branches
try {
    $stmt = $db->executeSelectQuery("SELECT COUNT(*) as count FROM branches");
    $branchCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($branchCount == 0) {
        $branchesData = [
            ['name' => 'المركز الرئيسي', 'location' => 'وسط الرياض', 'description' => 'المقر الرئيسي لبلدية الرياض'],
            ['name' => 'فرع الشمال', 'location' => 'شمال الرياض', 'description' => 'فرع الشمال لبلدية الرياض'],
            ['name' => 'فرع الجنوب', 'location' => 'جنوب الرياض', 'description' => 'فرع الجنوب لبلدية الرياض']
        ];

        foreach ($branchesData as $branch) {
            $stmt = $db->executePreparedQuery("INSERT INTO branches (name, location, description) VALUES (?, ?, ?)", [$branch['name'], $branch['location'], $branch['description']]);
        }
        echo "<p style='color: green;'>✅ Sample branches inserted</p>";
        $fixesApplied++;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error inserting branches: " . $e->getMessage() . "</p>";
}

// Fix 5: Test critical methods
echo "<h3>🧪 Testing Critical Methods...</h3>";
$methodTests = [
    'addBranchIdColumnToRecordsTable' => [],
    'getAllFiltered' => ['all_branches', null],
    'getBranches' => []
];

foreach ($methodTests as $method => $params) {
    try {
        $result = call_user_func_array([$db, $method], $params);
        echo "<p style='color: green;'>✅ Method $method() works correctly</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Method $method() failed: " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

// Summary
echo "<h1>📊 Fix Summary</h1>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

if ($fixesApplied > 0) {
    echo "<h2 style='color: green;'>🎉 Database Fixed!</h2>";
    echo "<p><strong>$fixesApplied fixes applied successfully!</strong></p>";
    echo "<p>The database should now be fully functional. Try accessing your application again.</p>";
} else {
    echo "<h2 style='color: blue;'>ℹ️ No Fixes Needed</h2>";
    echo "<p>Database structure appears to be correct. If you're still getting 500 errors, the issue may be elsewhere.</p>";
}

echo "<h3>🚀 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Test Login:</strong> <a href='index.php'>Go to login page</a></li>";
echo "<li><strong>Check Dashboard:</strong> Try accessing dashboard after login</li>";
echo "<li><strong>Verify Data:</strong> Check if all features work correctly</li>";
echo "</ol>";

echo "<h3>🔧 If Still Having Issues:</h3>";
echo "<ul>";
echo "<li>Run <code>http://your-domain.com/test_database.php</code> to test all methods</li>";
echo "<li>Check <code>http://your-domain.com/check_server.php</code> for server issues</li>";
echo "<li>Enable error logging in PHP files for debugging</li>";
echo "<li>Check web server error logs</li>";
echo "</ul>";

echo "</div>";
?>