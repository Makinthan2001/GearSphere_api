<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

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

    // Note: Low stock notifications are handled by createOrder.php for order-related updates
    // Manual stock updates by sellers can be handled separately if needed
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
