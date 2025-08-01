<?php
/**
 * Get Seller Orders API Endpoint
 * 
 * This script retrieves comprehensive order data for sellers including:
 * - Complete order information with customer details
 * - Order items with product information
 * - Payment method details
 * - Customer shipping information
 * - Grouped order structure for easy frontend consumption
 * 
 * Method: GET
 * Authentication: Required (seller session)
 * Returns: Array of orders with nested items and customer data
 */

// Initialize CORS configuration and session management
require_once __DIR__ . '/corsConfig.php';
initializeEndpoint();
require_once __DIR__ . '/sessionConfig.php';
require_once __DIR__ . '/DbConnector.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify user authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Establish database connection
    $pdo = (new DBConnector())->connect();
    
    // Complex query joining orders, order_items, products, users, and payment tables
    // This retrieves all necessary data in a single query for efficiency
    $stmt = $pdo->prepare(
        "SELECT o.order_id, o.order_date, o.status AS order_status, o.total_amount,
                u.user_id AS customer_id, u.name AS customer_name, u.email AS customer_email, u.contact_number AS customer_phone, u.address AS shipping_address,
                oi.order_item_id, oi.product_id, oi.quantity, oi.price,
                p.name AS product_name, p.category, p.image_url,
                pay.payment_method
         FROM orders o
         JOIN order_items oi ON o.order_id = oi.order_id
         JOIN products p ON oi.product_id = p.product_id
         JOIN users u ON o.user_id = u.user_id
         LEFT JOIN payment pay ON o.order_id = pay.order_id
         ORDER BY o.order_date DESC"
    );
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group items by order for hierarchical structure
    // This transforms flat database results into nested order structure
    $orders = [];
    foreach ($rows as $row) {
        $oid = $row['order_id'];
        
        // Create new order entry if not exists
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'order_id' => $oid,
                'date' => $row['order_date'],
                'status' => ucfirst($row['order_status']),
                'total' => $row['total_amount'],
                'customer' => [
                    'id' => $row['customer_id'],
                    'name' => $row['customer_name'],
                    'email' => $row['customer_email'],
                    'phone' => $row['customer_phone'],
                    'address' => $row['shipping_address'],
                ],
                'items' => [],
                'paymentMethod' => $row['payment_method'] ?? '',
            ];
        }
        
        // Add order item to the existing order
        $orders[$oid]['items'][] = [
            'order_item_id' => $row['order_item_id'],
            'product_id' => $row['product_id'],
            'name' => $row['product_name'],
            'category' => $row['category'],
            'image_url' => $row['image_url'],
            'quantity' => $row['quantity'],
            'price' => $row['price'],
        ];
    }
    
    // Return grouped orders data (convert associative array to indexed array)
    echo json_encode(['success' => true, 'orders' => array_values($orders)]);
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
