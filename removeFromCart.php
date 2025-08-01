<?php

/**
 * Remove from Cart API Endpoint
 * 
 * This endpoint handles removing products from a user's shopping cart in the GearSphere system.
 * It validates user authentication, processes item removal requests, and manages
 * cart state with proper error handling and security checks.
 * 
 * @method POST
 * @endpoint /removeFromCart.php
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
$user_id = $_SESSION['user_id'];           // Get authenticated user ID from session
$product_id = $data['product_id'] ?? null; // Product to remove from cart

// Validate required product ID
if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
    exit;
}

// ====================================================================
// PROCESS CART ITEM REMOVAL
// ====================================================================

$cart = new Cart();
$success = $cart->removeFromCart($user_id, $product_id);

if ($success) {
    // Item removal successful
    echo json_encode(['success' => true, 'message' => 'Product removed from cart.']);
} else {
    // Item removal failed
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to remove product from cart.']);
}
