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
            echo "Department column added successfully to the records table.<br>";

            // Fill existing records with default value "-"
            $updateResult = $db->executeRawQuery("UPDATE records SET department = '-' WHERE department IS NULL OR department = ''");
            if ($updateResult !== false) {
                echo "Existing records updated with default department value '-'.";
            } else {
                echo "Warning: Could not update existing records with default department value.";
            }
        } else {
            echo "Failed to add department column to the records table.";
        }
    } else {
        echo "Department column already exists in the records table.<br>";

        // Check if there are any records with empty department values and fill them
        $updateResult = $db->executeRawQuery("UPDATE records SET department = '-' WHERE department IS NULL OR department = ''");
        if ($updateResult !== false) {
            echo "Existing records with empty department values updated to '-'.";
        } else {
            echo "No records needed updating or update failed.";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>