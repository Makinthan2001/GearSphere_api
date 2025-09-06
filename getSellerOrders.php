<?php
require_once __DIR__ . '/corsConfig.php';
initializeEndpoint();
require_once __DIR__ . '/sessionConfig.php';
require_once __DIR__ . '/DbConnector.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
try {
    $pdo = (new DBConnector())->connect();
    $stmt = $pdo->prepare(
        "SELECT o.order_id, o.order_date, o.status AS order_status, o.total_amount,
                o.delivery_charge, o.delivery_address,
                u.user_id AS customer_id, u.name AS customer_name, u.email AS customer_email, u.contact_number AS customer_phone, u.address AS customer_address,
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

    // Group items by order
    $orders = [];
    foreach ($rows as $row) {
        $oid = $row['order_id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'order_id' => $oid,
                'date' => $row['order_date'],
                'status' => ucfirst($row['order_status']),
                'total' => $row['total_amount'],
                'delivery_charge' => $row['delivery_charge'] ?? 0,
                'delivery_address' => $row['delivery_address'] ?: $row['customer_address'], // Use order delivery address or fallback to user address
                'customer' => [
                    'id' => $row['customer_id'],
                    'name' => $row['customer_name'],
                    'email' => $row['customer_email'],
                    'phone' => $row['customer_phone'],
                    'address' => $row['customer_address'],
                ],
                'items' => [],
                'paymentMethod' => $row['payment_method'] ?? '',
            ];
        }
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
    echo json_encode(['success' => true, 'orders' => array_values($orders)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
