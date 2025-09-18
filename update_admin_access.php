<?php
/**
 * Admin User Access Update Script
 *
 * This script ensures the admin user has full access to all branches
 * and proper permissions across the system.
 */

require_once './config/config.php';
require_once 'db.php';

echo "<h1>🔐 Updating Admin User Access...</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

try {
    // Create database connection
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "✅ Connected to database successfully!<br><br>";

    // First, ensure the admin role exists with full permissions
    echo "<h3>👤 Ensuring admin role exists...</h3>";

    $adminPermissions = json_encode(['*']); // Full access permissions
    $stmt = $db->executePreparedQuery("
        INSERT INTO roles (name, description, permissions)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        description = VALUES(description),
        permissions = VALUES(permissions)
    ", ['admin', 'Full system access', $adminPermissions]);

    if ($stmt) {
        echo "✅ Admin role updated with full permissions!<br>";
    } else {
        echo "❌ Failed to update admin role!<br>";
    }

    // Now update the admin user
    echo "<h3>👨‍💼 Updating admin user access...</h3>";

    // Check if admin user exists
    $adminUser = $db->getUserByName('admin');
    if (!$adminUser) {
        echo "❌ Admin user 'admin' does not exist. Creating...<br>";

        // Create admin user with full access
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $fullAccess = json_encode([
            'type' => 'all_branches',
            'assigned_branches' => []
        ]);

        $stmt = $db->executePreparedQuery("
            INSERT INTO users (username, password, name, role, branch_access, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ", ['admin', $adminPassword, 'مدير النظام', 'admin', $fullAccess, true]);

        if ($stmt) {
            echo "✅ Admin user created with full access!<br>";
            echo "   Username: admin<br>";
            echo "   Password: admin123<br>";
        } else {
            echo "❌ Failed to create admin user!<br>";
        }
    } else {
        echo "✅ Admin user exists. Updating access...<br>";

        // Update admin user with full access
        $fullAccess = json_encode([
            'type' => 'all_branches',
            'assigned_branches' => []
        ]);

        $stmt = $db->executePreparedQuery("
            UPDATE users
            SET role = ?, branch_access = ?, is_active = ?
            WHERE username = ?
        ", ['admin', $fullAccess, true, 'admin']);

        if ($stmt && $stmt->rowCount() > 0) {
            echo "✅ Admin user updated with full access!<br>";
        } else {
            echo "ℹ️ Admin user already has full access or update failed.<br>";
        }
    }

    // Verify the admin user's current access
    echo "<h3>🔍 Verifying admin access...</h3>";
    $updatedAdmin = $db->getUserByName('admin');
    if ($updatedAdmin) {
        echo "✅ Admin user details:<br>";
        echo "   - Username: " . $updatedAdmin['username'] . "<br>";
        echo "   - Name: " . $updatedAdmin['name'] . "<br>";
        echo "   - Role: " . $updatedAdmin['role'] . "<br>";
        echo "   - Active: " . ($updatedAdmin['is_active'] ? 'Yes' : 'No') . "<br>";

        if ($updatedAdmin['branch_access']) {
            $access = json_decode($updatedAdmin['branch_access'], true);
            echo "   - Branch Access Type: " . ($access['type'] ?? 'Not set') . "<br>";
        } else {
            echo "   - Branch Access: Not set<br>";
        }
    } else {
        echo "❌ Could not verify admin user details.<br>";
    }

    echo "</div>";
    echo "<h1>🎉 Admin Access Update Complete!</h1>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";
    echo "<p>The admin user now has full access to all branches and system features.</p>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "</div>";
    echo "<h1>❌ Error</h1>";
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 5px; border: 1px solid #f44336;'>";
    echo "<p><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>