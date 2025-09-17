<?php
/**
 * Database Structure Verification Script
 * Checks if all required tables and columns exist
 */

require_once './config/config.php';

echo "<h1>🔍 Database Structure Verification</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_usr, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>✅ Connected to database '$db_name'</h2>";
} catch (PDOException $e) {
    die("<h2>❌ Database Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
}

// Required tables and their columns
$requiredStructure = [
    'users' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'username' => 'VARCHAR(50) UNIQUE NOT NULL',
        'password' => 'VARCHAR(255) NOT NULL',
        'name' => 'VARCHAR(100) NOT NULL',
        'role' => "ENUM('admin', 'manager', 'user') DEFAULT 'user'",
        'branch_access' => 'JSON DEFAULT NULL',
        'is_active' => 'BOOLEAN DEFAULT TRUE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'branches' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(100) NOT NULL',
        'location' => 'VARCHAR(255)',
        'description' => 'TEXT',
        'is_active' => 'BOOLEAN DEFAULT TRUE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'records' => [
        'id' => 'VARCHAR(32) PRIMARY KEY',
        'number' => 'INT NOT NULL',
        'name' => 'VARCHAR(255) NOT NULL',
        'email' => 'VARCHAR(255)',
        'department' => 'VARCHAR(255)',
        'general_administration' => 'VARCHAR(255)',
        'administration' => 'VARCHAR(255)',
        'branch_id' => 'INT DEFAULT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'roles' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(50) UNIQUE NOT NULL',
        'description' => 'TEXT',
        'permissions' => 'JSON DEFAULT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'user_sessions' => [
        'id' => 'VARCHAR(128) PRIMARY KEY',
        'user_id' => 'INT NOT NULL',
        'ip_address' => 'VARCHAR(45)',
        'user_agent' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'expires_at' => 'TIMESTAMP NOT NULL'
    ],
    'audit_log' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT',
        'action' => 'VARCHAR(100) NOT NULL',
        'table_name' => 'VARCHAR(50)',
        'record_id' => 'VARCHAR(32)',
        'old_values' => 'JSON DEFAULT NULL',
        'new_values' => 'JSON DEFAULT NULL',
        'ip_address' => 'VARCHAR(45)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
];

// Check existing tables
echo "<h2>📋 Checking Tables...</h2>";
$existingTables = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $existingTables[] = $row[0];
}

$missingTables = [];
$tablesWithIssues = [];

foreach ($requiredStructure as $tableName => $columns) {
    if (!in_array($tableName, $existingTables)) {
        $missingTables[] = $tableName;
        echo "<p style='color: red;'>❌ Table '$tableName' is missing</p>";
    } else {
        echo "<p style='color: green;'>✅ Table '$tableName' exists</p>";

        // Check columns
        $stmt = $pdo->query("DESCRIBE `$tableName`");
        $existingColumns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[$row['Field']] = $row;
        }

        $missingColumns = [];
        foreach ($columns as $columnName => $columnDef) {
            if (!isset($existingColumns[$columnName])) {
                $missingColumns[$columnName] = $columnDef;
            }
        }

        if (!empty($missingColumns)) {
            $tablesWithIssues[$tableName] = $missingColumns;
            echo "<p style='color: orange;'>⚠️ Table '$tableName' is missing columns: " . implode(', ', array_keys($missingColumns)) . "</p>";
        }
    }
}

// Create missing tables
if (!empty($missingTables)) {
    echo "<h2>🔨 Creating Missing Tables...</h2>";
    foreach ($missingTables as $tableName) {
        $columns = $requiredStructure[$tableName];
        $columnDefs = [];
        foreach ($columns as $colName => $colDef) {
            $columnDefs[] = "`$colName` $colDef";
        }

        $indexes = [];
        if ($tableName === 'users') {
            $indexes[] = "INDEX idx_username (username)";
            $indexes[] = "INDEX idx_role (role)";
            $indexes[] = "INDEX idx_active (is_active)";
        } elseif ($tableName === 'branches') {
            $indexes[] = "INDEX idx_name (name)";
            $indexes[] = "INDEX idx_active (is_active)";
        } elseif ($tableName === 'records') {
            $indexes[] = "INDEX idx_number (number)";
            $indexes[] = "INDEX idx_name (name)";
            $indexes[] = "INDEX idx_email (email)";
            $indexes[] = "INDEX idx_department (department)";
            $indexes[] = "INDEX idx_general_administration (general_administration)";
            $indexes[] = "INDEX idx_administration (administration)";
            $indexes[] = "INDEX idx_branch_id (branch_id)";
        } elseif ($tableName === 'roles') {
            $indexes[] = "INDEX idx_name (name)";
        } elseif ($tableName === 'user_sessions') {
            $indexes[] = "INDEX idx_user_id (user_id)";
            $indexes[] = "INDEX idx_expires_at (expires_at)";
        } elseif ($tableName === 'audit_log') {
            $indexes[] = "INDEX idx_user_id (user_id)";
            $indexes[] = "INDEX idx_action (action)";
            $indexes[] = "INDEX idx_table_name (table_name)";
            $indexes[] = "INDEX idx_created_at (created_at)";
        }

        $createSQL = "CREATE TABLE `$tableName` (" . implode(', ', $columnDefs);
        if (!empty($indexes)) {
            $createSQL .= ', ' . implode(', ', $indexes);
        }

        // Add foreign keys
        $foreignKeys = [];
        if ($tableName === 'records') {
            $foreignKeys[] = "FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL";
        } elseif ($tableName === 'user_sessions') {
            $foreignKeys[] = "FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE";
        } elseif ($tableName === 'audit_log') {
            $foreignKeys[] = "FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
        }

        if (!empty($foreignKeys)) {
            $createSQL .= ', ' . implode(', ', $foreignKeys);
        }

        $createSQL .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $pdo->exec($createSQL);
            echo "<p style='color: green;'>✅ Created table '$tableName'</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Failed to create table '$tableName': " . $e->getMessage() . "</p>";
        }
    }
}

// Add missing columns
if (!empty($tablesWithIssues)) {
    echo "<h2>🔧 Adding Missing Columns...</h2>";
    foreach ($tablesWithIssues as $tableName => $missingColumns) {
        foreach ($missingColumns as $columnName => $columnDef) {
            try {
                $alterSQL = "ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnDef";
                $pdo->exec($alterSQL);
                echo "<p style='color: green;'>✅ Added column '$columnName' to table '$tableName'</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Failed to add column '$columnName' to '$tableName': " . $e->getMessage() . "</p>";
            }
        }
    }
}

// Insert default data if tables are empty
echo "<h2>📥 Checking Default Data...</h2>";

// Check and insert roles
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $roleCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($roleCount == 0) {
        echo "<p>📝 Inserting default roles...</p>";
        $rolesData = [
            ['name' => 'admin', 'description' => 'Full system access', 'permissions' => json_encode(['*'])],
            ['name' => 'manager', 'description' => 'Branch management access', 'permissions' => json_encode(['read', 'write', 'manage_branch'])],
            ['name' => 'user', 'description' => 'Basic user access', 'permissions' => json_encode(['read'])]
        ];

        foreach ($rolesData as $role) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO roles (name, description, permissions) VALUES (?, ?, ?)");
            $stmt->execute([$role['name'], $role['description'], $role['permissions']]);
        }
        echo "<p style='color: green;'>✅ Default roles inserted</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Roles table already has data ($roleCount roles)</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking roles: " . $e->getMessage() . "</p>";
}

// Check and insert admin user
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($userCount == 0) {
        echo "<p>👤 Creating default admin user...</p>";
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $adminPassword, 'مدير النظام', 'admin', true]);
        echo "<p style='color: green;'>✅ Admin user created: admin / admin123</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Users table already has data ($userCount users)</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking users: " . $e->getMessage() . "</p>";
}

// Check and insert sample branches
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM branches");
    $branchCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($branchCount == 0) {
        echo "<p>🏢 Inserting sample branches...</p>";
        $branchesData = [
            ['name' => 'المركز الرئيسي', 'location' => 'وسط الرياض', 'description' => 'المقر الرئيسي لبلدية الرياض'],
            ['name' => 'فرع الشمال', 'location' => 'شمال الرياض', 'description' => 'فرع الشمال لبلدية الرياض'],
            ['name' => 'فرع الجنوب', 'location' => 'جنوب الرياض', 'description' => 'فرع الجنوب لبلدية الرياض']
        ];

        foreach ($branchesData as $branch) {
            $stmt = $pdo->prepare("INSERT INTO branches (name, location, description) VALUES (?, ?, ?)");
            $stmt->execute([$branch['name'], $branch['location'], $branch['description']]);
        }
        echo "<p style='color: green;'>✅ Sample branches inserted</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Branches table already has data ($branchCount branches)</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking branches: " . $e->getMessage() . "</p>";
}

// Final verification
echo "<h2>🎯 Final Verification</h2>";
$allGood = true;

foreach ($requiredStructure as $tableName => $columns) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p style='color: green;'>✅ Table '$tableName': $count records</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Table '$tableName': Error - " . $e->getMessage() . "</p>";
        $allGood = false;
    }
}

echo "</div>";

if ($allGood) {
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50; margin-top: 20px;'>";
    echo "<h2 style='color: green;'>🎉 Database Structure is Complete!</h2>";
    echo "<p>All required tables and columns are present. The application should now work correctly.</p>";
    echo "<p><a href='index.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 5px; border: 1px solid #f44336; margin-top: 20px;'>";
    echo "<h2 style='color: red;'>❌ Database Issues Found</h2>";
    echo "<p>There are still issues with the database structure. Please check the errors above and try again.</p>";
    echo "</div>";
}

echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>🔧 Troubleshooting Tips:</h3>";
echo "<ul>";
echo "<li>Check MySQL user permissions: <code>GRANT ALL ON records.* TO 'your_user'@'localhost';</code></li>";
echo "<li>Verify database connection in <code>config/config.php</code></li>";
echo "<li>Ensure MySQL server is running: <code>sudo systemctl status mysql</code></li>";
echo "<li>Check available disk space</li>";
echo "</ul>";
echo "</div>";
?>