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
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

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
        email VARCHAR(255),
        department VARCHAR(255) DEFAULT '',
        administration VARCHAR(255)
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

// Get the current maximum number to use for new records
$maxNumber = $db->getMaxNumber();

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
        continue;
    }
    $totalRows++;
    $rowData = array_combine($headers, $row);
    $id = $rowData['id'] ?? '';
    $name = $rowData['name'] ?? '';
    $email = $rowData['email'] ?? '';
    $administration = $rowData['administration'] ?? '';
    $department = $rowData['department'] ?? '';

    // Skip rows without ID
    if (empty($id)) {
        continue;
    }

    // Check if record exists
    $existingRecord = $db->getById($id);

    if ($existingRecord) {
        // Update existing record, keep the existing number
        $record = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'administration' => $administration
        ];

        try {
            $affected = $db->update($record);
            if ($affected > 0) {
                $importedCount++;
            } else {
                $errors[] = "No rows updated for ID $id.";
            }
        } catch (Exception $e) {
            $errors[] = "Error updating record with ID $id: " . $e->getMessage();
        }
    } else {
        // Create new record with incremented number
        $maxNumber++;
        $number = $maxNumber;

        $record = [
            'id' => $id,
            'number' => $number,
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'administration' => $administration
        ];

        try {
            $affected = $db->insert($record);
            if ($affected > 0) {
                $importedCount++;
            } else {
                $errors[] = "No rows inserted for ID $id.";
            }
        } catch (Exception $e) {
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
