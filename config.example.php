<?php
// ============================================================================
// PRODUCTION DATABASE CONFIGURATION
// ============================================================================
// IMPORTANT: Fill in these values from your heberjahiz.com cPanel
// Go to cPanel > MySQL Databases to get these values
// ============================================================================

define('DB_HOST', 'localhost');  // Usually 'localhost' - DO NOT CHANGE unless heberjahiz specifies otherwise
define('DB_USER', 'YOUR_CPANEL_USERNAME_dbuser');  // CHANGE THIS - From cPanel MySQL Databases
define('DB_PASS', 'YOUR_DATABASE_PASSWORD');        // CHANGE THIS - The password you set in cPanel
define('DB_NAME', 'YOUR_CPANEL_USERNAME_groupeseb');  // CHANGE THIS - From cPanel MySQL Databases

// ============================================================================
// DATABASE CONNECTION FUNCTION
// ============================================================================
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            // Don't reveal database details in production
            error_log("Database connection error: " . $conn->connect_error);
            throw new Exception("Service temporarily unavailable. Please try again later.");
        }

        $conn->set_charset("utf8mb4");
        return $conn;

    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Service temporarily unavailable. Please try again later.");
    }
}

// ============================================================================
// FILE UPLOAD CONFIGURATION
// ============================================================================
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB maximum file size
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG']);

// ============================================================================
// CREATE UPLOADS DIRECTORY IF IT DOESN'T EXIST
// ============================================================================
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ============================================================================
// EMAIL NOTIFICATION CONFIGURATION
// ============================================================================
// Admin email - will receive notifications when forms are submitted
define('ADMIN_EMAIL', 'your-email@example.com');  // CHANGE THIS to your personal email (Gmail, Outlook, etc.)

// Email settings - Create noreply@tefal-moulinex.com in cPanel > Email Accounts first!
define('EMAIL_FROM', 'noreply@tefal-moulinex.com');   // Sender email (create in cPanel > Email Accounts)
define('EMAIL_FROM_NAME', 'Groupe SEB Notifications');
define('ENABLE_EMAIL_NOTIFICATIONS', true);        // Set to false to disable email notifications
define('WEBSITE_URL', 'https://tefal-moulinex.com');  // Your website URL (used for receipt links)

// ============================================================================
// SECURITY SETTINGS
// ============================================================================
// Disable error display in production (errors will be logged instead)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Only if using HTTPS
ini_set('session.use_strict_mode', 1);
?>
