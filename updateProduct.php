<?php

/**
 * Product Update API Endpoint
 * 
 * This endpoint handles updating existing products in the GearSphere catalog.
 * It processes product modifications including details and image uploads,
 * and manages stock levels with automatic status updates.
 * 
 * @method POST/PUT
 * @endpoint /updateProduct.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import Product class for database operations
require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    // ====================================================================
    // EXTRACT AND VALIDATE UPDATE DATA
    // ====================================================================
    
    $data = $_POST;                        // Product update data from form
    $imageFile = $_FILES['image'] ?? null; // Optional new product image
    $productId = $_POST['product_id'] ?? null; // ID of product to update
    
    // Validate required product ID
    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    
    // ====================================================================
    // PROCESS PRODUCT UPDATE
    // ====================================================================
    
    $product = new Product();
    $result = $product->updateProduct($productId, $data, $imageFile);
    
    // Note: The Product class handles updates to both main products table
    // and category-specific tables based on product type
    
    // Return update result to client
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

/**
 * Update product stock and automatic status management
 * 
 * This helper function updates product stock levels and automatically
 * sets the appropriate status based on stock thresholds for inventory
 * management and customer visibility.
 * 
 * @param int $productId ID of the product to update
 * @param int $newStock New stock quantity
 * @return bool True if update successful, false otherwise
 */
function setProductStockAndStatus($productId, $newStock)
{
    $db = (new DBConnector())->connect();
    
    // Determine automatic status based on stock levels
    $newStatus = ($newStock == 0) ? 'Out of Stock' : (($newStock <= 5) ? 'Low Stock' : 'In Stock');
    
    // Update both stock and status in single transaction
    $sql = "UPDATE products SET stock = :stock, status = :status WHERE product_id = :product_id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([
        ':stock' => $newStock,
        ':status' => $newStatus,
        ':product_id' => $productId
    ]);
}
