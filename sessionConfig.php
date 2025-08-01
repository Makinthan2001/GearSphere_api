<?php
/**
 * Session Configuration and Authentication Utilities
 * 
 * This file contains common session management functions used across
 * all API endpoints for user authentication and session handling.
 * 
 */

/**
 * Initialize PHP session with security configurations
 * 
 * Sets up session parameters for a 3-hour timeout with secure
 * cookie settings for the GearSphere application.
 * This function should be called at the beginning of each API endpoint
 */
function initializeSession()
{
    // Check if session is not already started
    if (session_status() === PHP_SESSION_NONE) {
        // Set session timeout to 3 hours (10800 seconds)
        ini_set('session.gc_maxlifetime', 10800);   // Server-side session lifetime
        ini_set('session.cookie_lifetime', 10800);  // Client-side cookie lifetime

        // Configure secure session cookie parameters
        session_set_cookie_params([
            'lifetime' => 10800,    // 3 hours in seconds
            'path' => '/',          // Available across entire domain
            'domain' => '',         // Current domain only
            'secure' => false,      // Set to true if using HTTPS in production
            'httponly' => true,     // Prevent JavaScript access to session cookie
            'samesite' => 'Lax'     // CSRF protection
        ]);
        
        // Start the session with configured parameters
        session_start();

        // Track user activity for session management
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }
}

/**
 * Handle HTTP OPTIONS requests for CORS preflight
 * 
 * Responds to CORS preflight requests from browsers when making
 * cross-origin requests from the frontend application.
 * 
 * Exits with 200 status if OPTIONS request
 */
if (!function_exists('handleOptions')) {
    function handleOptions()
    {
        // Check if this is a CORS preflight request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}

/**
 * Verify user authentication status
 * 
 * Checks if the current session contains valid user authentication
 * data and returns user information or terminates with 401 error.
 * 
 * @return array User data array with user_id and user_type
 * @throws HTTP 401 If user is not authenticated
 */
if (!function_exists('checkAuthentication')) {
    function checkAuthentication()
    {
        // Verify required session variables exist
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            // User is not authenticated - return 401 Unauthorized
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized. Please login first.',
                'debug' => [
                    'session_id' => session_id(),      // For debugging session issues
                    'session_data' => $_SESSION        // Current session data
                ]
            ]);
            exit();
        }
        
        // Return authenticated user information
        return [
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type']
        ];
    }
}
