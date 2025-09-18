<?php
/**
 * Database Schema Checker and Column Adder Script
 *
 * This script checks all required tables and columns in the database
 * and adds any missing columns or tables based on the expected schema.
 */

// Database connection settings (adjust as needed)
$db_host = 'localhost';
$db_name = 'riyadh_municipality'; // Change this to your database name
$db_usr = 'root';
$db_password = ''; // Change this if you have a password

require_once 'db.php';

echo "<h1>🔍 Checking and Updating Database Schema...</h1>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

try {
    // Create database connection
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
    echo "✅ Connected to database successfully!<br><br>";

    // Define expected schema
    $expectedSchema = [
        'users' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'username' => 'VARCHAR(50) UNIQUE NOT NULL',
            'password' => 'VARCHAR(255) NOT NULL',
            'name' => 'VARCHAR(100) NOT NULL',
            'role' => "ENUM('admin', 'manager', 'user') DEFAULT 'user'",
            'branch_access' => 'JSON DEFAULT NULL',
            'is_active' => 'BOOLEAN DEFAULT TRUE',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ],
        'branches' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(100) NOT NULL',
            'location' => 'VARCHAR(255)',
            'description' => 'TEXT',
            'is_active' => 'BOOLEAN DEFAULT TRUE',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ],
        'records' => [
            'id' => 'VARCHAR(32) PRIMARY KEY',
            'number' => 'INT NOT NULL',
            'name' => 'VARCHAR(255) NOT NULL',
            'email' => 'VARCHAR(255)',
            'department' => 'VARCHAR(255)',
            'general_administration' => 'VARCHAR(255)',
            'administration' => 'VARCHAR(255)',
            'branch_id' => 'INT DEFAULT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ],
        'roles' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(50) UNIQUE NOT NULL',
            'description' => 'TEXT',
            'permissions' => 'JSON DEFAULT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'user_sessions' => [
            'id' => 'VARCHAR(128) PRIMARY KEY',
            'user_id' => 'INT NOT NULL',
            'ip_address' => 'VARCHAR(45)',
            'user_agent' => 'TEXT',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'expires_at' => 'TIMESTAMP NOT NULL'
        ],
        'audit_log' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'user_id' => 'INT',
            'action' => 'VARCHAR(100) NOT NULL',
            'table_name' => 'VARCHAR(50)',
            'record_id' => 'VARCHAR(32)',
            'old_values' => 'JSON DEFAULT NULL',
            'new_values' => 'JSON DEFAULT NULL',
            'ip_address' => 'VARCHAR(45)',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ];

    // Define indexes
    $expectedIndexes = [
        'users' => [
            'idx_username' => 'username',
            'idx_role' => 'role',
            'idx_active' => 'is_active'
        ],
        'branches' => [
            'idx_name' => 'name',
            'idx_active' => 'is_active'
        ],
        'records' => [
            'idx_number' => 'number',
            'idx_name' => 'name',
            'idx_email' => 'email',
            'idx_department' => 'department',
            'idx_general_administration' => 'general_administration',
            'idx_administration' => 'administration',
            'idx_branch_id' => 'branch_id'
        ],
        'roles' => [
            'idx_name' => 'name'
        ],
        'user_sessions' => [
            'idx_user_id' => 'user_id',
            'idx_expires_at' => 'expires_at'
        ],
        'audit_log' => [
            'idx_user_id' => 'user_id',
            'idx_action' => 'action',
            'idx_table_name' => 'table_name',
            'idx_created_at' => 'created_at'
        ]
    ];

    // Define foreign keys
    $expectedForeignKeys = [
        'records' => [
            'fk_branch_id' => 'FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL'
        ],
        'user_sessions' => [
            'fk_user_id' => 'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'
        ],
        'audit_log' => [
            'fk_user_id' => 'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL'
        ]
    ];

    // Process each table
    foreach ($expectedSchema as $tableName => $columns) {
        echo "<h3>📋 Checking table: $tableName</h3>";

        // Check if table exists
        if (!$db->checkTableExists($tableName)) {
            echo "❌ Table '$tableName' does not exist. Creating...<br>";
            // Create table with all columns
            $createSQL = "CREATE TABLE $tableName (";
            $columnDefs = [];
            foreach ($columns as $colName => $colDef) {
                $columnDefs[] = "$colName $colDef";
            }
            $createSQL .= implode(', ', $columnDefs) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            if ($db->createTable($createSQL)) {
                echo "✅ Table '$tableName' created successfully!<br>";
            } else {
                echo "❌ Failed to create table '$tableName'!<br>";
                continue;
            }
        } else {
            echo "✅ Table '$tableName' exists.<br>";
        }

        // Check each column
        foreach ($columns as $columnName => $columnDefinition) {
            if (!$db->checkColumnExists($tableName, $columnName)) {
                echo "❌ Column '$columnName' missing in '$tableName'. Adding...<br>";
                $alterSQL = "ALTER TABLE $tableName ADD COLUMN $columnName $columnDefinition";
                if ($db->executeRawQuery($alterSQL) !== false) {
                    echo "✅ Column '$columnName' added successfully!<br>";
                } else {
                    echo "❌ Failed to add column '$columnName'!<br>";
                }
            } else {
                echo "✅ Column '$columnName' exists.<br>";
            }
        }

        // Add indexes if defined
        if (isset($expectedIndexes[$tableName])) {
            foreach ($expectedIndexes[$tableName] as $indexName => $indexColumn) {
                // Check if index exists (simplified check)
                $indexExists = false;
                try {
                    $stmt = $db->executeSelectQuery("SHOW INDEX FROM $tableName WHERE Key_name = ?", [$indexName]);
                    $indexExists = $stmt && $stmt->fetch();
                } catch (Exception $e) {
                    // Index might not exist
                }

                if (!$indexExists) {
                    echo "❌ Index '$indexName' missing. Adding...<br>";
                    $indexSQL = "ALTER TABLE $tableName ADD INDEX $indexName ($indexColumn)";
                    if ($db->executeRawQuery($indexSQL) !== false) {
                        echo "✅ Index '$indexName' added successfully!<br>";
                    } else {
                        echo "❌ Failed to add index '$indexName'!<br>";
                    }
                } else {
                    echo "✅ Index '$indexName' exists.<br>";
                }
            }
        }

        // Add foreign keys if defined
        if (isset($expectedForeignKeys[$tableName])) {
            foreach ($expectedForeignKeys[$tableName] as $fkName => $fkDefinition) {
                // Check if foreign key exists (simplified check)
                $fkExists = false;
                try {
                    $stmt = $db->executeSelectQuery("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.TABLE_CONSTRAINTS
                        WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                    ", [$db_name, $tableName, $fkName]);
                    $fkExists = $stmt && $stmt->fetch();
                } catch (Exception $e) {
                    // FK might not exist
                }

                if (!$fkExists) {
                    echo "❌ Foreign key '$fkName' missing. Adding...<br>";
                    $fkSQL = "ALTER TABLE $tableName ADD CONSTRAINT $fkName $fkDefinition";
                    if ($db->executeRawQuery($fkSQL) !== false) {
                        echo "✅ Foreign key '$fkName' added successfully!<br>";
                    } else {
                        echo "❌ Failed to add foreign key '$fkName'!<br>";
                    }
                } else {
                    echo "✅ Foreign key '$fkName' exists.<br>";
                }
            }
        }

        echo "<br>";
    }

    echo "</div>";
    echo "<h1>🎉 Schema Check Complete!</h1>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; border: 1px solid #4caf50;'>";
    echo "<p>All required tables and columns have been checked and added if missing.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "</div>";
    echo "<h1>❌ Error</h1>";
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 5px; border: 1px solid #f44336;'>";
    echo "<p><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>