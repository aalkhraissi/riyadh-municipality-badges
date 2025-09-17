<?php
require_once './config/config.php';
require_once 'db.php'; // Your database class

$db = new Database($db_host, $db_name, $db_usr, $db_password);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $branch = [
                'name' => $_POST['name']
            ];
            $id = $db->insertBranch($branch);
            $branch['id'] = $id;
            echo json_encode(['status' => 'success', 'entry' => $branch]);
            break;
        case 'edit':
            $branch = [
                'id' => $_POST['id'],
                'name' => $_POST['name']
            ];
            $db->updateBranch($branch);
            echo json_encode(['status' => 'success']);
            break;
        case 'delete':
            $id = $_POST['id'];
            $db->deleteBranch($id);
            echo json_encode(['status' => 'success']);
            break;
    }
    exit;
}

// On GET, just fetch all branches for initial load
$branches = $db->getBranches();
error_log("branch_data.php called, returning " . count($branches) . " branches");
echo json_encode($branches);
?>