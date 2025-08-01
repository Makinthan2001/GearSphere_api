<?php
/**
 * Get CPUs API Endpoint
 * 
 * This script retrieves all CPUs with detailed specifications.
 * It handles:
 * - CPU data retrieval with complete product details
 * - Error handling for database issues
 * - JSON response formatting for product comparison
 * 
 * Method: GET
 * Returns: Array of CPUs with detailed specifications
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Compare_product class for product operations
require_once __DIR__ . '/Main Classes/Compare_product.php';

try {
    // Create Compare_product object for database operations
    $product = new Compare_product();
    
    // Retrieve all CPUs with detailed specifications
    $cpus = $product->getAllCPUsWithDetails();
    
    // Return successful response with CPU data
    echo json_encode([
        'success' => true,
        'data' => $cpus
    ]);
} catch (Exception $e) {
    // Handle database or system errors
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
