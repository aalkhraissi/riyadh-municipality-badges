#!/usr/bin/env php
<?php
/**
 * Command Line Admin User Update Script
 * Run with: php update_admin_user_cli.php
 */

require_once './config/config.php';
require_once 'db.php';

echo "👨‍💼 Riyadh Municipality - Admin User Update (CLI)\n";
echo "==============================================\n\n";

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "✅ Connected to database '$db_name'\n";
} catch (Exception $e) {
    die("❌ Database Connection Failed: " . $e->getMessage() . "\n");
}

// Check if admin user exists
echo "🔍 Checking admin user...\n";
try {
    $stmt = $db->executeSelectQuery("SELECT * FROM users WHERE username = 'admin'");
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminUser) {
        echo "❌ Admin user not found. Creating new admin user...\n";

        // Create new admin user with full data
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->executePreparedQuery(
            "INSERT INTO users (username, password, name, role, branch_access, is_active) VALUES (?, ?, ?, ?, ?, ?)",
            [
                'admin',
                $adminPassword,
                'مدير النظام',
                'admin',
                json_encode(['all_branches' => true, 'permissions' => ['*']]),
                true
            ]
        );

        if ($stmt) {
            echo "✅ Admin user created successfully\n";
            // Re-fetch the user data
            $stmt = $db->executeSelectQuery("SELECT * FROM users WHERE username = 'admin'");
            $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            die("❌ Failed to create admin user\n");
        }
    } else {
        echo "✅ Admin user found (ID: {$adminUser['id']})\n";
    }
} catch (Exception $e) {
    die("❌ Error checking admin user: " . $e->getMessage() . "\n");
}

// Display current admin user data
echo "\n📊 Current Admin User Data:\n";
echo str_repeat("-", 60) . "\n";
printf("%-20s %-30s %-10s\n", "Field", "Value", "Status");
echo str_repeat("-", 60) . "\n";

$fieldsToCheck = [
    'id' => ['required' => true],
    'username' => ['required' => true, 'expected' => 'admin'],
    'name' => ['required' => true, 'expected' => 'مدير النظام'],
    'role' => ['required' => true, 'expected' => 'admin'],
    'branch_access' => ['required' => true],
    'is_active' => ['required' => true, 'expected' => '1'],
    'created_at' => ['required' => true],
    'updated_at' => ['required' => false]
];

$needsUpdate = false;

foreach ($fieldsToCheck as $field => $config) {
    $currentValue = isset($adminUser[$field]) ? $adminUser[$field] : 'NULL';
    $status = '✅ OK';

    if ($config['required'] && ($currentValue === 'NULL' || $currentValue === null)) {
        $status = '❌ Missing';
        $needsUpdate = true;
    } elseif (isset($config['expected']) && $currentValue !== $config['expected']) {
        $status = '⚠️ Wrong';
        $needsUpdate = true;
    }

    // Format display value
    if ($field === 'branch_access' && $currentValue !== 'NULL') {
        $decoded = json_decode($currentValue, true);
        $displayValue = $decoded ? json_encode($decoded, JSON_UNESCAPED_UNICODE) : $currentValue;
        if (strlen($displayValue) > 25) {
            $displayValue = substr($displayValue, 0, 25) . '...';
        }
    } elseif ($field === 'is_active') {
        $displayValue = $currentValue == 1 ? 'Active' : 'Inactive';
    } elseif (strlen($currentValue) > 25) {
        $displayValue = substr($currentValue, 0, 25) . '...';
    } else {
        $displayValue = $currentValue;
    }

    printf("%-20s %-30s %-10s\n", $field, $displayValue, $status);
}

echo str_repeat("-", 60) . "\n";

// Update admin user with complete data
if ($needsUpdate) {
    echo "\n🔄 Updating admin user...\n";

    // Prepare complete admin data
    $completeAdminData = [
        'username' => 'admin',
        'password' => isset($adminUser['password']) ? $adminUser['password'] : password_hash('admin123', PASSWORD_DEFAULT),
        'name' => 'مدير النظام',
        'role' => 'admin',
        'branch_access' => json_encode(['all_branches' => true, 'permissions' => ['*']]),
        'is_active' => true
    ];

    try {
        $stmt = $db->executePreparedQuery(
            "UPDATE users SET
                username = ?,
                password = ?,
                name = ?,
                role = ?,
                branch_access = ?,
                is_active = ?,
                updated_at = NOW()
            WHERE id = ?",
            [
                $completeAdminData['username'],
                $completeAdminData['password'],
                $completeAdminData['name'],
                $completeAdminData['role'],
                $completeAdminData['branch_access'],
                $completeAdminData['is_active'],
                $adminUser['id']
            ]
        );

        if ($stmt) {
            echo "✅ Admin user updated successfully!\n";

            // Re-fetch updated data
            $stmt = $db->executeSelectQuery("SELECT * FROM users WHERE id = ?", [$adminUser['id']]);
            $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "\n📋 Updated Admin User Data:\n";
            echo "- Username: {$updatedUser['username']}\n";
            echo "- Name: {$updatedUser['name']}\n";
            echo "- Role: {$updatedUser['role']}\n";

            $branchAccess = json_decode($updatedUser['branch_access'], true);
            $branchAccessText = (isset($branchAccess['all_branches']) && $branchAccess['all_branches']) ? 'All Branches' : 'Specific Branches';
            echo "- Branch Access: $branchAccessText\n";

            echo "- Active: " . ($updatedUser['is_active'] ? 'Yes' : 'No') . "\n";
            echo "- Updated: " . ($updatedUser['updated_at'] ?? 'Just now') . "\n";
        } else {
            echo "❌ Failed to update admin user\n";
        }
    } catch (Exception $e) {
        echo "❌ Error updating admin user: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n✅ Admin user already complete\n";
    echo "All required fields are properly populated. No updates needed.\n";
}

// Test login functionality
echo "\n🧪 Testing login functionality...\n";
try {
    $testPassword = 'admin123';
    $hashedPassword = $adminUser['password'];

    if (password_verify($testPassword, $hashedPassword)) {
        echo "✅ Password verification works correctly\n";
    } else {
        echo "❌ Password verification failed\n";
    }

    // Test user authentication method
    if ($db->authenticateUser('admin', 'admin123')) {
        echo "✅ Database authentication method works\n";
    } else {
        echo "❌ Database authentication method failed\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing login: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 ADMIN USER UPDATE SUMMARY\n";
echo str_repeat("=", 60) . "\n";

if ($needsUpdate) {
    echo "✅ Admin user has been updated with complete data\n";
} else {
    echo "✅ Admin user was already complete\n";
}

echo "\n🚀 LOGIN CREDENTIALS:\n";
echo "==================\n";
echo "Username: admin\n";
echo "Password: admin123\n";

echo "\n🔗 TEST LINKS:\n";
echo "=============\n";
echo "Login Page: http://your-domain.com/index.php\n";
echo "Dashboard:  http://your-domain.com/dashboard.php\n";

echo "\n⚠️  SECURITY NOTICE:\n";
echo "==================\n";
echo "• Change the default password after testing\n";
echo "• Delete this file after successful testing\n";
echo "• Command: rm update_admin_user_cli.php\n";

echo "\n✅ Admin user update completed!\n";
?>