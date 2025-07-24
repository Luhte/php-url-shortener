<?php
// index.php - Handles redirection logic and conditionally displays public shortening form

require_once 'config.php'; // Include database configuration

// Function to generate a random short code (kept for consistency, though not used here for public shortening)
function generateShortCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Handle redirection if a short code is provided in the URL
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $shortCode = $conn->real_escape_string($_GET['code']);

    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT long_url FROM short_links WHERE short_code = ?");
    if ($stmt === false) {
        error_log("index.php: Prepare (select long_url) failed: " . $conn->error);
        // Fallback to 404 page if database statement preparation fails
        header("HTTP/1.0 404 Not Found");
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Error</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <style> body { font-family: 'Inter', sans-serif; } </style>
        </head>
        <body class='bg-gray-100 flex items-center justify-center min-h-screen'>
            <div class='bg-white p-8 rounded-lg shadow-md text-center'>
                <h1 class='text-4xl font-bold text-red-600 mb-4'>Error</h1>
                <p class='text-xl text-gray-800 mb-6'>An internal error occurred. Please try again later.</p>
                <a href='" . BASE_URL . "manage' class='bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300'>Manage Links</a>
            </div>
        </body>
        </html>";
        exit();
    }
    $stmt->bind_param("s", $shortCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $longUrl = $row['long_url'];

        // Update hit counter
        $updateStmt = $conn->prepare("UPDATE short_links SET hits = hits + 1 WHERE short_code = ?");
        if ($updateStmt === false) {
            error_log("index.php: Prepare (update hits) failed: " . $conn->error);
            // Continue with redirection even if hit update fails, but log it.
        } else {
            $updateStmt->bind_param("s", $shortCode);
            $updateStmt->execute();
            $updateStmt->close();
        }

        // Redirect to the long URL
        header("Location: " . $longUrl);
        exit();
    } else {
        // Short code not found, display a 404 message or redirect to homepage
        header("HTTP/1.0 404 Not Found");
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Link Not Found</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <style>
                body { font-family: 'Inter', sans-serif; }
            </style>
        </head>
        <body class='bg-gray-100 flex items-center justify-center min-h-screen'>
            <div class='bg-white p-8 rounded-lg shadow-md text-center'>
                <h1 class='text-4xl font-bold text-red-600 mb-4'>404</h1>
                <p class='text-xl text-gray-800 mb-6'>Oops! The short link you are looking for does not exist.</p>
                <a href='" . BASE_URL . "manage' class='bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300'>Manage Links</a>
            </div>
        </body>
        </html>";
        exit();
    }
    $stmt->close();
}

// If no short code is provided, display the main landing page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Shortener Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-300 hover:scale-105">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Welcome to the Link Shortener</h1>

        <?php if (ALLOW_PUBLIC_SHORTENING): ?>
            <p class="text-gray-700 mb-4 text-center">Shorten your URLs quickly below:</p>
            <form id="shortenForm" class="space-y-4">
                <div>
                    <label for="longUrl" class="block text-gray-700 text-sm font-semibold mb-2">Long URL:</label>
                    <input type="url" id="longUrl" name="longUrl" placeholder="e.g., https://example.com/very/long/url" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                </div>
                <div>
                    <label for="customCode" class="block text-gray-700 text-sm font-semibold mb-2">Custom Short Code (Optional):</label>
                    <input type="text" id="customCode" name="customCode" placeholder="e.g., mylink"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for a random code.</p>
                </div>
                <button type="submit"
                        class="w-full bg-gradient-to-r from-green-500 to-teal-600 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:from-green-600 hover:to-teal-700 transition duration-300 ease-in-out transform hover:-translate-y-1">
                    Shorten!
                </button>
            </form>

            <div id="result" class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg text-center hidden">
                <p class="text-gray-700 font-medium mb-2">Your Shortened URL:</p>
                <a id="shortUrlOutput" href="#" target="_blank" class="text-blue-600 hover:underline text-lg font-bold break-all"></a>
                <button id="copyButton" class="mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-300">
                    Copy to Clipboard
                </button>
            </div>

            <div id="messageBox" class="mt-6 p-4 rounded-lg text-center hidden" role="alert"></div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const shortenForm = document.getElementById('shortenForm');
                    const longUrlInput = document.getElementById('longUrl');
                    const customCodeInput = document.getElementById('customCode');
                    const resultDiv = document.getElementById('result');
                    const shortUrlOutput = document.getElementById('shortUrlOutput');
                    const copyButton = document.getElementById('copyButton');
                    const messageBox = document.getElementById('messageBox');

                    const BASE_URL = "<?php echo BASE_URL; ?>";

                    shortenForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        showMessage('', 'hidden'); // Clear previous messages

                        const longUrl = longUrlInput.value.trim();
                        const customCode = customCodeInput.value.trim();

                        if (!longUrl) {
                            showMessage('Please enter a long URL.', 'bg-red-100 text-red-700');
                            return;
                        }

                        try {
                            const response = await fetch('api.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    action: 'shorten',
                                    long_url: longUrl,
                                    custom_code: customCode
                                }),
                            });

                            const data = await response.json();

                            if (data.success) {
                                const shortUrl = BASE_URL + data.short_code;
                                shortUrlOutput.textContent = shortUrl;
                                shortUrlOutput.href = shortUrl;
                                resultDiv.classList.remove('hidden');
                                showMessage('Link shortened successfully!', 'bg-green-100 text-green-700');
                                longUrlInput.value = ''; // Clear input
                                customCodeInput.value = ''; // Clear input
                            } else {
                                showMessage(`Error: ${data.message}`, 'bg-red-100 text-red-700');
                                resultDiv.classList.add('hidden');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            showMessage('An unexpected error occurred. Please try again.', 'bg-red-100 text-red-700');
                            resultDiv.classList.add('hidden');
                        }
                    });

                    copyButton.addEventListener('click', () => {
                        const textToCopy = shortUrlOutput.textContent;
                        const textArea = document.createElement("textarea");
                        textArea.value = textToCopy;
                        textArea.style.position = "fixed";
                        textArea.style.left = "-9999px";
                        textArea.style.top = "0";
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        try {
                            const successful = document.execCommand('copy');
                            if (successful) {
                                showMessage('Copied to clipboard!', 'bg-blue-100 text-blue-700');
                            } else {
                                showMessage('Failed to copy. Please copy manually.', 'bg-red-100 text-red-700');
                            }
                        } catch (err) {
                            console.error('Copy command failed:', err);
                            showMessage('Failed to copy. Please copy manually.', 'bg-red-100 text-red-700');
                        }
                        document.body.removeChild(textArea);
                    });

                    function showMessage(message, classes) {
                        messageBox.textContent = message;
                        messageBox.className = `mt-6 p-4 rounded-lg text-center ${classes}`;
                        messageBox.classList.remove('hidden');
                        setTimeout(() => {
                            messageBox.classList.add('hidden');
                        }, 5000);
                    }
                });
            </script>
        <?php else: ?>
            <p class="text-gray-700 mb-8 text-center">Link creation is currently restricted. Please log in to the management panel to create and manage short links.</p>
        <?php endif; ?>

        <div class="mt-6 text-center">
            <a href="manage" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1 inline-block">
                Manage Your Short Links
            </a>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); // Close database connection ?>
