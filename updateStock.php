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

    // Get the actual seller ID from session instead of hardcoding
    $sellerId = $_SESSION['user_id'] ?? null;
    
    if ($sellerId) {
        // Fetch product details to get product info
        $productDetails = $product->getProductById($productId);
        $productName = $productDetails['name'] ?? '';
        $minStock = 5; // You can make this dynamic if needed
        
        if ($newStock == 0 || $newStock <= $minStock) {
            $notif = new Notification();
            $message = "Low Stock Alert!\nYou have 1 items that need attention:\n\n$productName - Current Stock: $newStock (Min: $minStock)";
            $notif->addUniqueNotification($sellerId, $message, 24); // Use unique notification with 24-hour window
        }
    }
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
