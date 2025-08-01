<?php

/**
 * Product Retrieval API Endpoint
 * 
 * This endpoint handles product data retrieval for the GearSphere system.
 * It supports fetching individual products, products by category, or all products
 * based on query parameters provided in the request.
 * 
 * @method GET
 * @endpoint /getProducts.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import Product class for database operations
require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // ====================================================================
    // PROCESS GET REQUEST - Retrieve product data
    // ====================================================================
    
    $product = new Product();
    
    // Extract query parameters for filtering
    $productId = $_GET['id'] ?? null;       // Specific product ID
    $category = $_GET['category'] ?? null;  // Product category filter
    
    if ($productId) {
        // Fetch single product by ID
        $result = $product->getProductById($productId);
    } elseif ($category) {
        // Fetch all products in specified category
        $result = $product->getProductsByCategory($category);
    } else {
        // Fetch all products (no filters applied)
        $result = $product->getAllProducts();
    }
    
    // Return successful response with product data
    echo json_encode(['success' => true, 'products' => $result]);
} else {
    // ====================================================================
    // INVALID REQUEST METHOD - Return error
    // ====================================================================
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
