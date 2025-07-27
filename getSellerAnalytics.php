<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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
