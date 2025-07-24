<?php
// config.php - Database connection and management password configuration

// --- Database Configuration ---
// Replace with your MySQL database details
define('DB_HOST', 'localhost'); // Database host (e.g., 'localhost' or a specific IP/hostname)
define('DB_USER', 'your_db_username'); // Your database username
define('DB_PASS', 'your_db_password'); // Your database password
define('DB_NAME', 'your_db_name');     // Your database name

// --- Management Password Hash Configuration ---
// IMPORTANT: Generate a hash for your desired management password using password_hash().
// You can run a temporary PHP file with `echo password_hash('YOUR_DESIRED_STRONG_PASSWORD_HERE', PASSWORD_DEFAULT);`
// on your server to get the hash. Replace this placeholder with the actual hash.
define('MANAGEMENT_PASSWORD_HASH', '$2y$10$your_generated_password_hash'); // PASTE YOUR GENERATED HASH HERE

// --- Base URL for Shortened Links ---
// This should be the base URL of your link shortener installation.
// IMPORTANT: Ensure it includes the trailing slash!
// Example: 'http://yourdomain.com/' or 'http://yourdomain.com/shortener/'
define('BASE_URL', 'http://yourdomain.com/');

// --- Feature Toggle: Allow Public Link Shortening ---
// Set to 'true' to allow anyone to create short links from the homepage (index.php).
// Set to 'false' to restrict link creation to the authenticated management panel only.
define('ALLOW_PUBLIC_SHORTENING', false); // Default: OFF

// --- Establish Database Connection ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection and log errors for debugging
if ($conn->connect_error) {
    error_log("Database Connection failed: " . $conn->connect_error);
    // Display a generic error message to the user to avoid exposing sensitive details
    die("Connection failed. Please check server logs for details. (Error Code: DB_CONN_FAIL)");
}

// Set character set to UTF-8 for proper handling of various characters
$conn->set_charset("utf8mb4");
?>
