<?php
require_once 'corsConfig.php';
initializeEndpoint();

header('Content-Type: application/json');

require_once __DIR__ . '/Main Classes/Orders.php';
require_once __DIR__ . '/Main Classes/OrderItems.php';
require_once __DIR__ . '/Main Classes/Payment.php';
require_once __DIR__ . '/Main Classes/Product.php';
require_once __DIR__ . '/Main Classes/Notification.php';
require_once __DIR__ . '/Main Classes/Mailer.php';

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['items'], $data['total_amount'], $data['payment_method'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$user_id = $_SESSION['user_id']; // Get from session instead
$items = $data['items']; // array of [product_id, quantity, price]
$total_amount = $data['total_amount'];
$payment_method = $data['payment_method'];
$assignment_id = isset($data['assignment_id']) && is_numeric($data['assignment_id']) ? (int)$data['assignment_id'] : null;
$delivery_charge = isset($data['delivery_charge']) ? floatval($data['delivery_charge']) : 0.00;
$delivery_address = isset($data['delivery_address']) ? $data['delivery_address'] : null;

$orderObj = new Orders();
$orderItemsObj = new OrderItems();
$paymentObj = new Payment();
$productObj = new Product();
$notificationObj = new Notification();

// 1. Create order (delivery address will be saved from the provided address)
$order_id = $orderObj->createOrder($user_id, $total_amount, $assignment_id, 'pending', $delivery_charge, $delivery_address);
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Failed to create order.']);
    exit;
}

// 2. Add order items
$orderItems = [];
foreach ($items as $item) {
    if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid item data.']);
        exit;
    }
    
    $ok = $orderItemsObj->addOrderItem($order_id, $item['product_id'], $item['quantity'], $item['price']);
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Failed to add order item.']);
        exit;
    }
    
    // Get product details for low stock notifications
    $product = $productObj->getProductById($item['product_id']);
    if ($product) {
        $orderItems[] = [
            'name' => $product['name'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
        
        // Reduce stock and check for low stock notifications
        $oldStock = $product['stock'];
        $newStock = max(0, $oldStock - $item['quantity']);
        $productObj->updateStock($item['product_id'], $newStock);
        
        $minStock = 5; // Low stock threshold
        if ($newStock <= $minStock && $newStock >= 0) {
            // Send low stock alert to all sellers for inventory management
            $productName = $product['name'] ?? 'Unknown Product';
            $message = "⚠️ Low Stock Alert!\nProduct stock reduced due to customer order:\n\n$productName - Current Stock: $newStock (Min: $minStock)\n\nOrder ID: $order_id";
            $notificationObj->notifyAllSellers($message);
        }
    }
}

// 3. Add payment
$payment_id = $paymentObj->addPayment($order_id, $user_id, $total_amount, $payment_method, 'success');
if (!$payment_id) {
    echo json_encode(['success' => false, 'message' => 'Failed to add payment.']);
    exit;
}

// 4. Send new order notification to sellers
try {
    $item_count = count($items);
    $notification_sent = $notificationObj->createNewOrderNotification($order_id, $user_id, $total_amount, $item_count);
    
    if ($notification_sent) {
        error_log("Seller notification sent successfully for order {$order_id}");
    } else {
        error_log("Failed to send seller notification for order {$order_id}");
    }
} catch (Exception $e) {
    // Don't fail the order if notification fails
    error_log("Error sending seller notification for order {$order_id}: " . $e->getMessage());
}

echo json_encode(['success' => true, 'order_id' => $order_id, 'payment_id' => $payment_id]);
