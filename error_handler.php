<?php
/**
 * Production Error Handler for Riyadh Municipality Control System
 * Include this file at the top of your PHP files for better error handling
 */

// Enable error reporting for debugging (remove in production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Log the error
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");

    // Don't display errors in production unless specifically enabled
    if (ini_get('display_errors') == 0) {
        return true;
    }

    // Display user-friendly error message
    $errorMessage = "<div style='background: #ffebee; border: 1px solid #f44336; padding: 20px; margin: 20px; border-radius: 5px;'>";
    $errorMessage .= "<h2 style='color: #d32f2f; margin-top: 0;'>⚠️ خطأ في النظام</h2>";
    $errorMessage .= "<p>حدث خطأ فني في النظام. يرجى المحاولة مرة أخرى لاحقاً.</p>";
    $errorMessage .= "<p style='color: #666; font-size: 12px;'>Error ID: " . time() . "</p>";
    $errorMessage .= "</div>";

    echo $errorMessage;
    return true;
}

// Custom exception handler
function customExceptionHandler($exception) {
    // Log the exception
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());

    // Display user-friendly error message
    $errorMessage = "<div style='background: #ffebee; border: 1px solid #f44336; padding: 20px; margin: 20px; border-radius: 5px;'>";
    $errorMessage .= "<h2 style='color: #d32f2f; margin-top: 0;'>⚠️ خطأ في النظام</h2>";
    $errorMessage .= "<p>حدث خطأ فني في النظام. يرجى المحاولة مرة أخرى لاحقاً.</p>";
    $errorMessage .= "<p style='color: #666; font-size: 12px;'>Error ID: " . time() . "</p>";
    $errorMessage .= "</div>";

    echo $errorMessage;
}

// Set custom error and exception handlers
set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");

// Function to check if we're in development mode
function isDevelopment() {
    return (isset($_SERVER['SERVER_NAME']) && (
        strpos($_SERVER['SERVER_NAME'], 'localhost') !== false ||
        strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
        strpos($_SERVER['SERVER_NAME'], '.dev') !== false ||
        strpos($_SERVER['SERVER_NAME'], '.local') !== false
    ));
}

// Enable detailed errors in development
if (isDevelopment()) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}

// Function to handle fatal errors
function handleFatalError() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log the fatal error
        error_log("Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);

        // Display user-friendly error message for fatal errors
        if (!headers_sent()) {
            http_response_code(500);
        }

        $errorMessage = "<!DOCTYPE html><html lang='ar' dir='rtl'><head><meta charset='UTF-8'><title>خطأ في النظام</title></head><body>";
        $errorMessage .= "<div style='background: #ffebee; border: 1px solid #f44336; padding: 20px; margin: 20px; border-radius: 5px; font-family: Arial, sans-serif;'>";
        $errorMessage .= "<h2 style='color: #d32f2f; margin-top: 0;'>⚠️ خطأ في النظام</h2>";
        $errorMessage .= "<p>حدث خطأ فني خطير في النظام. تم تسجيل المشكلة وسنعمل على حلها في أقرب وقت ممكن.</p>";
        $errorMessage .= "<p>يرجى المحاولة مرة أخرى لاحقاً أو الاتصال بمسؤول النظام.</p>";
        $errorMessage .= "<p style='color: #666; font-size: 12px;'>Error ID: " . time() . "</p>";
        $errorMessage .= "<p><a href='index.php' style='color: #1976d2;'>العودة إلى الصفحة الرئيسية</a></p>";
        $errorMessage .= "</div></body></html>";

        echo $errorMessage;
        exit();
    }
}

// Register shutdown function to handle fatal errors
register_shutdown_function('handleFatalError');

// Function to safely include files
function safeInclude($file) {
    try {
        if (file_exists($file)) {
            include_once $file;
            return true;
        } else {
            error_log("File not found: $file");
            return false;
        }
    } catch (Exception $e) {
        error_log("Error including file $file: " . $e->getMessage());
        return false;
    }
}

// Function to check database connection
function checkDatabaseConnection() {
    try {
        if (file_exists('config/config.php')) {
            require_once 'config/config.php';
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_usr, $db_password);
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

// Function to get system status
function getSystemStatus() {
    $status = [
        'php_version' => phpversion(),
        'database' => checkDatabaseConnection(),
        'config_exists' => file_exists('config/config.php'),
        'writable' => is_writable('.'),
        'development' => isDevelopment()
    ];
    return $status;
}
?>