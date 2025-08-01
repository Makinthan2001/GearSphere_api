<?php

/**
 * Shopping Cart Quantity Update API Endpoint
 * 
 * This endpoint handles updating item quantities in a user's shopping cart for the GearSphere system.
 * It validates user authentication, processes quantity changes, and manages cart state
 * with proper error handling and security checks.
 * 
 * @method POST
 * @endpoint /updateCartQuantity.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();

// Import Cart class for cart operations
require_once __DIR__ . '/Main Classes/Cart.php';

// ====================================================================
// AUTHENTICATION CHECK - Verify user session
// ====================================================================

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit;
}

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Get JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);

// Extract required parameters
$user_id = $_SESSION['user_id'];              // Get authenticated user ID from session
$product_id = $data['product_id'] ?? null;    // Product to update quantity for
$quantity = $data['quantity'] ?? null;        // New quantity value

// Validate required parameters
if (!$product_id || $quantity === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required.']);
    exit;
}

// ====================================================================
// PROCESS CART QUANTITY UPDATE
// ====================================================================

$cart = new Cart();
$success = $cart->updateQuantity($user_id, $product_id, $quantity);

if ($success) {
    // Quantity update successful
    echo json_encode(['success' => true, 'message' => 'Cart quantity updated.']);
} else {
    // Quantity update failed
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update cart quantity.']);
}
