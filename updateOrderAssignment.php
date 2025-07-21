<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/Main Classes/Orders.php';

$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? (int)$data['order_id'] : null;
$assignment_id = isset($data['assignment_id']) ? (int)$data['assignment_id'] : null;

if (!$order_id || !$assignment_id) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id or assignment_id.']);
    exit;
}

$orderObj = new Orders();
$success = $orderObj->updateAssignment($order_id, $assignment_id);
if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order assignment.']);
}
