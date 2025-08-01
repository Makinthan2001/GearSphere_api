<?php

/**
 * Add to Cart API Endpoint
 * 
 * This endpoint handles adding products to a user's shopping cart in the GearSphere system.
 * It validates user authentication, processes the cart addition request, and manages
 * cart state with proper error handling and security checks.
 * 
 * @method POST
 * @endpoint /addToCart.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');

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
$user_id = $_SESSION['user_id'];        // Get authenticated user ID from session
$product_id = $data['product_id'] ?? null;  // Product to add to cart
$quantity = $data['quantity'] ?? 1;         // Quantity to add (default: 1)

// Validate required product ID
if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
    exit;
}

// ====================================================================
// PROCESS CART ADDITION
// ====================================================================

$cart = new Cart();
$success = $cart->addToCart($user_id, $product_id, $quantity);

if ($success) {
    // Cart addition successful
    echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
} else {
    // Cart addition failed
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add product to cart.']);
}
