<?php
// db.php
class Database {
    private $pdo;

    public function __construct($host, $dbname, $user, $pass) {
        // Use localhost if host is not specified or is empty
        if (empty($host)) {
            $host = "127.0.0.1";
        }
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        try {
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("DB connection failed: " . $e->getMessage());
        }
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM records ORDER BY number ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllFiltered($userBranchAccessType = null, $userAssignedBranches = null) {
        // Check if branch_id column exists
        $hasBranchColumn = $this->checkColumnExists('records', 'branch_id');

        if ($userBranchAccessType === 'all_branches' || !$userBranchAccessType || !$hasBranchColumn) {
            // Admin or user with all branches access, or branch_id column doesn't exist
            $stmt = $this->pdo->query("SELECT * FROM records ORDER BY number ASC");
        } else {
            // User with specific branch access
            if ($userAssignedBranches && is_array($userAssignedBranches)) {
                $placeholders = str_repeat('?,', count($userAssignedBranches) - 1) . '?';
                $stmt = $this->pdo->prepare("SELECT * FROM records WHERE branch_id IN ($placeholders) ORDER BY number ASC");
                $stmt->execute($userAssignedBranches);
            } else {
                // No assigned branches, return empty array
                return [];
            }
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM records WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMaxNumber($branchId = null) {
        if ($branchId) {
            // Get max number for specific branch
            $stmt = $this->pdo->prepare("SELECT MAX(number) as max_number FROM records WHERE branch_id = ?");
            $stmt->execute([$branchId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['max_number'] ?? 0;
        } else {
            // Get global max number (fallback)
            $stmt = $this->pdo->query("SELECT MAX(number) as max_number FROM records");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['max_number'] ?? 0;
        }
    }

    public function insert($record) {
        $stmt = $this->pdo->prepare("INSERT INTO records (id, number, name, general_administration, administration, department, email, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE number=VALUES(number), name=VALUES(name), general_administration=VALUES(general_administration), administration=VALUES(administration), department=VALUES(department), email=VALUES(email), branch_id=VALUES(branch_id)");
        $stmt->execute([
            $record['id'], $record['number'], $record['name'], $record['general_administration'] ?? '', $record['administration'], $record['department'] ?? '', $record['email'], $record['branch_id'] ?? null
        ]);
        return $stmt->rowCount();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM records WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function update($record) {
        $stmt = $this->pdo->prepare("UPDATE records SET name = ?, general_administration = ?, administration = ?, department = ?, email = ?, branch_id = ? WHERE id = ?");
        $stmt->execute([
            $record['name'], $record['general_administration'] ?? '', $record['administration'], $record['department'] ?? '', $record['email'], $record['branch_id'] ?? null, $record['id']
        ]);
        return $stmt->rowCount();
    }

    public function authenticateUser($username, $password) {
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
        return false;
    }

    public function createUsersTable() {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(100) NOT NULL,
                role ENUM('admin', 'manager', 'user') DEFAULT 'user',
                branch_access JSON DEFAULT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_role (role),
                INDEX idx_active (is_active)
            )");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function addUser($username, $password, $name, $role = 'user', $branchAccess = null, $isActive = true) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $branchAccessJson = $branchAccess ? json_encode($branchAccess) : null;
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, name, role, branch_access, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $name, $role, $branchAccessJson, $isActive]);
    }

    public function getUserByName($username) {
        $stmt = $this->pdo->prepare("SELECT id, username, name, role, branch_access, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT id, username, name, role, branch_access, is_active, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT id, username, name, role, branch_access, is_active, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($user) {
        try {
            // Handle branch access properly
            $branchAccessJson = null;
            if (isset($user['assigned_branches'])) {
                // New format: assigned_branches is passed separately
                $assignedBranches = $user['assigned_branches'];
                $branchAccessType = $user['branch_access_type'] ?? 'all_branches';

                if ($assignedBranches === "" || $assignedBranches === null || (is_array($assignedBranches) && empty($assignedBranches))) {
                    if ($branchAccessType === 'all_branches') {
                        $branchAccessJson = json_encode([
                            'type' => 'all_branches',
                            'assigned_branches' => []
                        ]);
                    } else {
                        $branchAccessJson = null;
                    }
                } else {
                    $branchAccessJson = json_encode([
                        'type' => $branchAccessType,
                        'assigned_branches' => $assignedBranches
                    ]);
                }
            } elseif (isset($user['branch_access'])) {
                // Old format: branch_access is passed as JSON string
                $branchAccessJson = $user['branch_access'];
            }

            error_log("Updating user ID: " . $user['id'] . " with data: " . json_encode($user));
            error_log("Branch access JSON: " . $branchAccessJson);

            $stmt = $this->pdo->prepare("UPDATE users SET name = ?, role = ?, branch_access = ?, is_active = ? WHERE id = ?");
            $result = $stmt->execute([
                $user['name'],
                $user['role'] ?? 'user',
                $branchAccessJson,
                $user['is_active'] ?? true,
                $user['id']
            ]);

            $rowCount = $stmt->rowCount();
            error_log("Update result: " . ($result ? 'success' : 'failed') . ", rows affected: " . $rowCount);

            return $rowCount;
        } catch (PDOException $e) {
            error_log("Database error in updateUser: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateUserPassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function addNameColumnToUsersTable() {
        try {
            // Check if the name column already exists
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM users LIKE 'name'");
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if (!$columnExists) {
                // Add the name column if it doesn't exist
                $this->pdo->exec("ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT 'Administrator'");
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function addRoleColumnsToUsersTable() {
        try {
            // Add role column if it doesn't exist
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM users LIKE 'role'");
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if (!$columnExists) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'manager', 'user') DEFAULT 'user'");
            }

            // Add branch_access column if it doesn't exist
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM users LIKE 'branch_access'");
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if (!$columnExists) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN branch_access JSON DEFAULT NULL");
            }

            // Add is_active column if it doesn't exist
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM users LIKE 'is_active'");
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if (!$columnExists) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
            }

            // Add updated_at column if it doesn't exist
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM users LIKE 'updated_at'");
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if (!$columnExists) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateUsersWithDefaultName($defaultName = 'Administrator') {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET name = ? WHERE name = ? OR name = '' OR name IS NULL");
            $stmt->execute([$defaultName, 'Administrator']);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function executeRawQuery($query) {
        try {
            return $this->pdo->exec($query);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function executeSelectQuery($query) {
        try {
            $stmt = $this->pdo->query($query);
            return $stmt;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function checkColumnExists($table, $column) {
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
            $stmt->execute([$column]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function checkTableExists($table) {
        try {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function createTable($query) {
        try {
            $this->pdo->exec($query);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Add branch_id column to records table if it doesn't exist
    public function addBranchIdColumnToRecords() {
        try {
            // Check if the branch_id column already exists
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM records LIKE 'branch_id'");
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if (!$columnExists) {
                // Add the branch_id column if it doesn't exist
                $this->pdo->exec("ALTER TABLE records ADD COLUMN branch_id INT DEFAULT NULL");
                return true;
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function addBranchIdColumnToRecordsTable() {
        try {
            // Check if the branch_id column already exists
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM records LIKE 'branch_id'");
            $stmt->execute();
            $columnExists = $stmt->fetch();

            if (!$columnExists) {
                // Add the branch_id column if it doesn't exist
                $this->pdo->exec("ALTER TABLE records ADD COLUMN branch_id INT NULL");
                // Add foreign key constraint
                $this->pdo->exec("ALTER TABLE records ADD CONSTRAINT fk_branch_id FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL");
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Branches methods
    public function getBranches() {
        $stmt = $this->pdo->query("SELECT * FROM branches ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBranchById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertBranch($branch) {
        $stmt = $this->pdo->prepare("INSERT INTO branches (name) VALUES (?)");
        $stmt->execute([$branch['name']]);
        return $this->pdo->lastInsertId();
    }

    public function updateBranch($branch) {
        $stmt = $this->pdo->prepare("UPDATE branches SET name = ? WHERE id = ?");
        $stmt->execute([$branch['name'], $branch['id']]);
        return $stmt->rowCount();
    }

    public function deleteBranch($id) {
        $stmt = $this->pdo->prepare("DELETE FROM branches WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function createBranchesTable() {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS branches (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function executePreparedQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
