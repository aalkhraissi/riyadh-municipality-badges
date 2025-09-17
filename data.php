<?php
require_once './config/config.php';
require_once 'db.php'; // Your database class

$db = new Database($db_host, $db_name, $db_usr, $db_password);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $branchId = $_POST['branch_id'] ?? null;
            $number = intval($_POST['number']);

            // If no number provided or branch-specific numbering, get next number for branch
            if ($number === 0 || $branchId) {
                $maxNumber = $db->getMaxNumber($branchId);
                $number = $maxNumber + 1;
            }

            $record = [
                'id' => bin2hex(random_bytes(8)),
                'number' => $number,
                'name' => $_POST['name'],
                'email' => strtolower($_POST['email']),
                'department' => $_POST['department'] ?? '',
                'general_administration' => $_POST['general_administration'] ?? '',
                'administration' => $_POST['administration'],
                'branch_id' => $branchId
            ];
            $db->insert($record);
            echo json_encode(['status' => 'success', 'entry' => $record]);
            break;
        case 'edit':
            $record = [
                'id' => $_POST['id'],
                'name' => $_POST['name'],
                'email' => strtolower($_POST['email']),
                'department' => $_POST['department'] ?? '',
                'general_administration' => $_POST['general_administration'] ?? '',
                'administration' => $_POST['administration'],
                'branch_id' => $_POST['branch_id'] ?? null
            ];
            $db->update($record);
            echo json_encode(['status' => 'success']);
            break;
        case 'delete':
            $id = $_POST['id'];
            $db->delete($id);
            echo json_encode(['status' => 'success']);
            break;
        case 'get_max_number':
            $branchId = $_POST['branch_id'] ?? null;
            $maxNumber = $db->getMaxNumber($branchId);
            echo json_encode(['max_number' => $maxNumber]);
            break;
    }
    exit;
}

// On GET, fetch data filtered by user permissions
$userBranchAccessType = $_GET['branch_access_type'] ?? null;
$userAssignedBranches = isset($_GET['assigned_branches']) ? json_decode($_GET['assigned_branches'], true) : null;

$data = $db->getAllFiltered($userBranchAccessType, $userAssignedBranches);
error_log("data.php called, returning " . count($data) . " records for user with access type: " . ($userBranchAccessType ?? 'all'));
echo json_encode($data);
?>
