<?php
require_once 'corsConfig.php';
initializeEndpoint();

header("Content-Type: application/json");

// Enhanced debugging for session issues
$debug_info = [
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_status' => session_status(),
    'session_save_path' => session_save_path(),
    'origin' => isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'Not set',
    'cookies' => $_COOKIE,
    'session_data_keys' => array_keys($_SESSION ?? [])
];

// Log session check attempt
error_log("GearSphere: Session check attempt - Session ID: " . session_id() . ", Has user_id: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO'));

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    // Enhanced debug information for troubleshooting
    $debug_info['session_contents'] = $_SESSION ?? [];

    error_log("GearSphere: Session check failed - No user_id or user_type in session");

    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No active session',
        'debug' => $debug_info
    ]);
    exit();
}

// Return session data
$response = [
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'user_type' => $_SESSION['user_type'],
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : null,
    'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null
];

// Add technician_id if present
if (isset($_SESSION['technician_id'])) {
    $response['technician_id'] = $_SESSION['technician_id'];
}

http_response_code(200);
echo json_encode($response);
