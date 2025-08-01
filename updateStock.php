<?php

/**
 * Stock Management API Endpoint
 * 
 * This endpoint handles inventory stock updates for products in the GearSphere system.
 * It updates stock levels, manages product status, tracks restock dates, and sends
 * low stock notifications to sellers when inventory falls below thresholds.
 * 
 * @method POST
 * @endpoint /updateStock.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

// Import required classes for stock management and notifications
require_once __DIR__ . '/Main Classes/Product.php';
require_once __DIR__ . '/Main Classes/Notification.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ====================================================================
    // EXTRACT AND VALIDATE REQUEST DATA
    // ====================================================================
    
    // Extract stock update parameters from POST data
    $productId = $_POST['product_id'] ?? null;
    $newStock = $_POST['stock'] ?? null;
    $newStatus = (isset($_POST['status']) && $_POST['status'] === 'Discontinued') ? 'Discontinued' : null;
    if ($newStatus === null) {
        $newStatus = '';
    }
    $lastRestockDate = $_POST['last_restock_date'] ?? null;

    // Validate required parameters
    if ($productId === null || $productId === '' || $newStock === null || $newStock === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID and stock are required',
            'debug' => [
                'received_post' => $_POST,
                'product_id' => $productId,
                'stock' => $newStock,
                'status' => $newStatus,
                'last_restock_date' => $lastRestockDate
            ]
        ]);
        exit;
    }

    // ====================================================================
    // UPDATE PRODUCT STOCK
    // ====================================================================
    
    $product = new Product();
    $result = $product->updateStock($productId, $newStock, $newStatus, $lastRestockDate);

    // ====================================================================
    // LOW STOCK NOTIFICATION SYSTEM
    // ====================================================================
    
    // Get the authenticated seller ID from session
    $sellerId = $_SESSION['user_id'] ?? null;
    
    if ($sellerId) {
        // Fetch product details to get product information for notification
        $productDetails = $product->getProductById($productId);
        $productName = $productDetails['name'] ?? '';
        $minStock = 5; // Minimum stock threshold (configurable)
        
        // Check if stock is at or below minimum threshold
        if ($newStock == 0 || $newStock <= $minStock) {
            $notif = new Notification();
            
            // Create detailed low stock alert message
            $message = "Low Stock Alert!\nYou have 1 items that need attention:\n\n$productName - Current Stock: $newStock (Min: $minStock)";
            
            // Send unique notification with 24-hour deduplication window
            // This prevents spam notifications for the same low stock issue
            $notif->addUniqueNotification($sellerId, $message, 24);
        }
    }
    
    // Return stock update result to client
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
