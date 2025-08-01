<?php
/**
 * Get Operating Systems API Endpoint
 * 
 * This script retrieves operating system products with detailed specifications.
 * It handles:
 * - All operating systems retrieval or specific OS by ID
 * - Conditional query based on ID parameter presence
 * - Error handling for database issues
 * - JSON response formatting for product comparison
 * 
 * Method: GET
 * Optional parameter: id (specific operating system ID)
 * Returns: Array of operating systems or single OS with detailed specifications
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include required classes for product operations and database connection
require_once "Main Classes/Compare_product.php";
require_once "DbConnector.php";

try {
    // Establish database connection
    $db = new DBConnector();
    $pdo = $db->connect();

    // Create Compare_product object with database connection
    $compareProduct = new Compare_product($pdo);

    // Check if a specific operating system ID is requested
    if (isset($_GET['id'])) {
        $productId = (int)$_GET['id'];
        
        // Retrieve specific operating system by ID
        $operatingSystem = $compareProduct->getOperatingSystemById($productId);

        if ($operatingSystem) {
            // Return specific operating system data
            echo json_encode([
                'success' => true,
                'data' => $operatingSystem
            ]);
        } else {
            // Return error if operating system not found
            echo json_encode([
                'success' => false,
                'message' => 'Operating System not found'
            ]);
        }
    } else {
        // Fetch all operating systems if no specific ID requested
        $operatingSystems = $compareProduct->getAllOperatingSystemsWithDetails();

        // Return all operating systems data
        echo json_encode([
            'success' => true,
            'data' => $operatingSystems
        ]);
    }
} catch (Exception $e) {
    // Handle database or system errors
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
