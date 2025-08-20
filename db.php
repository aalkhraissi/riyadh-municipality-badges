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
}
?>
