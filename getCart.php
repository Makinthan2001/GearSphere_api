<?php

/**
 * Shopping Cart Retrieval API Endpoint
 * 
 * This endpoint retrieves the current shopping cart contents for an authenticated user
 * in the GearSphere system. It returns detailed product information and formats
 * the data for frontend consumption with proper authentication checks.
 * 
 * @method GET
 * @endpoint /getCart.php
 */

// Initialize CORS configuration for cross-origin requests
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
// RETRIEVE AND FORMAT CART DATA
// ====================================================================

// Get authenticated user ID from session
$user_id = $_SESSION['user_id'];

// Fetch cart contents with product details
$cart = new Cart();
$items = $cart->getCart($user_id);

// Format items for frontend compatibility
// The frontend component expects an 'id' field for React key management
// and component state handling, so we map product_id to id
$itemsWithId = array_map(function ($item) {
    $item['id'] = $item['product_id'];
    return $item;
}, $items);

// Return cart data with success status
echo json_encode(['success' => true, 'cart' => $itemsWithId]);
