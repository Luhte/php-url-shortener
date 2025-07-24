# Simple PHP & MySQL Link Shortener

A lightweight, self-hosted, and easy-to-deploy URL shortener built using PHP for backend logic and MySQL for data storage, complemented by a JavaScript-powered frontend for link management. This project offers a robust solution for creating custom short URLs, tracking their usage statistics, and managing them through a secure, password-protected administrative interface. It's designed for simplicity and efficiency, making it ideal for personal use or small projects.

## Table of Contents

* [Features](#features)
* [Prerequisites](#prerequisites)
* [Setup Guide](#setup-guide)
    * [1. Database Setup](#1-database-setup)
    * [2. Generate Management Password Hash](#2-generate-management-password-hash)
    * [3. Configure `config.php`](#3-configure-configphp)
    * [4. Upload Files](#4-upload-files)
    * [5. Configure `.htaccess` for Clean URLs](#5-configure-htaccess-for-clean-urls)
* [Usage](#usage)
* [Management Panel](#management-panel)
* [Security Considerations](#security-considerations)
* [Contributing](#contributing)
* [License](#license)

## Features

* **Custom Short Codes:** Provides the flexibility to define your own memorable short URLs (e.g., `yourdomain.com/my-awesome-link`) instead of relying on random ones. This is great for branding or specific campaigns.
* **Random Short Codes:** If a custom code isn't provided, the system automatically generates a unique, alphanumeric short code (e.g., `yourdomain.com/XyZ7pQ`) to ensure no conflicts and quick link creation.
* **Hit Counter:** Each short link includes a built-in counter that increments every time the link is accessed, offering basic analytics on link popularity and traffic.
* **Admin Management Panel:** A dedicated web interface (`/manage`) allows administrators to easily view, add, modify, and remove short links through a user-friendly table.
* **Password Protection:** The entire management panel is secured with a single, configurable password, preventing unauthorized access to your link data and management tools.
* **Clean URLs:** Utilizes `.htaccess` for Apache servers to enable aesthetically pleasing and user-friendly short URLs (e.g., `yourdomain.com/shortcode`) without requiring the `?code=` query string in the browser.
* **Lightweight & Portable:** Built with minimal external dependencies, making it easy to deploy on virtually any standard web hosting environment that supports PHP and MySQL.

## Prerequisites

Before you begin the setup, ensure your hosting environment meets the following requirements:

* **Web Server:** An Apache web server is assumed due to the `.htaccess` configuration. If you are using Nginx or another server, you will need to translate the `.htaccess` rules to your server's equivalent configuration.
* **PHP:** PHP version 7.4 or higher is required. Ensure your hosting environment supports this version and that common extensions like `mysqli` (for MySQL database interaction) are enabled.
* **MySQL Database:** Access to a MySQL database server is necessary. This includes the ability to create databases, users, and grant privileges.
* **Web Hosting:** Any standard web hosting provider that offers PHP, MySQL, and supports `.htaccess` files (for Apache) will be compatible.

## Setup Guide

Follow these detailed steps to get your link shortener up and running on your web hosting account.

### 1. Database Setup

This step involves creating the database and the necessary table for your short links.

1.  **Log in to your hosting control panel:** This is usually cPanel, Plesk, or a custom panel provided by your host.
2.  **Navigate to your database management section:** Look for "MySQL Databases," "Databases Wizard," or a similar option.
3.  **Create a new database and user:**
    * **Database Name:** Choose a unique and descriptive name (e.g., `linkshortener_db`, `yourdomain_short`).
    * **Database Username:** Create a new user specifically for this database (e.g., `linkuser`, `yourdomain_user`).
    * **Password:** Generate a **strong, unique password** for this database user.
4.  **Grant All Privileges:** Ensure the newly created user has all privileges on the newly created database. Your hosting panel usually has a checkbox or a separate step for this.
5.  **Note down credentials:** **Crucially, record** your **Database Name**, **Database Username**, **Password**, and **Database Host**. The database host is often `localhost` if your PHP application and database are on the same server, but some hosts provide a specific hostname (e.g., `mysql.yourhost.com`).
6.  **Access your database management tool:** Typically phpMyAdmin, which can be accessed from your hosting control panel.
7.  **Select your database:** In phpMyAdmin, select the database you just created from the left sidebar.
8.  **Execute SQL query:** Go to the **SQL** tab and paste the following query to create the `short_links` table. This table will store your short codes, original URLs, and hit counts.

    ```sql
    CREATE TABLE IF NOT EXISTS short_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        short_code VARCHAR(10) UNIQUE NOT NULL,
        long_url TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        hits INT DEFAULT 0
    );
    ```
    Click "Go" or "Execute" to run the query.

### 2. Generate Management Password Hash

For enhanced security, the management panel password is not stored in plain text. Instead, a one-way hash of your password is used.

1.  **Create a temporary PHP file:** In your web server's public web directory (e.g., `public_html`, `www`, `htdocs`), create a new file named `generate_hash.php`.
2.  **Add content to `generate_hash.php`:** Open `generate_hash.php` and add only the following PHP code:

    ```php
    <?php
    echo password_hash('YOUR_DESIRED_STRONG_PASSWORD_HERE', PASSWORD_DEFAULT);
    ?>
    ```
    **IMPORTANT:** Replace `'YOUR_DESIRED_STRONG_PASSWORD_HERE'` with the actual strong password you intend to use for accessing the management panel.
3.  **Upload and run:** Save `generate_hash.php` and upload it to your server. Then, open your web browser and navigate to its URL (e.g., `http://yourdomain.com/generate_hash.php`).
4.  **Copy the hash:** A long string of characters (your password hash) will be displayed. **Copy this entire string.**
5.  **Delete the temporary file:** For security, **immediately delete `generate_hash.php` from your server** after copying the hash. Do not leave it accessible.

### 3. Configure `config.php`

This file holds your database connection details and the generated password hash.

1.  Create a file named `config.php` in your project's root directory.
2.  Add the following content to `config.php`. **Carefully replace all placeholder values** with the actual credentials you noted in Step 1 and the password hash you generated in Step 2.

    ```php
    <?php
    // config.php - Database connection and management password configuration

    // --- Database Configuration ---
    // DB_HOST: Your database server hostname or IP. Often 'localhost'.
    define('DB_HOST', 'localhost');
    // DB_USER: The username for your database.
    define('DB_USER', 'your_db_username');
    // DB_PASS: The password for your database user.
    define('DB_PASS', 'your_db_password');
    // DB_NAME: The name of the database you created.
    define('DB_NAME', 'your_db_name');

    // --- Management Password Hash Configuration ---
    // MANAGEMENT_PASSWORD_HASH: Paste the hash generated in Step 2 here.
    define('MANAGEMENT_PASSWORD_HASH', '$2y$10$YourGeneratedPasswordHashGoesHere');

    // --- Base URL for Shortened Links ---
    // BASE_URL: The root URL of your link shortener installation.
    // IMPORTANT: Ensure it includes the trailing slash!
    // Example: '[http://yourdomain.com/](http://yourdomain.com/)' or '[http://yourdomain.com/shortener/](http://yourdomain.com/shortener/)'
    define('BASE_URL', '[http://yourdomain.com/](http://yourdomain.com/)');

    // --- Establish Database Connection ---
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection and log errors for debugging
    if ($conn->connect_error) {
        error_log("Database Connection failed: " . $conn->connect_error);
        // Display a generic error message to the user to avoid exposing sensitive details
        die("Connection failed. Please check server logs for details.");
    }

    // Set character set to UTF-8 for proper handling of various characters
    $conn->set_charset("utf8mb4");
    ?>
    ```

### 4. Upload Files

Upload all the core application files to your public web directory on your hosting server. This is typically `public_html`, `www`, or `htdocs`. You can use an FTP client (like FileZilla) or your hosting provider's File Manager.

* `config.php` (configured in Step 3)
* `index.php` (handles redirection and the main landing page)
* `api.php` (the backend API for all link management operations)
* `manage.html` (the frontend HTML for the management panel)

### 5. Configure `.htaccess` for Clean URLs

This file is crucial for making your short links and the management panel accessible with clean, user-friendly URLs (e.g., `/shortcode` instead of `/index.php?code=shortcode`, and `/manage` instead of `/manage.html`).

1.  Create a file named `.htaccess` (note the leading dot) in the **same public web directory** where you uploaded your PHP and HTML files.
2.  Add the following content to your `.htaccess` file:

    ```apacheconf
    # .htaccess - URL Rewriting for Simple PHP & MySQL Link Shortener

    # Turn on the Rewrite Engine
    RewriteEngine On

    # Set the base directory for rewriting (usually / for the root of your domain)
    RewriteBase /

    # Rule to rewrite /manage to manage.html
    # This allows users to access the management panel at [http://yourdomain.com/manage](http://yourdomain.com/manage)
    RewriteRule ^manage$ manage.html [L]

    # Rule to handle all other requests that are not existing files or directories.
    # This is for the short links.
    # If the requested URI is NOT an existing file (-f)
    RewriteCond %{REQUEST_FILENAME} !-f
    # AND NOT an existing directory (-d)
    RewriteCond %{REQUEST_FILENAME} !-d
    # Then rewrite the request to index.php and pass the original URI as a 'code' parameter.
    # The [L] flag means "Last rule" (stop processing further rules if this one matches).
    # The [QSA] flag means "Query String Append" (append any existing query string to the rewritten URL).
    RewriteRule ^(.*)$ index.php?code=$1 [L,QSA]

    # Optional: Uncomment and adjust these lines if you want to force all traffic to HTTPS.
    # This is highly recommended for security.
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    ```

## Usage

Once setup is complete:

* **Accessing Short Links:** After creating a short link (e.g., with short code `my-product`), you can access the original long URL by simply visiting `http://yourdomain.com/my-product`. The system will automatically redirect you.
* **Main Page:** Visiting the root of your domain (e.g., `http://yourdomain.com/`) will display a simple welcome page with a direct link to the management panel.

## Management Panel

The management panel provides a centralized interface for controlling your short links.

* **Access:** Navigate to `http://yourdomain.com/manage`. You will be prompted to enter the management password you configured in `config.php`.
* **Functionality:**
    * **Add New Short Links:** Use the "Add New Short Link" section to create new entries. You can specify a custom short code or leave it blank for an auto-generated one.
    * **View Links:** A table displays all your short links, their corresponding long URLs, the number of times they've been hit, and their creation date.
    * **Edit Links:** Click the "Edit" button next to a link to modify its long URL.
    * **Delete Links:** Click the "Delete" button to permanently remove a short link. A confirmation prompt will appear to prevent accidental deletions.
* **Logout:** A "Logout" button is available to end your administrative session.

## Security Considerations

Implementing proper security measures is paramount for any web application.

* **HTTPS (SSL/TLS):** This is the **MOST CRITICAL** security measure. **You must enable and enforce HTTPS on your domain.** HTTPS encrypts all data transmitted between your users' browsers and your server, including your management password during login. Most hosting providers offer free SSL certificates (e.g., Let's Encrypt). Configure your web server to redirect all HTTP traffic to HTTPS.
* **Strong Passwords:** Always use strong, unique, and complex passwords for your database user and your management panel. Avoid common words or easily guessable combinations.
* **Error Reporting in Production:** In `api.php`, error reporting is temporarily enabled (`error_reporting(E_ALL); ini_set('display_errors', 1);`) for debugging during setup. **It is absolutely essential to remove or comment out these lines once your application is stable and deployed in a production environment** to prevent sensitive information from being exposed to users.
* **Input Validation & Sanitization:** While basic validation is in place, for a robust production system, consider more comprehensive server-side input validation and sanitization (e.g., using `filter_var` with more flags, or prepared statements for all database interactions) to prevent SQL injection, XSS (Cross-Site Scripting), and other vulnerabilities.
* **CSRF Protection:** For the management interface, consider implementing CSRF (Cross-Site Request Forgery) tokens. This helps protect against malicious websites tricking authenticated users into performing unwanted actions.
* **Rate Limiting:** To prevent abuse of the link shortening functionality (e.g., spamming, brute-force attacks on custom codes), consider implementing server-side rate limiting on the `shorten` action in `api.php`.
* **File Permissions:** Ensure your PHP files have appropriate file permissions on the server (e.g., `644` for files, `755` for directories) to prevent unauthorized access or modification.

## Contributing

Contributions are welcome! If you find bugs, have feature requests, or want to improve the code, feel free to:

1.  Fork the repository.
2.  Create a new branch (`git checkout -b feature/your-feature-name`).
3.  Make your changes.
4.  Commit your changes (`git commit -m 'Add new feature'`).
5.  Push to the branch (`git push origin feature/your-feature-name`).
6.  Open a Pull Request.

## License

This project is open-source and available under the [MIT License](LICENSE).
