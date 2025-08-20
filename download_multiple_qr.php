<?php
// download_multiple_qr.php
// Generate and download multiple QR codes in a zip file

// Check if IDs are provided
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    die("No IDs provided");
}

// Check if ZipArchive class is available
if (!class_exists('ZipArchive')) {
    die("ZipArchive class is not available on this server");
}

// Load database
require_once './config/config.php';
require_once 'db.php';

try {
    $db = new Database($db_host, $db_name, $db_usr, $db_password);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get IDs from query parameter
$ids = explode(',', $_GET['ids']);

// Create a temporary directory for QR codes within the project folder
$tempDir = __DIR__ . '/temp_qr_codes_' . uniqid();
if (!mkdir($tempDir, 0777, true)) {
    die("Failed to create temporary directory");
}

// Array to store QR code file paths
$qrFiles = [];

// Generate QR codes for each ID
foreach ($ids as $id) {
    $record = $db->getById($id);
    
    if ($record) {
        // Prepare URL data for QR code
        $url = "$base_url/preview.php?id=" . urlencode($record['id']);
        
        // Generate the QR code URL
        $encodedUrl = urlencode($url);
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?data=$encodedUrl&size=200x200";
        
        // Download the QR code image content
        $imageContent = @file_get_contents($qrApiUrl);
        
        if ($imageContent !== false) {
            // Save the image to temporary directory
            $filename = "QRCode_" . sprintf('%03d', $record['number']) . ".png";
            $filePath = $tempDir . '/' . $filename;
            
            if (file_put_contents($filePath, $imageContent)) {
                $qrFiles[] = $filePath;
            }
        }
    }
}

// Check if we have any QR codes to download
if (empty($qrFiles)) {
    // Clean up temporary directory
    @array_map('unlink', glob("$tempDir/*"));
    @rmdir($tempDir);
    die("No QR codes were generated");
}

// Create zip file
$zipFileName = "QR_Codes_" . date('Y-m-d_H-i-s') . ".zip";
$zipFilePath = $tempDir . '/' . $zipFileName;

// Create zip archive
$zip = new ZipArchive();
if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
    // Clean up temporary directory
    @array_map('unlink', glob("$tempDir/*"));
    @rmdir($tempDir);
    die("Failed to create zip file");
}

// Add QR code files to zip
foreach ($qrFiles as $filePath) {
    $zip->addFile($filePath, basename($filePath));
}

$zip->close();

// Check if zip file was created successfully
if (!file_exists($zipFilePath)) {
    // Clean up temporary directory
    @array_map('unlink', glob("$tempDir/*"));
    @rmdir($tempDir);
    die("Failed to create zip file");
}

// Serve as download
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zipFilePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zipFilePath));

// Output the zip file content
readfile($zipFilePath);

// Clean up temporary directory and files
@array_map('unlink', glob("$tempDir/*"));
@rmdir($tempDir);

exit;
?>