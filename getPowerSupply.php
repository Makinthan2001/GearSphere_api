<?php
/**
 * Get Power Supplies API Endpoint
 * 
 * This script retrieves all power supply products with detailed specifications.
 * It handles:
 * - Power supply data retrieval with complete product details
 * - Error handling for database issues
 * - JSON response formatting for product comparison
 * 
 * Method: GET
 * Returns: Array of power supply products with detailed specifications
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Compare_product class for product operations
require_once "Main Classes/Compare_product.php";

try {
    // Create Compare_product object for database operations
    $compareProduct = new Compare_product();
    
    // Retrieve all power supply products with detailed specifications
    $psus = $compareProduct->getAllPowerSuppliesWithDetails();

    // Return successful response with power supply data
    echo json_encode([
        "success" => true,
        "data" => $psus
    ]);
} catch (Exception $e) {
    // Handle database or system errors
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch power supplies: " . $e->getMessage()
    ]);
}
