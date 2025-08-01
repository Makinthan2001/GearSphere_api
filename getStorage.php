<?php
/**
 * Get Storage API Endpoint
 * 
 * This script retrieves all storage products with detailed specifications.
 * It handles:
 * - Storage data retrieval with complete product details
 * - Error handling for database issues
 * - JSON response formatting for product comparison
 * 
 * Method: GET
 * Returns: Array of storage products with detailed specifications
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Compare_product class for product operations
require_once "Main Classes/Compare_product.php";

try {
    // Create Compare_product object for database operations
    $compareProduct = new Compare_product();
    
    // Retrieve all storage products with detailed specifications
    $storage = $compareProduct->getAllStorageWithDetails();

    // Return successful response with storage data
    echo json_encode([
        "success" => true,
        "data" => $storage
    ]);
} catch (Exception $e) {
    // Handle database or system errors
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch storage: " . $e->getMessage()
    ]);
}
