<?php
/**
 * Get All Customers API Endpoint
 * 
 * This script retrieves all customer records from the database.
 * It handles:
 * - Customer data retrieval with action parameter validation
 * - Error handling for empty results
 * - JSON response formatting
 * 
 * Method: GET
 * Required parameter: action=getAll
 * Returns: Array of all customer records
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Customer class for database operations
require_once './Main Classes/Customer.php';

// Validate that the required action parameter is provided
if ($_GET['action'] === 'getAll') {

    // Create Customer object for database operations
    $getAllCustomers = new Customer();

    // Retrieve all customer records from database
    $result = $getAllCustomers->getAllCustomers();

    // Check if customers were found and return appropriate response
    if ($result) {
        // Success: Return customer data
        http_response_code(200);
        echo json_encode($result);
    } else {
        // No customers found or database error
        http_response_code(404);
        echo json_encode(["message" => "customer not found"]);
    }
} else {
    // Invalid or missing action parameter
    echo json_encode(["message" => "error occured"]);
}
