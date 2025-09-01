<?php
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);

    echo "Reordering database columns...<br>";

    // Reorder columns to: id, number, name, general_administration, administration, department, email
    $reorderQueries = [
        "ALTER TABLE records MODIFY COLUMN general_administration VARCHAR(255) DEFAULT '' AFTER name",
        "ALTER TABLE records MODIFY COLUMN email VARCHAR(255) AFTER department"
    ];

    foreach ($reorderQueries as $query) {
        $result = $db->executeRawQuery($query);
        if (!$result) {
            echo "Failed to execute query: $query<br>";
            exit;
        }
    }

    echo "Column reordering completed successfully.<br>";
    echo "New column order: id, number, name, general_administration, administration, department, email<br>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>