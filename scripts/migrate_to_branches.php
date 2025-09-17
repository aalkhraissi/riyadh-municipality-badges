<?php
// migrate_to_branches.php - One-time migration script to create default branch and assign to existing records

require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);

    // Create branches table if it doesn't exist
    $db->createBranchesTable();
    echo "Ensured branches table exists\n";

    // Ensure branch_id column exists in records table
    $db->addBranchIdColumnToRecordsTable();
    echo "Ensured branch_id column exists in records table\n";

    // Create default branch if it doesn't exist
    $defaultBranchName = "الفرع الرئيسي";
    $existingBranches = $db->getBranches();
    $defaultBranchId = null;

    foreach ($existingBranches as $branch) {
        if ($branch['name'] === $defaultBranchName) {
            $defaultBranchId = $branch['id'];
            break;
        }
    }

    if (!$defaultBranchId) {
        // Create default branch
        $defaultBranch = ['name' => $defaultBranchName];
        $defaultBranchId = $db->insertBranch($defaultBranch);
        echo "Created default branch: $defaultBranchName (ID: $defaultBranchId)\n";
    } else {
        echo "Default branch already exists: $defaultBranchName (ID: $defaultBranchId)\n";
    }

    // Count records without branch_id
    $stmt = $db->executePreparedQuery("SELECT COUNT(*) as count FROM records WHERE branch_id IS NULL OR branch_id = ''");
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $recordsToUpdate = $result['count'];

        if ($recordsToUpdate > 0) {
            // Assign default branch to records that don't have branch_id
            $updateStmt = $db->executePreparedQuery("UPDATE records SET branch_id = ? WHERE branch_id IS NULL OR branch_id = ''", [$defaultBranchId]);

            if ($updateStmt) {
                echo "Assigned default branch to $recordsToUpdate existing records\n";
            } else {
                echo "Failed to update records\n";
            }
        } else {
            echo "All records already have branch_id assigned\n";
        }
    } else {
        echo "Failed to count records\n";
    }

    // Show summary
    $totalRecords = count($db->getAll());
    $totalBranches = count($db->getBranches());

    echo "\nMigration completed successfully!\n";
    echo "Total records: $totalRecords\n";
    echo "Total branches: $totalBranches\n";
    echo "Default branch ID: $defaultBranchId\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nYou can now safely remove this migration script.\n";
?>