<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Setup database and create test user
require_once './config/config.php';
require_once 'db.php';

try {
    echo "Setting up database...<br>";
    $db = new Database($db_host, $db_name, $db_usr, $db_password);

    // Create users table if it doesn't exist
    echo "Creating users table...<br>";
    $db->createUsersTable();

    // Add missing columns to existing users table
    echo "Adding missing columns to users table...<br>";
    $columnsAdded = $db->addRoleColumnsToUsersTable();
    if ($columnsAdded) {
        echo "✅ Missing columns added successfully!<br>";
    } else {
        echo "⚠️ Some columns might already exist or failed to add.<br>";
    }

    // Create branches table if it doesn't exist
    echo "Creating branches table...<br>";
    $db->createBranchesTable();

    // Add branch_id column to records table if it doesn't exist
    echo "Ensuring branch_id column exists in records table...<br>";
    $db->addBranchIdColumnToRecordsTable();

    // Create a test admin user
    echo "Creating/updating test admin user...<br>";
    try {
        $adminCreated = $db->addUser('admin', 'admin123', 'Administrator', 'all_branches', null, 1);
        if ($adminCreated) {
            echo "✅ Admin user created successfully!<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        } else {
            echo "⚠️ Admin user might already exist.<br>";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "⚠️ Admin user already exists, updating password...<br>";
            // Try to update the existing admin user
            try {
                $db->pdo->prepare("UPDATE users SET password = ?, name = ?, branch_access_type = ?, is_admin = ? WHERE username = ?")
                      ->execute([password_hash('admin123', PASSWORD_DEFAULT), 'Administrator', 'all_branches', 1, 'admin']);
                echo "✅ Admin user updated successfully!<br>";
                echo "Username: admin<br>";
                echo "Password: admin123<br>";
            } catch (Exception $updateError) {
                echo "❌ Failed to update admin user: " . $updateError->getMessage() . "<br>";
            }
        } else {
            echo "❌ Error creating admin user: " . $e->getMessage() . "<br>";
        }
    }

    // Create a test normal user
    echo "Creating/updating test normal user...<br>";
    try {
        $normalUserCreated = $db->addUser('user', 'user123', 'Normal User', 'specific_branches', [1], 0);
        if ($normalUserCreated) {
            echo "✅ Normal user created successfully!<br>";
            echo "Username: user<br>";
            echo "Password: user123<br>";
        } else {
            echo "⚠️ Normal user might already exist.<br>";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "⚠️ Normal user already exists, updating password...<br>";
            // Try to update the existing normal user
            try {
                $db->pdo->prepare("UPDATE users SET password = ?, name = ?, branch_access_type = ?, assigned_branches = ?, is_admin = ? WHERE username = ?")
                      ->execute([password_hash('user123', PASSWORD_DEFAULT), 'Normal User', 'specific_branches', json_encode([1]), 0, 'user']);
                echo "✅ Normal user updated successfully!<br>";
                echo "Username: user<br>";
                echo "Password: user123<br>";
            } catch (Exception $updateError) {
                echo "❌ Failed to update normal user: " . $updateError->getMessage() . "<br>";
            }
        } else {
            echo "❌ Error creating normal user: " . $e->getMessage() . "<br>";
        }
    }

    // Create some test branches
    echo "Creating test branches...<br>";
    $branch1 = $db->insertBranch(['name' => 'الفرع الرئيسي']);
    $branch2 = $db->insertBranch(['name' => 'فرع الشمال']);
    $branch3 = $db->insertBranch(['name' => 'فرع الجنوب']);

    if ($branch1 && $branch2 && $branch3) {
        echo "✅ Test branches created successfully!<br>";
    } else {
        echo "⚠️ Some branches might already exist.<br>";
    }

    echo "<br>🎉 Database setup completed successfully!<br>";
    echo "<br>You can now login with:<br>";
    echo "Admin: admin / admin123<br>";
    echo "User: user / user123<br>";

} catch (Exception $e) {
    echo "❌ Database setup error: " . $e->getMessage();
}
?>