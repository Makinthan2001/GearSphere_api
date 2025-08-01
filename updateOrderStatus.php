<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/DbConnector.php';

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
    
    // Update order status
    $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE order_id = :order_id');
    $stmt->execute([':status' => $status, ':order_id' => $order_id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
