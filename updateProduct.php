<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = $_POST;
    $imageFile = $_FILES['image'] ?? null;
    $productId = $_POST['product_id'] ?? null;
    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    $product = new Product();
    $result = $product->updateProduct($productId, $data, $imageFile);
    // If you need to update specific tables, you can do so here or extend the Product class
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

function setProductStockAndStatus($productId, $newStock) {
    $db = (new DBConnector())->connect();
    $newStatus = ($newStock == 0) ? 'Out of Stock' : (($newStock <= 5) ? 'Low Stock' : 'In Stock');
    $sql = "UPDATE products SET stock = :stock, status = :status WHERE product_id = :product_id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([
        ':stock' => $newStock,
        ':status' => $newStatus,
        ':product_id' => $productId
    ]);
}
?> 