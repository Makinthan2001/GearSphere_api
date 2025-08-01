<?php

/**
 * Session Cleanup Utility Script
 * 
 * This development-only utility clears all GearSphere sessions across different ports
 * and environments. It's designed for testing and debugging purposes to reset
 * session state without manual browser cleanup.
 * 
 * @method GET/POST
 * @endpoint /clearAllSessions.php
 * @warning DEVELOPMENT ONLY - Should never be deployed to production
 */

// ====================================================================
// SECURITY CHECK - Restrict to development environments only
// ====================================================================

// Session cleanup script - clears all GearSphere sessions
// DEVELOPMENT ONLY - DO NOT USE IN PRODUCTION
if (
    !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) &&
    !str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost')
) {
    http_response_code(403);
    die('This script is for development only.');
}

// Initialize CORS configuration
require_once 'corsConfig.php';

// ====================================================================
// SESSION CLEANUP CONFIGURATION
// ====================================================================

// Define all possible development ports that might have active sessions
$ports = ['3000', '3001', '3002', '3003', '5173', '5174', '8080', '8081'];

echo "Clearing all GearSphere sessions...\n";

// ====================================================================
// ITERATE AND CLEAR SESSIONS FOR ALL PORTS
// ====================================================================

foreach ($ports as $port) {
    // Generate unique session name for each port
    $sessionName = "GEARSPHERE_SESSION_" . $port;

    // Set the session name for this iteration
    session_name($sessionName);

    // Start session if not already active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Clear all session data
    $_SESSION = array();

    // Remove session cookie if cookies are enabled
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,        // Set expiry time in the past
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session completely
    session_destroy();

    echo "Cleared session for port $port (session name: $sessionName)\n";
}

// ====================================================================
// COMPLETION CONFIRMATION
// ====================================================================

echo "All sessions cleared successfully!\n";
echo "You can now test with fresh sessions on each port.\n";
