<?php

/**
 * CORS Configuration for GearSphere API
 * Handles multiple frontend ports for simultaneous user role testing
 */

// Include session configuration
require_once 'sessionConfig.php';

if (!function_exists('setCorsHeaders')) {
    function setCorsHeaders()
    {
        // Define allowed origins (multiple ports for different user roles)
        $allowedOrigins = [
            'http://localhost:3000',  // Customer
            'http://localhost:3001',  // Admin
            'http://localhost:3002',  // Seller
            'http://localhost:3003',  // Technician
            'http://localhost:3004',  // Additional port
            'http://localhost:3005',  // Additional port
            'http://localhost:3005',  // Additional port
            'http://localhost:3006',  // Additional port
            'http://localhost:5173',  // Vite default port
            'http://localhost:5174',  // Alternative Vite port
            'http://localhost:8080',  // Alternative port
            'http://localhost:8081'   // Alternative port
        ];

        // Get the request origin
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

        // Set other CORS headers
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400"); // Cache preflight for 24 hours
    }
}

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

if (!function_exists('getPortSpecificSessionName')) {
    function getPortSpecificSessionName()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $port = '';

        // Extract port from origin
        if (preg_match('/localhost:(\d+)/', $origin, $matches)) {
            $port = $matches[1];
        } else {
            // Default port if no origin or port found
            $port = '3000';
        }

        return "GEARSPHERE_SESSION_" . $port;
    }
}

if (!function_exists('initializeEndpoint')) {
    function initializeEndpoint()
    {
        // Set CORS headers first
        setCorsHeaders();

        // Handle preflight requests
        handlePreflightRequest();

        // Only configure session if not already active
        if (session_status() === PHP_SESSION_NONE) {
            // Use port-specific session names to allow multiple concurrent logins
            $sessionName = getPortSpecificSessionName();

            // Configure session settings with longer lifetime for stability
            session_set_cookie_params([
                'lifetime' => 10800, // 3 hours
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Set port-specific session name (each port gets its own session)
            session_name($sessionName);

            // Start session
            session_start();

            // Debug: Log session startup with port info
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'Unknown';
            error_log("GearSphere: Session started - ID: " . session_id() . ", Name: " . session_name() . ", Origin: " . $origin);
        } else {
            // Session already active, just log the info
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'Unknown';
            error_log("GearSphere: Using existing session - ID: " . session_id() . ", Name: " . session_name() . ", Origin: " . $origin);
        }
    }
}
