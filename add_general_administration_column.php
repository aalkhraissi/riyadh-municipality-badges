<?php
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);

    // Check if general_administration column already exists
    $columnExists = $db->checkColumnExists('records', 'general_administration');

    if (!$columnExists) {
        // Add the general_administration column after department
        $result = $db->executeRawQuery("ALTER TABLE records ADD COLUMN general_administration VARCHAR(255) DEFAULT '' AFTER department");
        if ($result !== false) {
            echo "General Administration column added successfully to the records table.<br>";

            // Fill existing records with default value "-"
            $updateResult = $db->executeRawQuery("UPDATE records SET general_administration = '-' WHERE general_administration IS NULL OR general_administration = ''");
            if ($updateResult !== false) {
                echo "Existing records updated with default general administration value '-'.";
            } else {
                echo "Warning: Could not update existing records with default general administration value.";
            }
        } else {
            echo "Failed to add general administration column to the records table.";
        }
    } else {
        echo "General Administration column already exists in the records table.<br>";

        // Check if there are any records with empty general_administration values and fill them
        $updateResult = $db->executeRawQuery("UPDATE records SET general_administration = '-' WHERE general_administration IS NULL OR general_administration = ''");
        if ($updateResult !== false) {
            echo "Existing records with empty general administration values updated to '-'.";
        } else {
            echo "No records needed updating or update failed.";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>