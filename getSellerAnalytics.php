<?php
require_once __DIR__ . '/corsConfig.php';
initializeEndpoint();
require_once __DIR__ . '/sessionConfig.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/Main Classes/Orders.php';

try {
    $orders = new Orders();
    $summary = [
        'totalRevenue' => (float)$orders->getTotalRevenue(),
        'totalOrders' => (int)$orders->getTotalOrders(),
        'averageOrderValue' => (float)$orders->getAverageOrderValue(),
        'conversionRate' => 0 // Placeholder, needs logic if available
    ];
    $salesTrend = $orders->getSalesTrend('month');
    $topProducts = $orders->getTopProducts(3);
    $categoryPerformance = $orders->getCategoryPerformance();

    echo json_encode([
        'success' => true,
        'summary' => $summary,
        'salesTrend' => $salesTrend,
        'topProducts' => $topProducts,
        'categoryPerformance' => $categoryPerformance
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
