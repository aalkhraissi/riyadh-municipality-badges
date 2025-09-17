<?php
// import_csv.php - Import CSV to database

// Start output buffering to prevent any unwanted output
ob_start();

// Set error handling to not display errors in output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Custom error handler to return JSON on fatal errors
function jsonErrorHandler($errno, $errstr, $errfile, $errline) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'PHP Error: ' . $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    exit;
}

// Shutdown function to handle fatal errors
function jsonShutdownHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Fatal PHP Error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
}

set_error_handler('jsonErrorHandler');
register_shutdown_function('jsonShutdownHandler');

header('Content-Type: application/json');
mb_internal_encoding('UTF-8');

try {
    require_once './config/config.php';
    require_once 'db.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit;
}

// Check upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csvfile'])) {
    error_log("Import CSV - No file uploaded or not POST request");
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("FILES: " . print_r($_FILES, true));
    error_log("POST: " . print_r($_POST, true));
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

// Get branch_id from POST data
$branchId = isset($_POST['branch_id']) ? $_POST['branch_id'] : null;
error_log("Import CSV - Branch ID: " . ($branchId ?? 'null'));

if ($_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Upload error: ' . $_FILES['csvfile']['error']]);
    exit;
}

$csvPath = $_FILES['csvfile']['tmp_name'];
if (!file_exists($csvPath)) {
    echo json_encode(['status' => 'error', 'message' => 'CSV file not found']);
    exit;
}

// Connect to database
try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if records table exists, if not, create it
if (!$db->checkTableExists('records')) {
    $createTableQuery = "CREATE TABLE records (
        id VARCHAR(255) PRIMARY KEY,
        number INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        general_administration VARCHAR(255) DEFAULT '',
        administration VARCHAR(255),
        department VARCHAR(255) DEFAULT '',
        email VARCHAR(255)
    )";
    $db->createTable($createTableQuery);
}

// Check if department column exists, if not, add it
if (!$db->checkColumnExists('records', 'department')) {
    $result = $db->executeRawQuery("ALTER TABLE records ADD COLUMN department VARCHAR(255) DEFAULT ''");
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add department column to the records table.']);
        exit;
    }
}

// Check if general_administration column exists, if not, add it
if (!$db->checkColumnExists('records', 'general_administration')) {
    $result = $db->executeRawQuery("ALTER TABLE records ADD COLUMN general_administration VARCHAR(255) DEFAULT '' AFTER department");
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add general_administration column to the records table.']);
        exit;
    }
}

// Check if branch_id column exists, if not, add it
if (!$db->checkColumnExists('records', 'branch_id')) {
    $result = $db->executeRawQuery("ALTER TABLE records ADD COLUMN branch_id INT DEFAULT NULL");
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add branch_id column to the records table.']);
        exit;
    }
}

// Get the current maximum number for the selected branch
$maxNumber = $db->getMaxNumber($branchId);

// Read CSV with UTF-8 support
if (($handle = fopen($csvPath, 'r')) === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to open CSV']);
    exit;
}

$headers = [];
$importedCount = 0;
$totalRows = 0;
$messages = [];
$errors = [];

while (($row = fgetcsv($handle)) !== false) {
    error_log("Processing row: " . implode(',', $row));

    // Convert each field to UTF-8 if needed
    foreach ($row as $key => $value) {
        if (!mb_check_encoding($value, 'UTF-8')) {
            $row[$key] = mb_convert_encoding($value, 'UTF-8');
        }
    }

    if (empty($headers)) {
        // Clean headers: remove BOM and trim whitespace
        $headers = array_map(function($header) {
            return trim($header, "\xEF\xBB\xBF \t\n\r\0\x0B"); // Remove BOM and whitespace
        }, $row);
        error_log("Headers found: " . implode(',', $headers));
        continue;
    }
    $totalRows++;
    $rowData = array_combine($headers, $row);
    error_log("Row data: " . print_r($rowData, true));

    $id = trim($rowData['id'] ?? '');
    $name = $rowData['name'] ?? '';
    $email = strtolower($rowData['email'] ?? '');
    $administration = $rowData['administration'] ?? '';
    $department = $rowData['department'] ?? '';
    $generalAdministration = $rowData['general_administration'] ?? '';

    // Generate ID if empty
    if (empty($id)) {
        $id = bin2hex(random_bytes(8)); // Generate a unique 16-character ID
        error_log("Generated new ID: $id for row without ID");
    }

    // Handle number field - use from CSV if provided, otherwise auto-increment
    $number = null;
    if (isset($rowData['number']) && !empty(trim($rowData['number']))) {
        $number = intval(trim($rowData['number']));
        error_log("Using number from CSV: $number");
    } else {
        $maxNumber++;
        $number = $maxNumber;
        error_log("Auto-generated number: $number");
    }

    // Check if record exists
    $existingRecord = $db->getById($id);

    if ($existingRecord) {
        // Update existing record, keep the existing number
        $record = [
            'id' => $id,
            'name' => $name,
            'general_administration' => $generalAdministration,
            'administration' => $administration,
            'department' => $department,
            'email' => $email,
            'branch_id' => $branchId
        ];

        try {
            error_log("Updating record: " . print_r($record, true));
            $affected = $db->update($record);
            error_log("Update result for ID $id: $affected rows affected");
            if ($affected > 0) {
                $importedCount++;
            } else {
                $errors[] = "No rows updated for ID $id.";
            }
        } catch (Exception $e) {
            error_log("Update error for ID $id: " . $e->getMessage());
            $errors[] = "Error updating record with ID $id: " . $e->getMessage();
        }
    } else {
        // Create new record with the determined number
        $record = [
            'id' => $id,
            'number' => $number,
            'name' => $name,
            'general_administration' => $generalAdministration,
            'administration' => $administration,
            'department' => $department,
            'email' => $email,
            'branch_id' => $branchId
        ];

        try {
            error_log("Inserting record: " . print_r($record, true));
            $affected = $db->insert($record);
            error_log("Insert result for ID $id: $affected rows affected");
            if ($affected > 0) {
                $importedCount++;
            } else {
                $errors[] = "No rows inserted for ID $id.";
            }
        } catch (Exception $e) {
            error_log("Insert error for ID $id: " . $e->getMessage());
            $errors[] = "Error adding record with ID $id: " . $e->getMessage();
        }
    }
}
fclose($handle);

$status = (count($errors) == 0 && $importedCount > 0) ? 'success' : (count($errors) > 0 ? 'error' : 'no_data');
$message = "Import completed successfully! $importedCount records processed.";
if (count($errors) > 0) {
    $message .= " Some records had issues.";
}

error_log("Import CSV - Status: $status, Imported: $importedCount, Errors: " . count($errors) . ", Branch ID: " . ($branchId ?? 'null'));

// Clean output buffer and send only JSON
ob_clean();
echo json_encode([
    'status' => $status,
    'message' => $message,
    'totalRows' => $totalRows,
    'importedCount' => $importedCount,
    'messages' => $messages,
    'errors' => $errors
]);
?>
