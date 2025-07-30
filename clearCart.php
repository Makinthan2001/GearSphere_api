<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Cart.php';

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit;
}

$user_id = $_SESSION['user_id']; // Get from session instead

$cart = new Cart();
$success = $cart->clearCart($user_id);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Cart cleared.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to clear cart.']);
}
