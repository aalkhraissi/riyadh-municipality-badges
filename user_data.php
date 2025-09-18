<?php
require_once './config/config.php';
require_once 'db.php'; // Your database class

$db = new Database($db_host, $db_name, $db_usr, $db_password);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $username = $_POST['username'];
            $password = $_POST['password'];
            $name = $_POST['name'];
            $branchAccessType = $_POST['branch_access_type'] ?? 'all_branches';
            $assignedBranches = isset($_POST['assigned_branches']) ? $_POST['assigned_branches'] : null;
            $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
            $role = $isAdmin ? 'admin' : 'user'; // Convert is_admin to role

            // Check if username already exists
            $existingUser = $db->getUserByName($username);
            if ($existingUser) {
                echo json_encode(['status' => 'error', 'message' => 'اسم المستخدم موجود بالفعل']);
                exit;
            }

            $result = $db->addUser($username, $password, $name, $role, $assignedBranches, true);
            if ($result) {
                $newUser = $db->getUserByName($username);
                echo json_encode(['status' => 'success', 'entry' => $newUser]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'فشل في إضافة المستخدم']);
            }
            break;

        case 'edit':
            $id = $_POST['id'];
            $name = $_POST['name'];
            $branchAccessType = $_POST['branch_access_type'] ?? 'all_branches';
            $assignedBranches = isset($_POST['assigned_branches']) ? $_POST['assigned_branches'] : null;
            $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
            $role = $isAdmin ? 'admin' : 'user'; // Convert is_admin to role

            // Handle assigned_branches - ensure it's an array if it exists
            if ($assignedBranches !== null && $assignedBranches !== "") {
                if (is_string($assignedBranches)) {
                    // If it's a JSON string, decode it
                    $assignedBranches = json_decode($assignedBranches, true);
                } elseif (!is_array($assignedBranches)) {
                    // If it's not an array, convert to array
                    $assignedBranches = [$assignedBranches];
                }
            } else {
                // If it's null or empty string, set to null
                $assignedBranches = null;
            }

            $user = [
                'id' => $id,
                'name' => $name,
                'role' => $role,
                'branch_access' => $assignedBranches ? json_encode([
                    'type' => $branchAccessType,
                    'assigned_branches' => $assignedBranches
                ]) : null,
                'is_active' => true
            ];

            // First check if user exists
            $existingUser = $db->getUserById($id);
            if (!$existingUser) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود',
                    'debug' => [
                        'user_id' => $id,
                        'error' => 'User not found in database'
                    ]
                ]);
                exit;
            }

            try {
                $result = $db->updateUser($user);
                if ($result) {
                    echo json_encode(['status' => 'success']);
                } else {
                    // Return detailed error information
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'فشل في تحديث المستخدم',
                        'debug' => [
                            'user_id' => $id,
                            'user_data' => $user,
                            'result' => $result,
                            'existing_user' => $existingUser
                        ]
                    ]);
                }
            } catch (Exception $e) {
                // Return the actual error message
                echo json_encode([
                    'status' => 'error',
                    'message' => 'فشل في تحديث المستخدم',
                    'debug' => [
                        'error' => $e->getMessage(),
                        'user_id' => $id,
                        'user_data' => $user,
                        'existing_user' => $existingUser
                    ]
                ]);
            }
            break;

        case 'delete':
            $id = $_POST['id'];
            $result = $db->deleteUser($id);
            if ($result) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'فشل في حذف المستخدم']);
            }
            break;

        case 'reset_password':
            $id = $_POST['id'];
            $newPassword = $_POST['new_password'];
            $result = $db->updateUserPassword($id, $newPassword);
            if ($result) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'فشل في إعادة تعيين كلمة المرور']);
            }
            break;
    }
    exit;
}

// On GET, just fetch all users for initial load
$users = $db->getAllUsers();
error_log("user_data.php called, returning " . count($users) . " users");
echo json_encode($users);
?>