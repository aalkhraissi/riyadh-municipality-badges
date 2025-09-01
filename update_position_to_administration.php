<?php
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);

    echo "<h2>Database Column Update Script</h2>";
    echo "<p>Updating 'position' column to 'administration' and ensuring correct column order...</p>";

    // Check if position column exists
    $positionExists = $db->checkColumnExists('records', 'position');

    if ($positionExists) {
        echo "<p>✓ Found 'position' column. Renaming to 'administration'...</p>";

        // Rename position column to administration
        $renameResult = $db->executeRawQuery("ALTER TABLE records CHANGE COLUMN position administration VARCHAR(255)");
        if ($renameResult !== false) {
            echo "<p>✓ Successfully renamed 'position' column to 'administration'.</p>";
        } else {
            echo "<p>✗ Failed to rename 'position' column to 'administration'.</p>";
            exit;
        }
    } else {
        echo "<p>✓ 'position' column not found. Checking for 'administration' column...</p>";
    }

    // Check if administration column exists
    $adminExists = $db->checkColumnExists('records', 'administration');

    if (!$adminExists) {
        echo "<p>✗ 'administration' column not found. Creating it...</p>";

        // Add administration column
        $addResult = $db->executeRawQuery("ALTER TABLE records ADD COLUMN administration VARCHAR(255)");
        if ($addResult !== false) {
            echo "<p>✓ Successfully added 'administration' column.</p>";
        } else {
            echo "<p>✗ Failed to add 'administration' column.</p>";
            exit;
        }
    } else {
        echo "<p>✓ 'administration' column already exists.</p>";
    }

    // Check if department column exists
    $deptExists = $db->checkColumnExists('records', 'department');

    if (!$deptExists) {
        echo "<p>✗ 'department' column not found. Creating it...</p>";

        // Add department column
        $addDeptResult = $db->executeRawQuery("ALTER TABLE records ADD COLUMN department VARCHAR(255) DEFAULT ''");
        if ($addDeptResult !== false) {
            echo "<p>✓ Successfully added 'department' column.</p>";

            // Fill existing records with empty value
            $updateResult = $db->executeRawQuery("UPDATE records SET department = '' WHERE department IS NULL");
            if ($updateResult !== false) {
                echo "<p>✓ Existing records updated with empty department value.</p>";
            } else {
                echo "<p>⚠ Could not update existing records with empty department value.</p>";
            }
        } else {
            echo "<p>✗ Failed to add 'department' column.</p>";
            exit;
        }
    } else {
        echo "<p>✓ 'department' column already exists.</p>";
    }

    // Check current column order and fix if necessary
    echo "<p>Checking column order...</p>";

    // Get current table structure
    $columnsResult = $db->executeSelectQuery("SHOW COLUMNS FROM records");
    if ($columnsResult !== false) {
        $columns = [];
        while ($row = $columnsResult->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }
    } else {
        echo "<p>⚠ Could not retrieve table structure.</p>";
        $columns = [];
    }

    if ($columnsResult !== false) {

        // Find positions of administration and department
        $adminPos = array_search('administration', $columns);
        $deptPos = array_search('department', $columns);

        if ($adminPos !== false && $deptPos !== false) {
            if ($adminPos > $deptPos) {
                echo "<p>⚠ Column order is incorrect. 'administration' should come before 'department'.</p>";
                echo "<p>Reordering columns...</p>";

                // Create a new table with correct order
                $createTempResult = $db->executeRawQuery("
                    CREATE TABLE records_temp AS
                    SELECT id, number, name, email, administration, department
                    FROM records
                ");

                if ($createTempResult !== false) {
                    // Drop original table
                    $dropResult = $db->executeRawQuery("DROP TABLE records");
                    if ($dropResult !== false) {
                        // Rename temp table to original
                        $renameResult = $db->executeRawQuery("ALTER TABLE records_temp RENAME TO records");
                        if ($renameResult !== false) {
                            echo "<p>✓ Successfully reordered columns. 'administration' now comes before 'department'.</p>";
                        } else {
                            echo "<p>✗ Failed to rename temp table.</p>";
                        }
                    } else {
                        echo "<p>✗ Failed to drop original table.</p>";
                    }
                } else {
                    echo "<p>✗ Failed to create temp table.</p>";
                }
            } else {
                echo "<p>✓ Column order is correct. 'administration' comes before 'department'.</p>";
            }
        } else {
            echo "<p>⚠ Could not determine column positions.</p>";
        }
    } else {
        echo "<p>⚠ Could not retrieve table structure.</p>";
    }

    // Verify the final structure
    echo "<p><strong>Final verification:</strong></p>";
    $finalColumnsResult = $db->executeSelectQuery("SHOW COLUMNS FROM records");
    if ($finalColumnsResult !== false) {
        echo "<ul>";
        while ($row = $finalColumnsResult->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>{$row['Field']} - {$row['Type']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>⚠ Could not retrieve final table structure.</p>";
    }

    echo "<p><strong>✓ Database update completed successfully!</strong></p>";
    echo "<p>You can now use the updated application with the new column structure.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>