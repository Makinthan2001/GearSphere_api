<?php
/**
 * Get Seller Analytics API Endpoint
 * 
 * This script retrieves comprehensive analytics data for sellers including:
 * - Revenue and order statistics summary
 * - Sales trend analysis over time
 * - Top-performing products
 * - Category performance metrics
 * 
 * Method: GET/POST
 * Authentication: Required (any authenticated user)
 * Returns: Analytics dashboard data for sellers
 */

// Initialize CORS configuration and session management
require_once __DIR__ . '/corsConfig.php';
initializeEndpoint();
require_once __DIR__ . '/sessionConfig.php';

// Verify user authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Include Orders class for analytics data retrieval
require_once __DIR__ . '/Main Classes/Orders.php';

try {
    // Create Orders object for analytics operations
    $orders = new Orders();
    
    // Compile summary statistics for seller dashboard
    $summary = [
        'totalRevenue' => (float)$orders->getTotalRevenue(),           // Total revenue generated
        'totalOrders' => (int)$orders->getTotalOrders(),              // Total number of orders
        'averageOrderValue' => (float)$orders->getAverageOrderValue(), // Average value per order
        'conversionRate' => 0 // Placeholder for conversion rate calculation
    ];
    
    // Get sales trend data for monthly analysis
    $salesTrend = $orders->getSalesTrend('month');
    
    // Get top 3 performing products
    $topProducts = $orders->getTopProducts(3);
    
    // Get performance metrics by product category
    $categoryPerformance = $orders->getCategoryPerformance();

    // Return comprehensive analytics data
    echo json_encode([
        'success' => true,
        'summary' => $summary,
        'salesTrend' => $salesTrend,
        'topProducts' => $topProducts,
        'categoryPerformance' => $categoryPerformance
    ]);
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
