<?php
// Common session configuration for all endpoints
function initializeSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        // Set session timeout to 3 hours (10800 seconds)
        ini_set('session.gc_maxlifetime', 10800);
        ini_set('session.cookie_lifetime', 10800);

        // Configure session before starting
        session_set_cookie_params([
            'lifetime' => 10800, // 3 hours
            'path' => '/',
            'domain' => '',
            'secure' => false,  // Set to true if using HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();

        // Set last activity timestamp if not set
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }
}

// Handle OPTIONS requests
if (!function_exists('handleOptions')) {
    function handleOptions()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}

// Check if user is authenticated
if (!function_exists('checkAuthentication')) {
    function checkAuthentication()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized. Please login first.',
                'debug' => [
                    'session_id' => session_id(),
                    'session_data' => $_SESSION
                ]
            ]);
            exit();
        }
        return [
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type']
        ];
    }
}
