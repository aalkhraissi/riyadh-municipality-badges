#!/usr/bin/env php
<?php
/**
 * Command Line Database Setup Script
 * Run with: php setup_database_cli.php
 */

// Include configuration
require_once './config/config.php';

echo "🚀 Riyadh Municipality Database Setup (CLI Version)\n";
echo "================================================\n\n";

// Create database connection
try {
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_usr, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL server successfully!\n";
} catch (PDOException $e) {
    die("❌ MySQL Connection Failed: " . $e->getMessage() . "\n");
}

// Create database if it doesn't exist
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$db_name' created or already exists!\n";
} catch (PDOException $e) {
    die("❌ Database Creation Failed: " . $e->getMessage() . "\n");
}

// Connect to the specific database
try {
    $pdo->exec("USE `$db_name`");
    echo "✅ Connected to database '$db_name'!\n\n";
} catch (PDOException $e) {
    die("❌ Database Selection Failed: " . $e->getMessage() . "\n");
}

echo "📋 Creating database tables...\n";

// Create users table
echo "  👤 Creating users table... ";
$usersTableSQL = "
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($usersTableSQL);
    echo "✅ Done\n";
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}

// Create branches table
echo "  🏢 Creating branches table... ";
$branchesTableSQL = "
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($branchesTableSQL);
    echo "✅ Done\n";
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}

// Create records table
echo "  👥 Creating records table... ";
$recordsTableSQL = "
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
    INDEX idx_branch_id (branch_id),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($recordsTableSQL);
    echo "✅ Done\n";
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}

// Create roles table
echo "  🔐 Creating roles table... ";
$rolesTableSQL = "
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    permissions JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($rolesTableSQL);
    echo "✅ Done\n";
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}

// Create user_sessions table
echo "  📊 Creating user_sessions table... ";
$sessionsTableSQL = "
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sessionsTableSQL);
    echo "✅ Done\n";
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}

// Create audit_log table
echo "  📝 Creating audit_log table... ";
$auditLogTableSQL = "
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
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($auditLogTableSQL);
    echo "✅ Done\n\n";
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n\n";
}

echo "📥 Inserting sample data...\n";

// Insert default roles
echo "  👤 Inserting roles... ";
$rolesData = [
    ['name' => 'admin', 'description' => 'Full system access', 'permissions' => json_encode(['*'])],
    ['name' => 'manager', 'description' => 'Branch management access', 'permissions' => json_encode(['read', 'write', 'manage_branch'])],
    ['name' => 'user', 'description' => 'Basic user access', 'permissions' => json_encode(['read'])]
];

foreach ($rolesData as $role) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO roles (name, description, permissions) VALUES (?, ?, ?)");
        $stmt->execute([$role['name'], $role['description'], $role['permissions']]);
    } catch (PDOException $e) {
        echo "❌ Failed to insert role '{$role['name']}': " . $e->getMessage() . "\n";
    }
}
echo "✅ Done\n";

// Insert sample branches
echo "  🏢 Inserting branches... ";
$branchesData = [
    ['name' => 'المركز الرئيسي', 'location' => 'وسط الرياض', 'description' => 'المقر الرئيسي لبلدية الرياض'],
    ['name' => 'فرع الشمال', 'location' => 'شمال الرياض', 'description' => 'فرع الشمال لبلدية الرياض'],
    ['name' => 'فرع الجنوب', 'location' => 'جنوب الرياض', 'description' => 'فرع الجنوب لبلدية الرياض'],
    ['name' => 'فرع الشرق', 'location' => 'شرق الرياض', 'description' => 'فرع الشرق لبلدية الرياض'],
    ['name' => 'فرع الغرب', 'location' => 'غرب الرياض', 'description' => 'فرع الغرب لبلدية الرياض']
];

foreach ($branchesData as $branch) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO branches (name, location, description) VALUES (?, ?, ?)");
        $stmt->execute([$branch['name'], $branch['location'], $branch['description']]);
    } catch (PDOException $e) {
        echo "❌ Failed to insert branch '{$branch['name']}': " . $e->getMessage() . "\n";
    }
}
echo "✅ Done\n";

// Insert default admin user
echo "  👨‍💼 Creating admin user... ";
try {
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $adminPassword, 'مدير النظام', 'admin', true]);
    echo "✅ Done\n";
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}

// Insert sample users
echo "  👥 Creating sample users... ";
$usersData = [
    ['username' => 'manager1', 'password' => 'manager123', 'name' => 'مدير الفرع الأول', 'role' => 'manager'],
    ['username' => 'user1', 'password' => 'user123', 'name' => 'مستخدم تجريبي', 'role' => 'user']
];

foreach ($usersData as $user) {
    try {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user['username'], $hashedPassword, $user['name'], $user['role'], true]);
    } catch (PDOException $e) {
        echo "❌ Failed to create user '{$user['username']}': " . $e->getMessage() . "\n";
    }
}
echo "✅ Done\n";

// Insert sample records
echo "  📄 Inserting sample records... ";
$sampleRecords = [
    ['id' => 'sample001', 'number' => 1, 'name' => 'أحمد محمد علي', 'email' => 'ahmed@example.com', 'department' => 'تقنية المعلومات', 'general_administration' => 'الإدارة العامة لتقنية المعلومات', 'administration' => 'إدارة الأنظمة', 'branch_id' => 1],
    ['id' => 'sample002', 'number' => 2, 'name' => 'فاطمة أحمد حسن', 'email' => 'fatima@example.com', 'department' => 'الموارد البشرية', 'general_administration' => 'الإدارة العامة للموارد البشرية', 'administration' => 'إدارة التطوير الوظيفي', 'branch_id' => 1],
    ['id' => 'sample003', 'number' => 3, 'name' => 'محمد عبدالله سالم', 'email' => 'mohammed@example.com', 'department' => 'المالية', 'general_administration' => 'الإدارة العامة للمالية', 'administration' => 'إدارة الميزانية', 'branch_id' => 2],
    ['id' => 'sample004', 'number' => 4, 'name' => 'سارة خالد العتيبي', 'email' => 'sara@example.com', 'department' => 'التخطيط', 'general_administration' => 'الإدارة العامة للتخطيط', 'administration' => 'إدارة التطوير الحضري', 'branch_id' => 3],
    ['id' => 'sample005', 'number' => 5, 'name' => 'عبدالرحمن أحمد الزهراني', 'email' => 'abdulrahman@example.com', 'department' => 'الصيانة', 'general_administration' => 'الإدارة العامة للصيانة', 'administration' => 'إدارة صيانة المرافق', 'branch_id' => 4]
];

foreach ($sampleRecords as $record) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO records (id, number, name, email, department, general_administration, administration, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $record['id'], $record['number'], $record['name'], $record['email'],
            $record['department'], $record['general_administration'], $record['administration'], $record['branch_id']
        ]);
    } catch (PDOException $e) {
        echo "❌ Failed to insert record '{$record['name']}': " . $e->getMessage() . "\n";
    }
}
echo "✅ Done\n\n";

echo "🎉 Database setup completed successfully!\n\n";

echo "📊 SUMMARY:\n";
echo "==========\n";
echo "Database: $db_name\n";
echo "Tables: users, branches, records, roles, user_sessions, audit_log\n";
echo "Sample Data: ✅ Admin user, sample branches, sample records\n\n";

echo "🔑 LOGIN CREDENTIALS:\n";
echo "====================\n";
echo "Admin:    admin / admin123\n";
echo "Manager:  manager1 / manager123\n";
echo "User:     user1 / user123\n\n";

echo "🚀 NEXT STEPS:\n";
echo "=============\n";
echo "1. Delete setup files for security\n";
echo "   rm setup_database.php setup_database_cli.php\n\n";
echo "2. Access the application\n";
echo "   http://localhost/your-project/index.php\n\n";
echo "3. Login with admin credentials\n\n";

echo "⚠️  SECURITY NOTICE:\n";
echo "==================\n";
echo "Remember to delete the setup files after successful setup!\n\n";

echo "✅ Setup complete! Riyadh Municipality Control System is ready to use.\n";
?>