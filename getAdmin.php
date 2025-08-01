<?php
/**
 * Get Admin Profile API Endpoint
 * 
 * This script retrieves admin profile information with authentication.
 * It handles:
 * - Session-based authentication verification
 * - Admin privilege validation
 * - Secure data retrieval (removes sensitive information)
 * 
 * Method: GET/POST
 * Authentication: Required (admin session)
 * Returns: Admin profile data (without password)
 */

// Include session management and CORS configuration
require_once 'sessionConfig.php';
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Admin class for database operations
require_once 'Main Classes/Admin.php';

try {
    // Extract user ID from active session
    $user_id = $_SESSION['user_id'] ?? null;

    // Verify user is authenticated
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }

    // Create Admin object for database operations
    $admin = new Admin();

    // Retrieve admin profile data from database
    $adminData = $admin->getDetails($user_id);

    if ($adminData) {
        // Verify user has admin privileges
        if ($adminData['user_type'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Admin privileges required.']);
            exit;
        }

        // Remove sensitive information before sending response
        unset($adminData['password']);

        // Return admin profile data
        echo json_encode($adminData);
    } else {
        // Return error if admin not found
        http_response_code(404);
        echo json_encode(['error' => 'Admin not found']);
    }
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
