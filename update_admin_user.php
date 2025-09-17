<?php
/**
 * Update Admin User Script
 * Ensures the admin user has all required fields with complete data
 */

require_once './config/config.php';
require_once 'db.php';

echo "<h1>👨‍💼 Update Admin User</h1>";
echo "<div style='font-family: monospace; background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "<h2>✅ Connected to database '$db_name'</h2>";
} catch (Exception $e) {
    die("<h2>❌ Database Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
}

// Check if admin user exists
echo "<h3>🔍 Checking Admin User...</h3>";
try {
    $stmt = $db->executeSelectQuery("SELECT * FROM users WHERE username = 'admin'");
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminUser) {
        echo "<p style='color: red;'>❌ Admin user not found. Creating new admin user...</p>";

        // Create new admin user with full data
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->executePreparedQuery(
            "INSERT INTO users (username, password, name, role, branch_access, is_active) VALUES (?, ?, ?, ?, ?, ?)",
            [
                'admin',
                $adminPassword,
                'مدير النظام',
                'admin',
                json_encode(['all_branches' => true]),
                true
            ]
        );

        if ($stmt) {
            echo "<p style='color: green;'>✅ Admin user created successfully</p>";
            // Re-fetch the user data
            $stmt = $db->executeSelectQuery("SELECT * FROM users WHERE username = 'admin'");
            $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            die("<p style='color: red;'>❌ Failed to create admin user</p>");
        }
    } else {
        echo "<p style='color: green;'>✅ Admin user found (ID: {$adminUser['id']})</p>";
    }
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error checking admin user: " . $e->getMessage() . "</p>");
}

// Display current admin user data
echo "<h3>📊 Current Admin User Data:</h3>";
echo "<table style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f5f5f5;'><th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Field</th><th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Current Value</th><th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Status</th></tr>";

$fieldsToCheck = [
    'id' => ['required' => true, 'expected' => 'Any'],
    'username' => ['required' => true, 'expected' => 'admin'],
    'name' => ['required' => true, 'expected' => 'مدير النظام'],
    'role' => ['required' => true, 'expected' => 'admin'],
    'branch_access' => ['required' => true, 'expected' => 'JSON with branch permissions'],
    'is_active' => ['required' => true, 'expected' => '1 (true)'],
    'created_at' => ['required' => true, 'expected' => 'Any timestamp'],
    'updated_at' => ['required' => false, 'expected' => 'Any timestamp']
];

$needsUpdate = false;

foreach ($fieldsToCheck as $field => $config) {
    $currentValue = isset($adminUser[$field]) ? $adminUser[$field] : 'NULL';
    $status = '✅ OK';

    if ($config['required'] && ($currentValue === 'NULL' || $currentValue === null)) {
        $status = '❌ Missing';
        $needsUpdate = true;
    } elseif ($field === 'username' && $currentValue !== 'admin') {
        $status = '⚠️ Wrong value';
        $needsUpdate = true;
    } elseif ($field === 'name' && $currentValue !== 'مدير النظام') {
        $status = '⚠️ Wrong value';
        $needsUpdate = true;
    } elseif ($field === 'role' && $currentValue !== 'admin') {
        $status = '⚠️ Wrong value';
        $needsUpdate = true;
    } elseif ($field === 'is_active' && $currentValue != 1) {
        $status = '⚠️ Wrong value';
        $needsUpdate = true;
    }

    // Format display value
    if ($field === 'branch_access' && $currentValue !== 'NULL') {
        $decoded = json_decode($currentValue, true);
        $displayValue = $decoded ? json_encode($decoded, JSON_UNESCAPED_UNICODE) : $currentValue;
    } elseif ($field === 'is_active') {
        $displayValue = $currentValue == 1 ? 'Active (1)' : 'Inactive (0)';
    } elseif (strlen($currentValue) > 50) {
        $displayValue = substr($currentValue, 0, 50) . '...';
    } else {
        $displayValue = $currentValue;
    }

    echo "<tr><td style='border: 1px solid #ddd; padding: 8px;'>$field</td><td style='border: 1px solid #ddd; padding: 8px;'>$displayValue</td><td style='border: 1px solid #ddd; padding: 8px;'>$status</td></tr>";
}

echo "</table>";

// Update admin user with complete data
if ($needsUpdate) {
    echo "<h3>🔄 Updating Admin User...</h3>";

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
            echo "<p style='color: green;'>✅ Admin user updated successfully!</p>";

            // Re-fetch updated data
            $stmt = $db->executeSelectQuery("SELECT * FROM users WHERE id = ?", [$adminUser['id']]);
            $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "<h4>📋 Updated Admin User Data:</h4>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> {$updatedUser['username']}</li>";
            echo "<li><strong>Name:</strong> {$updatedUser['name']}</li>";
            echo "<li><strong>Role:</strong> {$updatedUser['role']}</li>";
            $branchAccess = json_decode($updatedUser['branch_access'], true);
            $branchAccessText = (isset($branchAccess['all_branches']) && $branchAccess['all_branches']) ? 'All Branches' : 'Specific Branches';
            echo "<li><strong>Branch Access:</strong> $branchAccessText</li>";
            echo "<li><strong>Active:</strong> " . ($updatedUser['is_active'] ? 'Yes' : 'No') . "</li>";
            echo "<li><strong>Updated:</strong> " . ($updatedUser['updated_at'] ?? 'Just now') . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update admin user</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error updating admin user: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h3>✅ Admin User Already Complete</h3>";
    echo "<p>All required fields are properly populated. No updates needed.</p>";
}

// Test login functionality
echo "<h3>🧪 Testing Login Functionality...</h3>";
try {
    $testPassword = 'admin123';
    $hashedPassword = $adminUser['password'];

    if (password_verify($testPassword, $hashedPassword)) {
        echo "<p style='color: green;'>✅ Password verification works correctly</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed</p>";
    }

    // Test user authentication method
    if ($db->authenticateUser('admin', 'admin123')) {
        echo "<p style='color: green;'>✅ Database authentication method works</p>";
    } else {
        echo "<p style='color: red;'>❌ Database authentication method failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing login: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Login test section
echo "<h2>🚀 Test Login</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
echo "<h3>Login Credentials:</h3>";
echo "<ul>";
echo "<li><strong>Username:</strong> <code>admin</code></li>";
echo "<li><strong>Password:</strong> <code>admin123</code></li>";
echo "</ul>";

echo "<h3>Test Links:</h3>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>Login Page</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Dashboard (requires login)</a></li>";
echo "</ul>";

echo "<p><strong>Note:</strong> After successful login, you should be redirected to the dashboard without any 500 errors.</p>";
echo "</div>";

// Security notice
echo "<div style='margin-top: 20px; padding: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 5px;'>";
echo "<h3>⚠️ Security Notice</h3>";
echo "<p><strong>Important:</strong> Change the default password after testing!</p>";
echo "<p><strong>Delete this file</strong> after successful testing to prevent unauthorized access.</p>";
echo "<p>Command: <code>rm update_admin_user.php</code></p>";
echo "</div>";
?>