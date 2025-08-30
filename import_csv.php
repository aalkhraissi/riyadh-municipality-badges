<?php
// import_csv.php - Import CSV to database

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

require_once './config/config.php';
require_once 'db.php';

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
        position VARCHAR(255),
        department VARCHAR(255) DEFAULT ''
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
        $messages[] = "Headers found: " . implode(', ', $headers);
        continue;
    }
    $totalRows++;
    $rowData = array_combine($headers, $row);
    if ($totalRows == 1) {
        $messages[] = "Available keys: " . implode(', ', array_keys($rowData));
    }
    if ($totalRows <= 3) { // Debug first 3 rows
        $messages[] = "Row $totalRows data: " . json_encode($rowData);
    }
    $id = $rowData['id'] ?? '';
    $name = $rowData['name'] ?? '';
    $email = $rowData['email'] ?? '';
    $department = $rowData['department'] ?? '';
    $position = $rowData['position'] ?? '';

    // Debug ID for first few rows
    if ($totalRows <= 5) {
        $messages[] = "Row $totalRows ID: '$id' (length: " . strlen($id) . ")";
    }

    // Skip rows without ID
    if (empty($id)) {
        $messages[] = "Skipped row $totalRows: no ID";
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
            'position' => $position,
            'department' => $department
        ];
        $messages[] = "Updating existing record ID: $id";

        try {
            $affected = $db->update($record);
            if ($affected > 0) {
                $importedCount++;
                $messages[] = "Record with ID $id updated.";
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
            'position' => $position,
            'department' => $department
        ];
        $messages[] = "Creating new record ID: $id, Number: $number";

        try {
            $affected = $db->insert($record);
            if ($affected > 0) {
                $importedCount++;
                $messages[] = "Record with ID $id added. Number: $number";
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
$message = "CSV import completed. Total rows processed: $totalRows. Records added/updated: $importedCount.";
if (count($errors) > 0) {
    $message .= " Errors: " . implode('; ', $errors);
}

echo json_encode([
    'status' => $status,
    'message' => $message,
    'totalRows' => $totalRows,
    'importedCount' => $importedCount,
    'messages' => $messages,
    'errors' => $errors
]);
?>
