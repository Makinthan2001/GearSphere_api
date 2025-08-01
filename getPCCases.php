<?php
/**
 * Get PC Cases API Endpoint
 * 
 * This script retrieves all PC case products with detailed specifications.
 * It handles:
 * - PC case data retrieval with complete product details
 * - Error handling for database issues
 * - JSON response formatting for product comparison
 * 
 * Method: GET
 * Returns: Array of PC case products with detailed specifications
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Compare_product class for product operations
require_once __DIR__ . '/Main Classes/Compare_product.php';

try {
    // Create Compare_product object for database operations
    $product = new Compare_product();
    
    // Retrieve all PC case products with detailed specifications
    $pcCases = $product->getAllPCCasesWithDetails();
    
    // Return successful response with PC case data
    echo json_encode([
        'success' => true,
        'data' => $pcCases
    ]);
} catch (Exception $e) {
    // Handle database or system errors
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
