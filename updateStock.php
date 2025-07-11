<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/Main Classes/Product.php';
require_once __DIR__ . '/Main Classes/Notification.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $newStock = $_POST['stock'] ?? null;
    $newStatus = (isset($_POST['status']) && $_POST['status'] === 'Discontinued') ? 'Discontinued' : null;
    if ($newStatus === null) {
        $newStatus = '';
    }
    $lastRestockDate = $_POST['last_restock_date'] ?? null;

    if ($productId === null || $productId === '' || $newStock === null || $newStock === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID and stock are required',
            'debug' => [
                'received_post' => $_POST,
                'product_id' => $productId,
                'stock' => $newStock,
                'status' => $newStatus,
                'last_restock_date' => $lastRestockDate
            ]
        ]);
        exit;
    }

    $product = new Product();
    $result = $product->updateStock($productId, $newStock, $newStatus, $lastRestockDate);

    // Hardcode sellerId since there is only one seller
    $sellerId = 27;
    // Fetch product details to get seller_id and product info
    $productDetails = $product->getProductById($productId);
    $productName = $productDetails['name'] ?? '';
    $minStock = 5; // You can make this dynamic if needed
    if ($newStock == 0 || $newStock <= $minStock) {
        $notif = new Notification();
        $message = "Low Stock Alert!\nYou have 1 items that need attention:\n\n$productName - Current Stock: $newStock (Min: $minStock)";
        $notif->addNotification($sellerId, $message);
    }
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 