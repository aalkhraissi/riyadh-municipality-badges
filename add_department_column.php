<?php
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);

    // Check if department column already exists
    $columnExists = $db->checkColumnExists('records', 'department');

    if (!$columnExists) {
        // Add the department column if it doesn't exist
        $result = $db->executeRawQuery("ALTER TABLE records ADD COLUMN department VARCHAR(255) DEFAULT ''");
        if ($result !== false) {
            echo "Department column added successfully to the records table.";
        } else {
            echo "Failed to add department column to the records table.";
        }
    } else {
        echo "Department column already exists in the records table.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>