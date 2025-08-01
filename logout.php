<?php

/**
 * User Logout API Endpoint
 * 
 * This endpoint handles secure user logout for the GearSphere system.
 * It completely destroys user sessions, clears session cookies, and ensures
 * proper cleanup for security and session management.
 * 
 * @method POST/GET
 * @endpoint /logout.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header("Content-Type: application/json");

// ====================================================================
// SESSION CLEANUP AND DESTRUCTION
// ====================================================================

// Start session to access and destroy it
session_start();

// Clear all session variables
$_SESSION = array();

// ====================================================================
// REMOVE SESSION COOKIE FROM CLIENT
// ====================================================================

// Delete session cookie if it exists for complete logout
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,           // Set expiration time in the past
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// ====================================================================
// DESTROY SESSION ON SERVER
// ====================================================================

// Completely destroy session on server side
session_destroy();

// ====================================================================
// RETURN SUCCESS RESPONSE
// ====================================================================

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
