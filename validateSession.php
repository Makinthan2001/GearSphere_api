<?php

/**
 * Session Validation API Endpoint
 * 
 * This endpoint validates user sessions and manages session timeouts for the GearSphere system.
 * It checks session validity, enforces inactivity timeouts, updates activity timestamps,
 * and returns current session data for authentication verification.
 * 
 * @method GET/POST
 * @endpoint /validateSession.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header("Content-Type: application/json");

// ====================================================================
// SESSION EXISTENCE CHECK
// ====================================================================

// Check if session exists and contains required authentication data
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired or invalid',
        'expired' => true
    ]);
    exit();
}

// ====================================================================
// SESSION ACTIVITY TRACKING INITIALIZATION
// ====================================================================

// Initialize last activity timestamp if not present
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// ====================================================================
// SESSION TIMEOUT VALIDATION
// ====================================================================

// Define session timeout duration (3 hours = 10800 seconds)
$session_timeout = 10800; // 3 hours

// Check if session has exceeded inactivity timeout
if (time() - $_SESSION['last_activity'] > $session_timeout) {
    // Session expired due to inactivity - destroy it
    session_destroy();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired due to inactivity',
        'expired' => true
    ]);
    exit();
}

// ====================================================================
// UPDATE SESSION ACTIVITY AND RETURN DATA
// ====================================================================

// Update last activity timestamp to current time
$_SESSION['last_activity'] = time();

// Compile session response data
$response = [
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'user_type' => $_SESSION['user_type'],
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : null,
    'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
    'last_activity' => $_SESSION['last_activity']
];

// Add technician-specific data if user is a technician
if (isset($_SESSION['technician_id'])) {
    $response['technician_id'] = $_SESSION['technician_id'];
}

// Return valid session information
http_response_code(200);
echo json_encode($response);
