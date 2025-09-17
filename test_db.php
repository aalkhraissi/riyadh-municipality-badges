<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Test database connection
require_once './config/config.php';
require_once 'db.php';

try {
    echo "Testing database connection...<br>";
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "✅ Database connection successful!<br>";

    // Test if tables exist
    echo "Checking if tables exist...<br>";
    $tablesExist = $db->checkTableExists('users');
    echo "Users table exists: " . ($tablesExist ? "✅ Yes" : "❌ No") . "<br>";

    $tablesExist = $db->checkTableExists('records');
    echo "Records table exists: " . ($tablesExist ? "✅ Yes" : "❌ No") . "<br>";

    $tablesExist = $db->checkTableExists('branches');
    echo "Branches table exists: " . ($tablesExist ? "✅ Yes" : "❌ No") . "<br>";

    // Test if columns exist
    echo "Checking if columns exist...<br>";
    $columnExists = $db->checkColumnExists('records', 'branch_id');
    echo "branch_id column in records table exists: " . ($columnExists ? "✅ Yes" : "❌ No") . "<br>";

    echo "<br>Database test completed successfully!";

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>