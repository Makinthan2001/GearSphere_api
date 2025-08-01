<?php
require_once 'corsConfig.php';
initializeEndpoint();

header("Content-Type: application/json");

// Check if session exists and is valid
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired or invalid',
        'expired' => true
    ]);
    exit();
}

// Check if session has a last activity timestamp
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Check session timeout (3 hours = 10800 seconds)
$session_timeout = 10800; // 3 hours
if (time() - $_SESSION['last_activity'] > $session_timeout) {
    // Session expired, destroy it
    session_destroy();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired due to inactivity',
        'expired' => true
    ]);
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Return valid session data
$response = [
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'user_type' => $_SESSION['user_type'],
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : null,
    'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
    'last_activity' => $_SESSION['last_activity']
];

// Add technician_id if present
if (isset($_SESSION['technician_id'])) {
    $response['technician_id'] = $_SESSION['technician_id'];
}

http_response_code(200);
echo json_encode($response);
