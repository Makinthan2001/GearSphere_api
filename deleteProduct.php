<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = null;
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $productId = $_GET['id'] ?? null;
    } else {
        $productId = $_POST['product_id'] ?? null;
        if (!$productId) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $productId = $data['product_id'] ?? null;
        }
    }
    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    $product = new Product();
    $result = $product->deleteProduct($productId);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
