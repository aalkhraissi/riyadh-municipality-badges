<?php
// export_csv.php - Export database records to CSV

mb_internal_encoding('UTF-8');

require_once 'config.php';
require_once 'db.php';

// Connect to database
try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get all records
$records = $db->getAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=employees.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel compatibility with Arabic characters
fwrite($output, "\xEF\xBB\xBF");

// Output the column headings
fputcsv($output, ['id', 'number', 'name', 'email', 'position']);

// Loop through the records and output them
foreach ($records as $record) {
    // Ensure all text is properly encoded as UTF-8
    $row = [
        $record['id'],
        $record['number'],
        $record['name'],
        $record['email'],
        $record['position']
    ];
    fputcsv($output, $row);
}

// Close the file pointer
fclose($output);
exit;
?>