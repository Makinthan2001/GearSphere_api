<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'DbConnector.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['product_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }

    $product_id = intval($_GET['product_id']);
    $database = new DBConnector();
    $conn = $database->connect();

    // Get basic product information
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'product' => $product
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
