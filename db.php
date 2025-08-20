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

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM records WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMaxNumber() {
        $stmt = $this->pdo->query("SELECT MAX(number) as max_number FROM records");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['max_number'] ?? 0;
    }

    public function insert($record) {
        $stmt = $this->pdo->prepare("REPLACE INTO records (id, number, name, email, position) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $record['id'], $record['number'], $record['name'], $record['email'], $record['position']
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM records WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function update($record) {
        $stmt = $this->pdo->prepare("UPDATE records SET name = ?, email = ?, position = ? WHERE id = ?");
        return $stmt->execute([
            $record['name'], $record['email'], $record['position'], $record['id']
        ]);
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function addUser($username, $password, $name) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $name]);
    }

    public function getUserByName($username) {
        $stmt = $this->pdo->prepare("SELECT id, username, name FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function updateUsersWithDefaultName($defaultName = 'Administrator') {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET name = ? WHERE name = ? OR name = '' OR name IS NULL");
            $stmt->execute([$defaultName, 'Administrator']);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
