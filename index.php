<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $servername = $_ENV['DB_SERVER'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    $dbname = $_ENV['DB_NAME'];
    $accessCodeEnv = $_ENV['ACCESS_CODE']; // Access code from .env

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $output = '';
    $shortCode = ''; // Initialize the variable to avoid undefined variable notice
    $domain = "http://yourdomain.com"; // Replace with your actual domain

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $longUrl = $_POST["longUrl"];
        $accessCode = $_POST["accessCode"]; // Retrieve access code from form

        // Check if the entered access code matches the one in .env
        if ($accessCode !== $accessCodeEnv) {
            $output = "Unauthorized access. Invalid access code.";
        } else {
            // Validate the URL
            if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
                $output = "Invalid URL. Please enter a valid URL.";
            } else {
                $scheme = parse_url($longUrl, PHP_URL_SCHEME);
                if (!in_array($scheme, ['http', 'https'])) {
                    $output = "Invalid URL scheme. Only HTTP and HTTPS are allowed.";
                } else {
                    $shortCode = substr(md5($longUrl), 0, 6); // Simple short code generation
                    
                    $sql = "INSERT INTO urls (short_code, long_url) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $shortCode, $longUrl);
                    $stmt->execute();

                    $shortUrl = htmlspecialchars("$domain/?code=$shortCode");
                    $output = "Short URL created: <a href='$shortUrl' id='shortUrlLink'>$shortUrl</a>
                               <button onclick='copyToClipboard()'>Copy URL</button>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    $output = $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9; /* Light grey background */
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }
        form {
            background: #fafafa; /* Very light grey for the form background */
            padding: 20px;
            border: 1px solid #ddd; /* Light grey border */
            border-radius: 5px;
            margin-top: 15px;
        }
        label {
            color: #333; /* Dark grey for text */
            font-weight: bold;
            display: block;
            text-align: left;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="submit"], button {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"], button {
            background-color: #007bff; /* Bootstrap blue */
            color: white;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            border: none;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #0056b3; /* Darker blue */
        }
        a, a:visited {
            color: #007bff; /* Link blue */
            text-decoration: none;
        }
        p {
            color: #333; /* Dark grey for text */
            margin-top: 0;
        }
        .footer {
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($output)): ?>
            <p><?= $output; ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="longUrl">URL:</label>
            <input type="text" name="longUrl" id="longUrl" placeholder="Enter a valid HTTP or HTTPS URL" required>
            <label for="accessCode">Access Code:</label>
            <input type="password" name="accessCode" id="accessCode" placeholder="Enter access code" required>
            <input type="submit" value="Shorten">
        </form>
    </div>
    <div class="footer">
        <a href="https://github.com/luhte" target="_blank">Made by Luhte</a>
    </div>
    <script>
        function copyToClipboard() {
            const copyText = document.getElementById('shortUrlLink').href;
            navigator.clipboard.writeText(copyText).then(() => {
                alert('URL copied to clipboard!');
            }).catch(err => {
                alert('Failed to copy URL');
            });
        }
    </script>
</body>
</html>
