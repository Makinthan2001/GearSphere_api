<?php

/**
 * Clear Shopping Cart API Endpoint
 * 
 * This endpoint handles clearing all items from a user's shopping cart in the GearSphere system.
 * It validates user authentication, removes all cart items, and manages cart state cleanup
 * with proper error handling and security checks.
 * 
 * @method POST
 * @endpoint /clearCart.php
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
// PROCESS CART CLEARING
// ====================================================================

// Get authenticated user ID from session
$user_id = $_SESSION['user_id'];

// Clear all items from user's cart
$cart = new Cart();
$success = $cart->clearCart($user_id);

if ($success) {
    // Cart clearing successful
    echo json_encode(['success' => true, 'message' => 'Cart cleared.']);
} else {
    // Cart clearing failed
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to clear cart.']);
}
