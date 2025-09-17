<?php
// export_csv.php - Export database records to CSV

mb_internal_encoding('UTF-8');

require_once './config/config.php';
require_once 'db.php';

// Connect to database
try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get branch_id from query parameter
$branchId = isset($_GET['branch_id']) ? $_GET['branch_id'] : null;

// Get branch name if branch_id is specified
$branchName = '';
if ($branchId) {
    $branch = $db->getBranchById($branchId);
    if ($branch) {
        $branchName = $branch['name'];
    }
}

// Get records filtered by branch if specified
if ($branchId) {
    $allRecords = $db->getAll();
    $records = array_filter($allRecords, function($record) use ($branchId) {
        return $record['branch_id'] == $branchId;
    });
} else {
    $records = $db->getAll();
}

// Create filename with branch name if available
$filename = 'employees';
if (!empty($branchName)) {
    $filename .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $branchName);
}
$filename .= '.csv';

// Set headers for CSV download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel compatibility with Arabic characters
fwrite($output, "\xEF\xBB\xBF");

// Output the column headings
fputcsv($output, ['id', 'number', 'name', 'general_administration', 'administration', 'department', 'email']);

// Loop through the records and output them
foreach ($records as $record) {
    // Ensure all text is properly encoded as UTF-8
    $row = [
        $record['id'],
        $record['number'],
        $record['name'],
        $record['general_administration'] ?? '',
        $record['administration'],
        $record['department'] ?? '',
        $record['email']
    ];
    fputcsv($output, $row);
}

// Close the file pointer
fclose($output);
exit;
?>