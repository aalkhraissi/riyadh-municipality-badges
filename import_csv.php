<?php
// import_csv.php - Import CSV to database

ini_set('display_errors', 1);
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

require_once 'config.php';
require_once 'db.php';

// Check upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csvfile'])) {
    die("No file uploaded");
}

if ($_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) {
    die("Upload error: " . $_FILES['csvfile']['error']);
}

$csvPath = $_FILES['csvfile']['tmp_name'];
if (!file_exists($csvPath)) {
    die("CSV file not found");
}

// Connect to database
try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the current maximum number to use for new records
$maxNumber = $db->getMaxNumber();

// Read CSV with UTF-8 support
if (($handle = fopen($csvPath, 'r')) === false) {
    die("Failed to open CSV");
}

$headers = [];
$importedCount = 0;

while (($row = fgetcsv($handle)) !== false) {
    // Convert each field to UTF-8 if needed
    foreach ($row as $key => $value) {
        if (!mb_check_encoding($value, 'UTF-8')) {
            $row[$key] = mb_convert_encoding($value, 'UTF-8');
        }
    }
    
    if (empty($headers)) {
        $headers = $row; // first row as header
        continue;
    }
    $rowData = array_combine($headers, $row);
    $id = $rowData['id'] ?? '';
    $name = $rowData['name'] ?? '';
    $email = $rowData['email'] ?? '';
    $position = $rowData['position'] ?? '';
    
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
            'position' => $position
        ];
        
        try {
            $db->update($record);
            $importedCount++;
            echo "Record with ID $id updated.\n";
        } catch (Exception $e) {
            echo "Error updating record with ID $id: " . $e->getMessage() . "\n";
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
            'position' => $position
        ];
        
        try {
            $db->insert($record);
            $importedCount++;
            echo "Record with ID $id added. Number: $number\n";
        } catch (Exception $e) {
            echo "Error adding record with ID $id: " . $e->getMessage() . "\n";
        }
    }
}
fclose($handle);

echo "CSV import successful. Total records added/updated: $importedCount.";
?>
