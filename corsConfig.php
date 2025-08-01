<?php

/**
 * CORS (Cross-Origin Resource Sharing) Configuration for GearSphere API
 * 
 * This file handles CORS policies to allow the frontend application to
 * communicate with the backend API from different ports. It supports
 * multiple concurrent user sessions for testing different user roles.
 * @package GearSphere-BackEnd
 */

// Include session configuration utilities
require_once 'sessionConfig.php';

/**
 * Set CORS headers for cross-origin requests
 * 
 * Configures HTTP headers to allow frontend applications running on
 * different localhost ports to access the API endpoints.
 * 
 * 
 */
if (!function_exists('setCorsHeaders')) {
    function setCorsHeaders()
    {
        // Define allowed origins (multiple ports for different user roles)
        $allowedOrigins = [
            'http://localhost:3000',  // Primary Customer interface
            'http://localhost:3001',  // Admin dashboard
            'http://localhost:3002',  // Seller interface
            'http://localhost:3003',  // Technician portal
            'http://localhost:3004',  // Additional testing port
            'http://localhost:3005',  // Additional testing port
            'http://localhost:3006',  // Additional testing port
            'http://localhost:5173',  // Vite default development port
            'http://localhost:5174',  // Alternative Vite port
            'http://localhost:8080',  // Alternative development port
            'http://localhost:8081'   // Alternative development port
        ];

        // Get the request origin from HTTP headers
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        // Check if the origin is in the allowed list
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // Fallback for local development - allow any localhost origin
            if (strpos($origin, 'http://localhost:') === 0) {
                header("Access-Control-Allow-Origin: $origin");
            }
        }

        // Set additional CORS headers for API functionality
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");     // Allowed HTTP methods
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");  // Allowed headers
        header("Access-Control-Allow-Credentials: true");      // Allow cookies and authentication
        header("Access-Control-Max-Age: 86400");               // Cache preflight for 24 hours
    }
}

/**
 * Handle CORS preflight requests (OPTIONS method)
 * 
 * Browsers send OPTIONS requests before actual requests to check
 * if the cross-origin request is allowed.
 * 
 * @return void Exits with 200 status if OPTIONS request
 */
if (!function_exists('handlePreflightRequest')) {
    function handlePreflightRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            setCorsHeaders();
            http_response_code(200);
            exit();
        }
    }
}

/**
 * Generate port-specific session names
 * 
 * Creates unique session names based on the requesting port to allow
 * multiple concurrent user logins for testing different user roles.
 * 
 * @return string Port-specific session name
 */
if (!function_exists('getPortSpecificSessionName')) {
    function getPortSpecificSessionName()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $port = '';

        // Extract port number from the origin URL
        if (preg_match('/localhost:(\d+)/', $origin, $matches)) {
            $port = $matches[1];
        } else {
            // Default port if no origin or port found
            $port = '3000';
        }

        // Return unique session name for this port
        return "GEARSPHERE_SESSION_" . $port;
    }
}

/**
 * Initialize API endpoint with CORS and session configuration
 * 
 * This function should be called at the beginning of each API endpoint
 * to set up proper CORS headers and port-specific sessions.
 * 
 * @return void
 */
if (!function_exists('initializeEndpoint')) {
    function initializeEndpoint()
    {
        // Use port-specific session names to allow multiple concurrent logins
        $sessionName = getPortSpecificSessionName();

        // Configure session cookie parameters for security and stability
        session_set_cookie_params([
            'lifetime' => 10800,    // 3 hours session lifetime
            'path' => '/',          // Available across entire domain
            'domain' => '',         // Current domain only
            'secure' => false,      // Set to true for HTTPS in production
            'httponly' => true,     // Prevent JavaScript access to session cookie
            'samesite' => 'Lax'     // CSRF protection
        ]);

        // Set port-specific session name (each port gets its own session)
        session_name($sessionName);

        // Start session if not already active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();

            // Debug logging: Track session startup with port information
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'Unknown';
            error_log("GearSphere: Session started - ID: " . session_id() . 
                     ", Name: " . session_name() . 
                     ", Origin: " . $origin);
        }

        // Set CORS headers for this request
        setCorsHeaders();

        // Handle CORS preflight requests
        handlePreflightRequest();
    }
}
