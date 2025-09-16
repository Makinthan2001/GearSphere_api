<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/DbConnector.php';
require_once __DIR__ . '/Main Classes/Notification.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents(__DIR__ . '/order_status_update.log', date('c') . ' - ' . json_encode($data) . PHP_EOL, FILE_APPEND);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : null;
$status = isset($data['status']) ? strtolower(trim($data['status'])) : null;

$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!$order_id || !$status || !in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid order_id or status']);
    exit;
}

try {
    $pdo = (new DBConnector())->connect();
    
    // Get customer ID for this order before updating
    $stmt = $pdo->prepare('SELECT user_id FROM orders WHERE order_id = :order_id');
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $customer_id = $order['user_id'];
    
    // Update order status
    $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE order_id = :order_id');
    $result = $stmt->execute([':status' => $status, ':order_id' => $order_id]);
    
    if ($result) {
        // Create notification for customer
        $notification = new Notification();
        $notification_result = $notification->createOrderStatusNotification($order_id, $customer_id, $status);
        
        if ($notification_result) {
            error_log("Order status updated and notification sent - Order: {$order_id}, Customer: {$customer_id}, Status: {$status}");
        } else {
            error_log("Order status updated but notification failed - Order: {$order_id}, Customer: {$customer_id}, Status: {$status}");
        }
        
        echo json_encode(['success' => true, 'message' => 'Order status updated and customer notified']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    }
    
} catch (Exception $e) {
    error_log("Error in updateOrderStatus: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
