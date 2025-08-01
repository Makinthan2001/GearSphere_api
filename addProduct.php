<?php

/**
 * Product Addition API Endpoint
 * 
 * This endpoint handles adding new products to the GearSphere catalog.
 * It processes form data including product details and image uploads,
 * then stores the product in the appropriate database tables.
 * 
 * @method POST
 * @endpoint /addProduct.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import Product class for database operations
require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ====================================================================
    // PROCESS POST REQUEST - Add new product
    // ====================================================================
    
    // Log file upload data for debugging
    error_log('FILES: ' . print_r($_FILES, true));
    
    // Extract form data and file upload
    $data = $_POST;                        // Product details from form
    $imageFile = $_FILES['image'] ?? null; // Uploaded product image
    
    // Create product instance and attempt to add product
    $product = new Product();
    $result = $product->addProduct($data, $imageFile);
    
    // Note: The Product class automatically handles insertion into both
    // the main products table and category-specific tables based on
    // the product category specified in the data
    
    // Return result to client
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
