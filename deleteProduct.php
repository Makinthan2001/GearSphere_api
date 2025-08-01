<?php

/**
 * Product Deletion API Endpoint
 * 
 * This endpoint handles deleting products from the GearSphere catalog.
 * It supports multiple request methods and validates product IDs before
 * processing deletions with proper error handling and response formatting.
 * 
 * @method DELETE/POST
 * @endpoint /deleteProduct.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import Product class for database operations
require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    // ====================================================================
    // EXTRACT PRODUCT ID FROM MULTIPLE REQUEST FORMATS
    // ====================================================================
    
    $productId = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Handle DELETE method with query parameter
        $productId = $_GET['id'] ?? null;
    } else {
        // Handle POST method with form data or JSON
        $productId = $_POST['product_id'] ?? null;
        
        if (!$productId) {
            // Try JSON input if form data not present
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $productId = $data['product_id'] ?? null;
        }
    }
    
    // ====================================================================
    // VALIDATE PRODUCT ID AND PROCESS DELETION
    // ====================================================================
    
    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    
    // Attempt product deletion
    $product = new Product();
    $result = $product->deleteProduct($productId);
    
    // Return deletion result to client
    echo json_encode($result);
} else {
    // ====================================================================
    // INVALID REQUEST METHOD - Return error
    // ====================================================================
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
