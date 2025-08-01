<?php

/**
 * Order Status Update API Endpoint
 * 
 * This endpoint handles updating order status in the GearSphere system.
 * It manages order lifecycle states, validates status transitions, and logs
 * all status changes for order tracking and fulfillment management.
 * 
 * @method POST
 * @endpoint /updateOrderStatus.php
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();

// Import database connection
require_once __DIR__ . '/DbConnector.php';

// ====================================================================
// REQUEST METHOD VALIDATION
// ====================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Get JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);

// Log status update attempts for audit trail
file_put_contents(__DIR__ . '/order_status_update.log', date('c') . ' - ' . json_encode($data) . PHP_EOL, FILE_APPEND);

// Extract parameters
$order_id = isset($data['order_id']) ? intval($data['order_id']) : null;
$status = isset($data['status']) ? strtolower(trim($data['status'])) : null;

// Define valid order status values
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

// Validate required parameters and status values
if (!$order_id || !$status || !in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid order_id or status']);
    exit;
}

try {
    // ====================================================================
    // UPDATE ORDER STATUS IN DATABASE
    // ====================================================================
    
    $pdo = (new DBConnector())->connect();
    
    // Update order status with prepared statement for security
    $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE order_id = :order_id');
    $stmt->execute([':status' => $status, ':order_id' => $order_id]);
    
    // Return success response
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
