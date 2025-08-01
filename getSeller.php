<?php
/**
 * Get Seller Profile API Endpoint
 * 
 * This script retrieves seller profile information with authentication.
 * It handles:
 * - Session-based authentication verification
 * - Seller privilege validation
 * - Secure data retrieval (removes sensitive information)
 * 
 * Method: GET/POST
 * Authentication: Required (seller session)
 * Returns: Seller profile data (without password)
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include session management and Seller class
require_once 'sessionConfig.php';
require_once 'Main Classes/Seller.php';

try {
    // Extract user ID from active session
    $user_id = $_SESSION['user_id'] ?? null;

    // Verify user is authenticated
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }

    // Create Seller object for database operations
    $seller = new Seller();

    // Retrieve seller profile data from database
    $sellerData = $seller->getDetails($user_id);

    if ($sellerData) {
        // Verify user has seller privileges
        if ($sellerData['user_type'] !== 'seller') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Seller privileges required.']);
            exit;
        }

        // Remove sensitive information before sending response
        unset($sellerData['password']);

        // Return seller profile data
        echo json_encode($sellerData);
    } else {
        // Return error if seller not found
        http_response_code(404);
        echo json_encode(['error' => 'Seller not found']);
    }
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
