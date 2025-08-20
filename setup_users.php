<?php
require_once './config/config.php';
require_once 'db.php';

// Use 127.0.0.1 instead of localhost to avoid connection issues on macOS
$db_host = "127.0.0.1";
$db = new Database($db_host, $db_name, $db_usr, $db_password);

// Create users table
echo "Creating users table...\n";
if ($db->createUsersTable()) {
    echo "Users table created successfully.\n";
} else {
    echo "Failed to create users table.\n";
    exit(1);
}

// Add default admin user
echo "Adding default admin user...\n";
// Using the same password hash as in the current config
$defaultPassword = 'admin123'; // You should change this to a secure password
$adminName = 'Administrator'; // Default name for admin user
if ($db->addUser('admin', $defaultPassword, $adminName)) {
    echo "Default admin user added successfully.\n";
} else {
    echo "Failed to add default admin user.\n";
    exit(1);
}

echo "Setup completed successfully!\n";
?>