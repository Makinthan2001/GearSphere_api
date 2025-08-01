<?php
/**
 * Get Customer Profile API Endpoint
 * 
 * This script retrieves customer profile information for authenticated users.
 * It handles:
 * - Session-based authentication verification
 * - Customer profile data retrieval
 * - Error handling for unauthorized access and missing users
 * 
 * Method: GET/POST
 * Authentication: Required (customer session)
 * Returns: Customer profile data
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Customer class for database operations
require_once './Main Classes/Customer.php';

// Verify user authentication through session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Please login first."]);
    exit();
}

// Extract user ID from active session
$user_id = $_SESSION['user_id'];

// Create Customer object for database operations
$customerDetail = new Customer();

// Retrieve customer profile details from database
$result = $customerDetail->getDetails($user_id);

// Return response based on query result
if ($result) {
    // Success: Return customer profile data
    http_response_code(200);
    echo json_encode($result);
} else {
    // Error: Customer not found
    http_response_code(404);
    echo json_encode(["message" => "User not found"]);
}
