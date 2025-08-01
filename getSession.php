<?php

/**
 * Session Retrieval API Endpoint
 * 
 * This endpoint retrieves current session information for the GearSphere system.
 * It includes comprehensive debugging features for troubleshooting session issues
 * and returns detailed session data for authenticated users.
 * 
 * @method GET/POST
 * @endpoint /getSession.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header("Content-Type: application/json");

// ====================================================================
// DEBUG INFORMATION COLLECTION
// ====================================================================

// Enhanced debugging for session issues - collect comprehensive session state
$debug_info = [
    'session_id' => session_id(),                    // Current session identifier
    'session_name' => session_name(),                // Session name configuration
    'session_status' => session_status(),            // Session status code
    'session_save_path' => session_save_path(),      // Where sessions are stored
    'origin' => isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'Not set',
    'cookies' => $_COOKIE,                           // All cookies sent by client
    'session_data_keys' => array_keys($_SESSION ?? [])  // Keys present in session
];

// ====================================================================
// SESSION ACCESS LOGGING
// ====================================================================

// Log session check attempt for debugging and monitoring
error_log("GearSphere: Session check attempt - Session ID: " . session_id() . 
          ", Has user_id: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO'));

// ====================================================================
// SESSION VALIDATION
// ====================================================================

// Check if user is logged in by verifying required session variables
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    // Enhanced debug information for troubleshooting session failures
    $debug_info['session_contents'] = $_SESSION ?? [];

    // Log session validation failure
    error_log("GearSphere: Session check failed - No user_id or user_type in session");

    // Return unauthorized response with debug information
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No active session',
        'debug' => $debug_info
    ]);
    exit();
}

// ====================================================================
// COMPILE AND RETURN SESSION DATA
// ====================================================================

// Return comprehensive session data for authenticated users
$response = [
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'user_type' => $_SESSION['user_type'],
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : null,
    'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null
];

// Add technician-specific data if user is a technician
if (isset($_SESSION['technician_id'])) {
    $response['technician_id'] = $_SESSION['technician_id'];
}

// Return successful session response
http_response_code(200);
echo json_encode($response);
