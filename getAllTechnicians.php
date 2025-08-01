<?php
/**
 * Get All Technicians API Endpoint
 * 
 * This script retrieves all technician records from the database.
 * It handles:
 * - Technician data retrieval with action parameter validation
 * - Error handling for empty results
 * - JSON response formatting
 * 
 * Method: GET
 * Required parameter: action=getAll
 * Returns: Array of all technician records
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Technician class for database operations
require_once './Main Classes/technician.php';

// Validate that the required action parameter is provided
if ($_GET['action'] === 'getAll') {
    // Create Technician object for database operations
    $getAllTechnicians = new technician();
    
    // Retrieve all technician records from database
    $result = $getAllTechnicians->getAllTechnicians();
    
    // Check if technicians were found and return appropriate response
    if ($result) {
        // Success: Return technician data
        http_response_code(200);
        echo json_encode($result);
    } else {
        // No technicians found or database error
        http_response_code(404);
        echo json_encode(["message" => "technicians not found"]);
    }
} else {
    // Invalid or missing action parameter
    echo json_encode(["message" => "error occured"]);
}
