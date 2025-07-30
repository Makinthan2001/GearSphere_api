<?php
require_once 'corsConfig.php';
initializeEndpoint();

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    // Debug information
    $debug_info = [
        'session_id' => session_id(),
        'session_status' => session_status(),
        'session_data' => $_SESSION,
        'cookies' => $_COOKIE
    ];

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
