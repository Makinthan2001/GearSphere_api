<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once 'DbConnector.php';

// Get user info from session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];

// Only allow customers to access this endpoint
if (strtolower($userType) !== 'customer') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Customer access only.'
    ]);
    exit;
}

try {
    $pdo = (new DBConnector())->connect();

    // Get total orders count
    $totalOrdersQuery = "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?";
    $stmt = $pdo->prepare($totalOrdersQuery);
    $stmt->execute([$userId]);
    $totalOrdersResult = $stmt->fetch();
    $totalOrders = $totalOrdersResult['total_orders'];

    // Get delivered orders count
    $deliveredOrdersQuery = "SELECT COUNT(*) as delivered_orders FROM orders WHERE user_id = ? AND status = 'delivered'";
    $stmt = $pdo->prepare($deliveredOrdersQuery);
    $stmt->execute([$userId]);
    $deliveredOrdersResult = $stmt->fetch();
    $deliveredOrders = $deliveredOrdersResult['delivered_orders'];

    // Get pending orders count
    $pendingOrdersQuery = "SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status IN ('pending', 'processing', 'shipped')";
    $stmt = $pdo->prepare($pendingOrdersQuery);
    $stmt->execute([$userId]);
    $pendingOrdersResult = $stmt->fetch();
    $pendingOrders = $pendingOrdersResult['pending_orders'];

    // Get last 5 reviews where target_type = 'system'
    $reviewsQuery = "SELECT r.*, p.name as product_name 
                     FROM reviews r 
                     LEFT JOIN products p ON r.target_id = p.product_id 
                     WHERE r.user_id = ? AND r.target_type = 'system' 
                     ORDER BY r.created_at DESC 
                     LIMIT 5";
    $stmt = $pdo->prepare($reviewsQuery);
    $stmt->execute([$userId]);

    $reviews = [];
    while ($row = $stmt->fetch()) {
        $reviews[] = [
            'review_id' => $row['id'],
            'product_id' => $row['target_id'],
            'product_name' => $row['product_name'],
            'rating' => $row['rating'],
            'review_text' => $row['comment'],
            'review_date' => $row['created_at'],
            'target_type' => $row['target_type'],
            'status' => $row['status']
        ];
    }

    // Get customer details for additional info
    $customerQuery = "SELECT name, email FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($customerQuery);
    $stmt->execute([$userId]);
    $customerResult = $stmt->fetch();

    $response = [
        'success' => true,
        'user_id' => $userId,
        'user_type' => $userType,
        'customer_name' => $customerResult['name'] ?? 'Unknown',
        'customer_email' => $customerResult['email'] ?? '',
        'dashboard_data' => [
            'total_orders' => (int)$totalOrders,
            'delivered_orders' => (int)$deliveredOrders,
            'pending_orders' => (int)$pendingOrders,
            'recent_reviews' => $reviews,
            'total_reviews' => count($reviews)
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch dashboard data',
        'error' => $e->getMessage()
    ]);
}
