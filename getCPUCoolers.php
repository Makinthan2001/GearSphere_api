<?php
/**
 * Get CPU Coolers API Endpoint
 * 
 * This script retrieves all CPU coolers with detailed specifications.
 * It handles:
 * - CPU cooler data retrieval with complete product details
 * - Error handling for database issues
 * - JSON response formatting for product comparison
 * 
 * Method: GET
 * Returns: Array of CPU coolers with detailed specifications
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Compare_product class for product operations
require_once __DIR__ . '/Main Classes/Compare_product.php';

try {
    // Create Compare_product object for database operations
    $product = new Compare_product();
    
    // Retrieve all CPU coolers with detailed specifications
    $cpuCoolers = $product->getAllCPUCoolersWithDetails();
    
    // Return successful response with CPU cooler data
    echo json_encode([
        'success' => true,
        'data' => $cpuCoolers
    ]);
} catch (Exception $e) {
    // Handle database or system errors
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
