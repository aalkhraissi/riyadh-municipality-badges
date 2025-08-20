<?php
require_once './config/config.php';
require_once 'db.php';

// Use 127.0.0.1 instead of localhost to avoid connection issues on macOS
$db_host = "127.0.0.1";
$db = new Database($db_host, $db_name, $db_usr, $db_password);

// Add name column to users table
echo "Adding name column to users table...\n";
if ($db->addNameColumnToUsersTable()) {
    echo "Name column added successfully.\n";
} else {
    echo "Failed to add name column.\n";
    exit(1);
}

// Update existing users with a default name
echo "Updating existing users with default name...\n";
if ($db->updateUsersWithDefaultName('Administrator')) {
    echo "Existing users updated with default name.\n";
} else {
    echo "Failed to update existing users.\n";
    exit(1);
}

echo "Users table update completed successfully!\n";
?>