<?php
/**
 * Database Setup Script for Riyadh Municipality Control System
 *
 * This script creates all necessary database tables and inserts sample data
 * Run this script once to initialize the database
 */

// Include configuration
require_once './config/config.php';

// Create database connection
try {
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_usr, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>✅ Connected to MySQL server successfully!</h2>";
} catch (PDOException $e) {
    die("<h2>❌ MySQL Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
}

// Create database if it doesn't exist
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<h3>✅ Database '$db_name' created or already exists!</h3>";
} catch (PDOException $e) {
    die("<h2>❌ Database Creation Failed:</h2><p>" . $e->getMessage() . "</p>");
}

// Connect to the specific database
try {
    $pdo->exec("USE `$db_name`");
    echo "<h3>✅ Connected to database '$db_name'!</h3>";
} catch (PDOException $e) {
    die("<h2>❌ Database Selection Failed:</h2><p>" . $e->getMessage() . "</p>");
}

echo "<h1>🚀 Setting up Riyadh Municipality Database Tables...</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

// Create users table
echo "<h3>📋 Creating users table...</h3>";
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
    echo "✅ Users table created successfully!<br>";
} catch (PDOException $e) {
    echo "❌ Failed to create users table: " . $e->getMessage() . "<br>";
}

// Create branches table
echo "<h3>🏢 Creating branches table...</h3>";
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
    echo "✅ Branches table created successfully!<br>";
} catch (PDOException $e) {
    echo "❌ Failed to create branches table: " . $e->getMessage() . "<br>";
}

// Create records table (main employee records)
echo "<h3>👥 Creating records table...</h3>";
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
    echo "✅ Records table created successfully!<br>";
} catch (PDOException $e) {
    echo "❌ Failed to create records table: " . $e->getMessage() . "<br>";
}

// Create roles table for role-based access control
echo "<h3>🔐 Creating roles table...</h3>";
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
    echo "✅ Roles table created successfully!<br>";
} catch (PDOException $e) {
    echo "❌ Failed to create roles table: " . $e->getMessage() . "<br>";
}

// Create user_sessions table for session management
echo "<h3>📊 Creating user_sessions table...</h3>";
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
    echo "✅ User sessions table created successfully!<br>";
} catch (PDOException $e) {
    echo "❌ Failed to create user_sessions table: " . $e->getMessage() . "<br>";
}

// Create audit_log table for tracking changes
echo "<h3>📝 Creating audit_log table...</h3>";
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
    echo "✅ Audit log table created successfully!<br>";
} catch (PDOException $e) {
    echo "❌ Failed to create audit_log table: " . $e->getMessage() . "<br>";
}

echo "</div>";

// Insert sample data
echo "<h1>📥 Inserting Sample Data...</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

// Insert default roles
echo "<h3>👤 Inserting default roles...</h3>";
$rolesData = [
    ['name' => 'admin', 'description' => 'Full system access', 'permissions' => json_encode(['*'])],
    ['name' => 'manager', 'description' => 'Branch management access', 'permissions' => json_encode(['read', 'write', 'manage_branch'])],
    ['name' => 'user', 'description' => 'Basic user access', 'permissions' => json_encode(['read'])]
];

foreach ($rolesData as $role) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO roles (name, description, permissions) VALUES (?, ?, ?)");
        $stmt->execute([$role['name'], $role['description'], $role['permissions']]);
        echo "✅ Role '{$role['name']}' inserted!<br>";
    } catch (PDOException $e) {
        echo "❌ Failed to insert role '{$role['name']}': " . $e->getMessage() . "<br>";
    }
}

// Insert sample branches
echo "<h3>🏢 Inserting sample branches...</h3>";
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
        echo "✅ Branch '{$branch['name']}' inserted!<br>";
    } catch (PDOException $e) {
        echo "❌ Failed to insert branch '{$branch['name']}': " . $e->getMessage() . "<br>";
    }
}

// Insert default admin user
echo "<h3>👨‍💼 Inserting default admin user...</h3>";
try {
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $adminPassword, 'مدير النظام', 'admin', true]);
    echo "✅ Admin user 'admin' created with password 'admin123'!<br>";
} catch (PDOException $e) {
    echo "❌ Failed to create admin user: " . $e->getMessage() . "<br>";
}

// Insert sample users
echo "<h3>👥 Inserting sample users...</h3>";
$usersData = [
    ['username' => 'manager1', 'password' => 'manager123', 'name' => 'مدير الفرع الأول', 'role' => 'manager'],
    ['username' => 'user1', 'password' => 'user123', 'name' => 'مستخدم تجريبي', 'role' => 'user']
];

foreach ($usersData as $user) {
    try {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user['username'], $hashedPassword, $user['name'], $user['role'], true]);
        echo "✅ User '{$user['username']}' created with password '{$user['password']}'!<br>";
    } catch (PDOException $e) {
        echo "❌ Failed to create user '{$user['username']}': " . $e->getMessage() . "<br>";
    }
}

// Insert sample records
echo "<h3>📄 Inserting sample records...</h3>";
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
        echo "✅ Record '{$record['name']}' inserted!<br>";
    } catch (PDOException $e) {
        echo "❌ Failed to insert record '{$record['name']}': " . $e->getMessage() . "<br>";
    }
}

echo "</div>";

// Display setup summary
echo "<h1>🎉 Database Setup Complete!</h1>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";
echo "<h2>📊 Setup Summary:</h2>";
echo "<ul>";
echo "<li>✅ <strong>Database:</strong> $db_name</li>";
echo "<li>✅ <strong>Tables Created:</strong> users, branches, records, roles, user_sessions, audit_log</li>";
echo "<li>✅ <strong>Sample Data:</strong> Admin user, sample branches, sample records</li>";
echo "</ul>";

echo "<h2>🔑 Default Login Credentials:</h2>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>";
echo "<li><strong>Manager:</strong> username: <code>manager1</code>, password: <code>manager123</code></li>";
echo "<li><strong>User:</strong> username: <code>user1</code>, password: <code>user123</code></li>";
echo "</ul>";

echo "<h2>🚀 Next Steps:</h2>";
echo "<ol>";
echo "<li>Delete this setup file for security: <code>setup_database.php</code></li>";
echo "<li>Access the application at: <code>index.php</code></li>";
echo "<li>Login with admin credentials to manage the system</li>";
echo "<li>Customize branches and users as needed</li>";
echo "</ol>";
echo "</div>";

// Display database info
echo "<h2>ℹ️ Database Information:</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Host:</strong> $db_host</p>";
echo "<p><strong>Database:</strong> $db_name</p>";
echo "<p><strong>Tables Created:</strong></p>";
echo "<ul>";
echo "<li><code>users</code> - User authentication and management</li>";
echo "<li><code>branches</code> - Branch/division management</li>";
echo "<li><code>records</code> - Main employee/staff records</li>";
echo "<li><code>roles</code> - Role-based access control</li>";
echo "<li><code>user_sessions</code> - Session management</li>";
echo "<li><code>audit_log</code> - Change tracking and audit trail</li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 30px; padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>⚠️ Security Notice:</h3>";
echo "<p><strong>Important:</strong> Remember to delete this file (<code>setup_database.php</code>) after setup is complete to prevent unauthorized access to your database setup.</p>";
echo "</div>";

?>