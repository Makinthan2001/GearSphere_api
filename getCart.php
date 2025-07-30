<?php
require_once 'corsConfig.php';
initializeEndpoint();

header('Content-Type: application/json');

require_once __DIR__ . '/Main Classes/Cart.php';

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit;
}

$user_id = $_SESSION['user_id']; // Get from session instead

$cart = new Cart();
$items = $cart->getCart($user_id);

// The frontend component expects an 'id' field for keying and context logic.
$itemsWithId = array_map(function ($item) {
    $item['id'] = $item['product_id'];
    return $item;
}, $items);

echo json_encode(['success' => true, 'cart' => $itemsWithId]);
