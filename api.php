<?php
// api.php - Backend API for managing short links (create, list, update, delete)
// Includes password protection for management actions.

// Temporarily enable error reporting for debugging 500 Internal Server Error
// REMOVE THESE LINES IN PRODUCTION FOR SECURITY
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php'; // Include database configuration

header('Content-Type: application/json'); // Set header for JSON response

// Start session for authentication
session_start();

// Function to generate a random short code
function generateShortCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// --- Authentication Function ---
function authenticate($password) {
    // Verify the provided password against the stored hashed password
    // MANAGEMENT_PASSWORD_HASH is defined in config.php
    return password_verify($password, MANAGEMENT_PASSWORD_HASH);
}

// --- Check if the request is authenticated for management actions ---
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

// Get the request method and input data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? null;

// Handle authentication requests
if ($action === 'login') {
    $password = $input['password'] ?? '';
    if (authenticate($password)) {
        $_SESSION['authenticated'] = true;
        echo json_encode(['success' => true, 'message' => 'Logged in successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    }
    $conn->close();
    exit();
}

// All other actions require authentication (except 'shorten' which is public)
if ($action !== 'shorten' && !isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    $conn->close();
    exit();
}

// --- Handle API Actions ---
switch ($action) {
    case 'shorten':
        $longUrl = $input['long_url'] ?? '';
        $customCode = $input['custom_code'] ?? '';

        // Basic URL validation
        if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid URL provided.']);
            break;
        }

        // Sanitize and validate custom code
        if (!empty($customCode)) {
            // Allow only alphanumeric characters and hyphens for custom codes
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $customCode)) {
                echo json_encode(['success' => false, 'message' => 'Custom code can only contain letters, numbers, hyphens, and underscores.']);
                break;
            }
            // Check if custom code already exists
            $stmt = $conn->prepare("SELECT id FROM short_links WHERE short_code = ?");
            if ($stmt === false) {
                error_log("API: Shorten - Prepare (custom code check) failed: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database error during custom code check.']);
                break;
            }
            $stmt->bind_param("s", $customCode);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Custom code already in use. Please choose another.']);
                $stmt->close();
                break;
            }
            $stmt->close();
            $shortCode = $customCode;
        } else {
            // Generate a unique short code
            do {
                $shortCode = generateShortCode();
                $stmt = $conn->prepare("SELECT id FROM short_links WHERE short_code = ?");
                if ($stmt === false) {
                    error_log("API: Shorten - Prepare (generate short code) failed: " . $conn->error);
                    echo json_encode(['success' => false, 'message' => 'Database error during short code generation.']);
                    break 2; // Break out of do-while and switch
                }
                $stmt->bind_param("s", $shortCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
            } while ($result->num_rows > 0);
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO short_links (short_code, long_url) VALUES (?, ?)");
        if ($stmt === false) {
            error_log("API: Shorten - Prepare (insert) failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error during link insertion.']);
            break;
        }
        $stmt->bind_param("ss", $shortCode, $longUrl);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'short_code' => $shortCode]);
        } else {
            error_log("API: Shorten - Execute (insert) failed: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Failed to shorten URL. Database error.']);
        }
        $stmt->close();
        break;

    case 'list':
        $stmt = $conn->prepare("SELECT id, short_code, long_url, created_at, hits FROM short_links ORDER BY created_at DESC");
        if ($stmt === false) {
            error_log("API: List - Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error during list retrieval.']);
            break;
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $links = [];
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
        echo json_encode(['success' => true, 'links' => $links]);
        $stmt->close();
        break;

    case 'update':
        $id = $input['id'] ?? 0;
        $longUrl = $input['long_url'] ?? '';

        if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid URL provided for update.']);
            break;
        }

        $stmt = $conn->prepare("UPDATE short_links SET long_url = ? WHERE id = ?");
        if ($stmt === false) {
            error_log("API: Update - Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error during update preparation.']);
            break;
        }
        $stmt->bind_param("si", $longUrl, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Link updated successfully.']);
        } else {
            error_log("API: Update - Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Failed to update link.']);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $input['id'] ?? 0;

        $stmt = $conn->prepare("DELETE FROM short_links WHERE id = ?");
        if ($stmt === false) {
            error_log("API: Delete - Prepare failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error during delete preparation.']);
            break;
        }
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Link deleted successfully.']);
        } else {
            error_log("API: Delete - Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Failed to delete link.']);
        }
        $stmt->close();
        break;

    case 'logout':
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

$conn->close(); // Close database connection

?>
