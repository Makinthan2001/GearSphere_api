<?php
require_once 'corsConfig.php';
initializeEndpoint();

header('Content-Type: application/json');

require_once __DIR__ . '/Main Classes/Orders.php';
require_once __DIR__ . '/Main Classes/OrderItems.php';
require_once __DIR__ . '/Main Classes/Payment.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : null;

// Either user_id or assignment_id must be provided
if (!$user_id && !$assignment_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing user_id or assignment_id']);
    exit;
}

try {
    $ordersObj = new Orders();
    $orderItemsObj = new OrderItems();
    $paymentObj = new Payment();
    
    // Get orders either by user_id or assignment_id
    if ($assignment_id) {
        $orders = $ordersObj->getOrdersByAssignmentId($assignment_id);
    } else {
        $orders = $ordersObj->getOrdersByUserId($user_id);
    }
    
    $pdo = (new DBConnector())->connect();
    $result = [];
    foreach ($orders as $order) {
        $items = $orderItemsObj->getDetailedItemsByOrderId($order['order_id']);
        $payment = $paymentObj->getPaymentByOrderId($order['order_id']);
        $assignmentStatus = null;
        $shippingAddress = '';
        $phoneNumber = '';
        $technicianId = null;
        
        // Use delivery address from orders table if available, otherwise fallback to users table
        if (!empty($order['delivery_address'])) {
            $shippingAddress = $order['delivery_address'];
        } else {
            // Fallback to address from users table for old orders
            $stmtAddr = $pdo->prepare("SELECT address FROM users WHERE user_id = :user_id");
            $stmtAddr->execute([':user_id' => $order['user_id']]);
            $rowAddr = $stmtAddr->fetch(PDO::FETCH_ASSOC);
            if ($rowAddr && !empty($rowAddr['address'])) {
                $shippingAddress = $rowAddr['address'];
            }
        }
        
        // Fetch phone number from users table (this doesn't change with orders)
        $stmtPhone = $pdo->prepare("SELECT contact_number FROM users WHERE user_id = :user_id");
        $stmtPhone->execute([':user_id' => $order['user_id']]);
        $rowPhone = $stmtPhone->fetch(PDO::FETCH_ASSOC);
        if ($rowPhone && !empty($rowPhone['contact_number'])) {
            $phoneNumber = $rowPhone['contact_number'];
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
            'deliveryCharge' => $order['delivery_charge'] ?? 0,
            'deliveryAddress' => $shippingAddress, // Use address from user table
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
