<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product = new Product();
    $productId = $_GET['id'] ?? null;
    $category = $_GET['category'] ?? null;
    if ($productId) {
        $result = $product->getProductById($productId);
    } elseif ($category) {
        $result = $product->getProductsByCategory($category);
    } else {
        $result = $product->getAllProducts();
    }
    echo json_encode(['success' => true, 'products' => $result]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
