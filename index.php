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
            background-color: #ecf8f4; /* A softer green background */
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(36, 37, 38, 0.1);
            width: 350px;
            text-align: center;
        }
        .container form {
            background: #f9fff9; /* Very light green background for the form */
            padding: 15px;
            border: 1px solid #c3e6cb; /* Soft green border */
            border-radius: 5px;
        }
        input[type="text"], input[type="submit"], button {
            width: calc(100% - 24px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #c3e6cb; /* Soft green border */
            border-radius: 5px;
        }
        input[type="submit"], button {
            background-color: #28a745; /* Bootstrap green */
            color: white;
            cursor: pointer;
            font-size: 16px;
            border: none;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #218838; /* Darker green */
        }
        a, a:visited {
            color: #155724; /* Dark green */
            text-decoration: none;
        }
        p {
            color: #155724; /* Dark green */
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($output)): ?>
            <p><?= $output; ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="longUrl" style="color: #155724;">URL:</label>
            <input type="text" name="longUrl" id="longUrl" placeholder="Enter a valid HTTP or HTTPS URL" required>
            <input type="submit" value="Shorten">
        </form>
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
