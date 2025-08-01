<?php

/**
 * Order History Retrieval API Endpoint
 * 
 * This endpoint retrieves comprehensive order history for users in the GearSphere system.
 * It aggregates order data with detailed item information, payment details, shipping info,
 * and technician assignment status for complete order tracking and management.
 * 
 * @method GET
 * @endpoint /getOrders.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');

// Import required classes for order data retrieval
require_once __DIR__ . '/Main Classes/Orders.php';
require_once __DIR__ . '/Main Classes/OrderItems.php';
require_once __DIR__ . '/Main Classes/Payment.php';

// ====================================================================
// REQUEST METHOD VALIDATION
// ====================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ====================================================================
// EXTRACT AND VALIDATE PARAMETERS
// ====================================================================

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid user_id']);
    exit;
}

try {
    // ====================================================================
    // INITIALIZE DATA RETRIEVAL OBJECTS
    // ====================================================================
    
    $ordersObj = new Orders();
    $orderItemsObj = new OrderItems();
    $paymentObj = new Payment();
    
    // Get basic order data for the user
    $orders = $ordersObj->getOrdersByUserId($user_id);
    
    // Direct database connection for additional queries
    $pdo = (new DBConnector())->connect();
    
    $result = [];
    
    // ====================================================================
    // PROCESS EACH ORDER WITH COMPREHENSIVE DETAILS
    // ====================================================================
    
    foreach ($orders as $order) {
        // Get detailed order items with product information
        $items = $orderItemsObj->getDetailedItemsByOrderId($order['order_id']);
        
        // Get payment information for this order
        $payment = $paymentObj->getPaymentByOrderId($order['order_id']);
        
        // Initialize assignment and shipping variables
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
        
        // Get technician assignment status if order has custom build service
        if (!empty($order['assignment_id'])) {
            $stmt = $pdo->prepare("SELECT status, technician_id FROM technician_assignments WHERE assignment_id = :assignment_id");
            $stmt->execute([':assignment_id' => $order['assignment_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $assignmentStatus = $row ? $row['status'] : null;
            $technicianId = $row ? $row['technician_id'] : null;
        }
        
        // Compile comprehensive order information
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
    
    // ====================================================================
    // RETURN COMPREHENSIVE ORDER DATA
    // ====================================================================
    
    echo json_encode(['success' => true, 'orders' => $result]);
} catch (Exception $e) {
    // Handle any errors in order data retrieval
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
