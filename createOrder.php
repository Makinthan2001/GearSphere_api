<?php

/**
 * Order Creation API Endpoint
 * 
 * This endpoint handles the complete order creation process for the GearSphere system.
 * It manages order creation, inventory updates, payment processing, and notification
 * delivery in a comprehensive checkout workflow with proper error handling.
 * 
 * @method POST
 * @endpoint /createOrder.php
 * @param float $total_amount Total amount for the order
 * @param array $items Array of items being ordered
 * @param string $payment_method Method of payment used
 * @param int|null $assignment_id Optional assignment ID for the order
 * @return array Response indicating success or failure of order creation
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');

// Import required classes for order processing
require_once __DIR__ . '/Main Classes/Orders.php';
require_once __DIR__ . '/Main Classes/OrderItems.php';
require_once __DIR__ . '/Main Classes/Payment.php';
require_once __DIR__ . '/Main Classes/Product.php';
require_once __DIR__ . '/Main Classes/Notification.php';
require_once __DIR__ . '/Main Classes/Mailer.php';

// ====================================================================
// AUTHENTICATION CHECK - Verify user session
// ====================================================================

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit;
}

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Get JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields for order creation
if (!isset($data['items'], $data['total_amount'], $data['payment_method'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Extract order parameters
$user_id = $_SESSION['user_id'];                           // Authenticated user from session
$items = $data['items'];                                   // Array of [product_id, quantity, price]
$total_amount = $data['total_amount'];                     // Total order value
$payment_method = $data['payment_method'];                 // Payment method used
$assignment_id = isset($data['assignment_id']) && is_numeric($data['assignment_id']) ? (int)$data['assignment_id'] : null;

// Initialize required class instances
$orderObj = new Orders();
$orderItemsObj = new OrderItems();
$paymentObj = new Payment();
$productObj = new Product();
$notificationObj = new Notification();

// ====================================================================
// STEP 1: CREATE MAIN ORDER RECORD
// ====================================================================

$order_id = $orderObj->createOrder($user_id, $total_amount, $assignment_id);
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Failed to create order.']);
    exit;
}

// ====================================================================
// STEP 2: PROCESS ORDER ITEMS AND INVENTORY UPDATES
// ====================================================================

$orderItems = [];
foreach ($items as $item) {
    // Validate individual item data
    if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid item data.']);
        exit;
    }
    
    // Add item to order
    $ok = $orderItemsObj->addOrderItem($order_id, $item['product_id'], $item['quantity'], $item['price']);
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Failed to add order item.']);
        exit;
    }
    
    // Get product details for inventory management
    $product = $productObj->getProductById($item['product_id']);
    if ($product) {
        $orderItems[] = [
            'name' => $product['name'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
        
        // Update inventory - reduce stock by ordered quantity
        $oldStock = $product['stock'];
        $newStock = max(0, $oldStock - $item['quantity']);
        $productObj->updateStock($item['product_id'], $newStock);
        
        // Check for low stock and send notifications
        $minStock = 5; // Low stock threshold
        if ($newStock <= $minStock && $newStock >= 0) {
            $sellerId = 28; // Admin/Seller user ID for notifications
            $productName = $product['name'] ?? 'Unknown Product';
            
            // Create detailed low stock notification
            $message = "Low Stock Alert!\nProduct stock reduced due to customer order:\n\n$productName - Current Stock: $newStock (Min: $minStock)\n\nOrder ID: $order_id";
            
            // Send unique notification with 24-hour deduplication
            $notificationObj->addUniqueNotification($sellerId, $message, 24);
        }
    }
}

// ====================================================================
// STEP 3: PROCESS PAYMENT
// ====================================================================

$payment_id = $paymentObj->addPayment($order_id, $user_id, $total_amount, $payment_method, 'success');
if (!$payment_id) {
    echo json_encode(['success' => false, 'message' => 'Failed to add payment.']);
    exit;
}

// ====================================================================
// RETURN SUCCESS RESPONSE
// ====================================================================

echo json_encode(['success' => true, 'order_id' => $order_id, 'payment_id' => $payment_id]);
