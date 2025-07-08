<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/Main Classes/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('FILES: ' . print_r($_FILES, true));
    $data = $_POST;
    $imageFile = $_FILES['image'] ?? null;
    $product = new Product();
    $result = $product->addProduct($data, $imageFile);
    // If you need to insert into specific tables, you can do so here or extend the Product class
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 