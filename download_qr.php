<?php
// download_qr.php
// Fetch record by ID and generate QR code for download

if (!isset($_GET['id'])) {
    die("No ID provided");
}

// Load database
require_once 'config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

$id = $_GET['id'];
$record = $db->getById($id);

if (!$record) {
    die("Record not found");
}

// Prepare URL data for QR code
$url = "$base_url/preview.php?id=" . urlencode($record['id']);
// 'yourdomain.com/record.php' should point to your record display URL

// Generate the QR code URL
$encodedUrl = urlencode($url);
$qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?data=$encodedUrl&size=200x200";

// Download the QR code image content
$imageContent = file_get_contents($qrApiUrl);

if ($imageContent === false) {
    die("Failed to generate QR code");
}

// Serve as download
$filename = "QRCode_".sprintf('%03d', $record['number']).".png";

// Set headers to trigger download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($imageContent));

// Output the image content
echo $imageContent;
exit;
?>
