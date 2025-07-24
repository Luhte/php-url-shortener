Simple PHP & MySQL Link Shortener
A lightweight and self-hosted URL shortener built with PHP, MySQL, and a JavaScript-powered management interface. This project allows you to create custom short links and track their usage, with an admin panel protected by a password.
Table of Contents
Features
Prerequisites
Setup Guide
1. Database Setup
2. Generate Management Password Hash
3. Configure config.php
4. Upload Files
5. Configure .htaccess for Clean URLs
Usage
Management Panel
Security Considerations
Contributing
License
Features
Custom Short Codes: Create personalized short URLs (e.g., yourdomain.com/my-awesome-link).
Random Short Codes: Automatically generate unique short codes if a custom one isn't provided.
Hit Counter: Track how many times each short link has been accessed.
Admin Management Panel: A web interface to view, add, edit, and delete short links.
Password Protection: Secure the management panel with a password.
Clean URLs: Short links are displayed without ?code= thanks to .htaccess URL rewriting.
Lightweight: Minimal dependencies, easy to deploy on standard PHP/MySQL hosting.
Prerequisites
Web server (e.g., Apache) with PHP (7.4+)
MySQL database
Standard web hosting with PHP/MySQL and .htaccess support
Setup Guide
Follow these steps to get your link shortener up and running on your web hosting account.
1. Database Setup
Log in to your hosting control panel (e.g., cPanel, Plesk, or equivalent).
Navigate to your database management section (e.g., MySQL Databases, Databases Wizard).
Create a new database:
Database Name: (e.g., linkshortener_db)
Database Username: (e.g., linkuser)
Password: Generate a strong password.
Note down your Database Name, Username, Password, and Database Host (often localhost for internal connections).
Access your database management tool (e.g., phpMyAdmin), select your newly created database.
Go to the SQL tab and execute the following query to create the short_links table:
CREATE TABLE IF NOT EXISTS short_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_code VARCHAR(10) UNIQUE NOT NULL,
    long_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hits INT DEFAULT 0
);


2. Generate Management Password Hash
For security, the management password is stored as a hash.
Create a temporary PHP file (e.g., generate_hash.php) in your web server's public web directory (e.g., public_html).
Add the following content to generate_hash.php:
<?php
echo password_hash('YOUR_DESIRED_STRONG_PASSWORD_HERE', PASSWORD_DEFAULT);
?>

Replace YOUR_DESIRED_STRONG_PASSWORD_HERE with the actual strong password you want to use.
Save and upload generate_hash.php.
Open this file in your web browser (e.g., http://yourdomain.com/generate_hash.php).
Copy the entire hashed string that is displayed.
Immediately delete generate_hash.php from your server.
3. Configure config.php
Create a file named config.php and add the following content. Remember to replace all placeholder values with your actual database credentials, the password hash you generated, and your website's base URL.
<?php
// config.php - Database connection and management password configuration

// --- Database Configuration ---
define('DB_HOST', 'localhost'); // Database host (e.g., 'localhost' or a specific IP/hostname)
define('DB_USER', 'your_db_username'); // Your database username
define('DB_PASS', 'your_db_password'); // Your database password
define('DB_NAME', 'your_db_name');     // Your database name

// --- Management Password Hash Configuration ---
// PASTE YOUR GENERATED HASH HERE from Step 2
define('MANAGEMENT_PASSWORD_HASH', '$2y$10$YourGeneratedPasswordHashGoesHere');

// --- Base URL for Shortened Links ---
// IMPORTANT: Include trailing slash!
define('BASE_URL', 'http://yourdomain.com/');

// --- Establish Database Connection ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check server logs for details.");
}

$conn->set_charset("utf8mb4");
?>


4. Upload Files
Upload the following files to your public web directory (e.g., public_html, www, or htdocs):
config.php (configured in Step 3)
index.php (handles redirection and main landing page)
api.php (backend for link management)
manage.html (frontend for management panel)
5. Configure .htaccess for Clean URLs
Create a file named .htaccess (note the leading dot) in your public web directory and add the following content:
# .htaccess - URL Rewriting for Link Shortener

# Turn on the Rewrite Engine
RewriteEngine On

# Set the base directory for rewriting (usually / for root)
RewriteBase /

# Rule to rewrite /manage to manage.html
RewriteRule ^manage$ manage.html [L]

# Rule to handle requests for non-existent files or directories
# If the requested URI is NOT a file (-f) AND NOT a directory (-d),
# then rewrite the request to index.php and pass the original URI as 'code' parameter.
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?code=$1 [L,QSA]

# Optional: If you want to force HTTPS, uncomment and adjust these lines
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]


Usage
Accessing Short Links: Once you create a short link (e.g., abc), you can access the original long URL by visiting http://yourdomain.com/abc.
Main Page: Visiting http://yourdomain.com/ will show a simple welcome page with a link to the management panel.
Management Panel
Access the management panel by navigating to http://yourdomain.com/manage. You will be prompted to enter your management password.
From the management panel, you can:
Add new short links (with optional custom codes).
View all existing short links, their long URLs, hit counts, and creation dates.
Edit the long URL of existing short links.
Delete short links.
Security Considerations
HTTPS (SSL/TLS): It is CRITICAL to enable and enforce HTTPS on your domain. This encrypts all communication, including your management password during login. Most hosting providers offer free SSL certificates (e.g., Let's Encrypt).
Strong Passwords: Always use strong, unique passwords for your database and management panel.
Error Reporting: In api.php, error reporting is temporarily enabled for debugging. Remember to remove or comment out error_reporting(E_ALL); and ini_set('display_errors', 1); in api.php once your application is stable in production to prevent sensitive information from being exposed to users.
Input Validation: While basic validation is in place, for a production system, consider more comprehensive server-side input validation and sanitization.
CSRF Protection: For the management interface, consider implementing CSRF (Cross-Site Request Forgery) tokens to protect against malicious requests.
Rate Limiting: To prevent abuse, especially for the link shortening functionality, consider implementing rate limiting.
Contributing
Feel free to fork this repository, open issues, or submit pull requests to improve this simple link shortener.
License
This project is open-source and available under the MIT License.
