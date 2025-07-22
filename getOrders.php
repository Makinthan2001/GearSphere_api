<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/Main Classes/Orders.php';
require_once __DIR__ . '/Main Classes/OrderItems.php';
require_once __DIR__ . '/Main Classes/Payment.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid user_id']);
    exit;
}

try {
    $ordersObj = new Orders();
    $orderItemsObj = new OrderItems();
    $paymentObj = new Payment();
    $orders = $ordersObj->getOrdersByUserId($user_id);
    $pdo = (new DBConnector())->connect();
    $result = [];
    foreach ($orders as $order) {
        $items = $orderItemsObj->getDetailedItemsByOrderId($order['order_id']);
        $payment = $paymentObj->getPaymentByOrderId($order['order_id']);
        $assignmentStatus = null;
        $shippingAddress = '';
        $phoneNumber = '';
        $technicianId = null;
        // Fetch shipping address and phone number from users table
        $stmtAddr = $pdo->prepare("SELECT address, contact_number FROM users WHERE user_id = :user_id");
        $stmtAddr->execute([':user_id' => $order['user_id']]);
        $rowAddr = $stmtAddr->fetch(PDO::FETCH_ASSOC);
        if ($rowAddr) {
            if (!empty($rowAddr['address'])) {
                $shippingAddress = $rowAddr['address'];
            }
            if (!empty($rowAddr['contact_number'])) {
                $phoneNumber = $rowAddr['contact_number'];
            }
        }
        if (!empty($order['assignment_id'])) {
            $stmt = $pdo->prepare("SELECT status, technician_id FROM technician_assignments WHERE assignment_id = :assignment_id");
            $stmt->execute([':assignment_id' => $order['assignment_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $assignmentStatus = $row ? $row['status'] : null;
            $technicianId = $row ? $row['technician_id'] : null;
        }
        $result[] = [
            'order_id' => $order['order_id'],
            'date' => $order['order_date'],
            'orderStatus' => ucfirst($order['status']),
            'requestStatus' => $assignmentStatus ? ucfirst($assignmentStatus) : null,
            'total' => $order['total_amount'],
            'items' => $items,
            'paymentMethod' => $payment['payment_method'] ?? '',
            'paymentStatus' => $payment['payment_status'] ?? '',
            'shippingAddress' => $shippingAddress,
            'phoneNumber' => $phoneNumber,
            'trackingNumber' => $order['tracking_number'] ?? '',
            'assignmentStatus' => $assignmentStatus, // for backward compatibility
            'technicianId' => $technicianId,
        ];
    }
    echo json_encode(['success' => true, 'orders' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
