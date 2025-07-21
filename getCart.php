<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/Main Classes/Cart.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

$cart = new Cart();
$items = $cart->getCart($user_id);

// The frontend component expects an 'id' field for keying and context logic.
$itemsWithId = array_map(function ($item) {
    $item['id'] = $item['product_id'];
    return $item;
}, $items);

echo json_encode(['success' => true, 'cart' => $itemsWithId]);
