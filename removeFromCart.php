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

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id']; // Get from session instead
$product_id = $data['product_id'] ?? null;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
    exit;
}

$cart = new Cart();
$success = $cart->removeFromCart($user_id, $product_id);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Product removed from cart.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to remove product from cart.']);
}
