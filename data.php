<?php
require_once 'config.php';
require_once 'db.php'; // Your database class

$db = new Database($db_host, $db_name, $db_usr, $db_password);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $record = [
                'id' => bin2hex(random_bytes(8)),
                'number' => intval($_POST['number']),
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'position' => $_POST['position']
            ];
            $db->insert($record);
            echo json_encode(['status' => 'success', 'entry' => $record]);
            break;
        case 'edit':
            $record = [
                'id' => $_POST['id'],
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'position' => $_POST['position']
            ];
            $db->update($record);
            echo json_encode(['status' => 'success']);
            break;
        case 'delete':
            $id = $_POST['id'];
            $db->delete($id);
            echo json_encode(['status' => 'success']);
            break;
    }
    exit;
}

// On GET, just fetch all data for initial load
echo json_encode($db->getAll());
?>
