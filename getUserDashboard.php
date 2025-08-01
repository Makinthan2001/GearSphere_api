<?php
/**
 * Get User Dashboard API Endpoint
 * 
 * This script provides universal dashboard data for all user types in the system.
 * It handles:
 * - Multi-user type dashboard data retrieval (customer, seller, admin, technician)
 * - Session-based authentication verification
 * - Role-specific data aggregation
 * - Conditional dashboard content based on user privileges
 * 
 * Method: GET/POST
 * Authentication: Required (any authenticated user)
 * Returns: Dashboard data customized for user type
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Verify user authentication and session data
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

// Extract user information from session
$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];

try {
    // Include all required classes for different user types
    require_once 'Main Classes/Customer.php';
    require_once 'Main Classes/Seller.php';
    require_once 'Main Classes/Admin.php';
    require_once 'Main Classes/Orders.php';

    // Initialize base response structure
    $response = [
        'success' => true,
        'user_id' => $userId,
        'user_type' => $userType,
        'dashboard_data' => []
    ];

    // Get user-specific dashboard data based on user type
    switch (strtolower($userType)) {
        case 'customer':
            // Customer dashboard: orders, profile, recent activity
            $customer = new Customer();
            $orders = new Orders();

            $response['dashboard_data'] = [
                'total_orders' => $orders->getOrdersByUserId($userId),
                'user_details' => $customer->getDetails($userId),
                'recent_orders' => array_slice($orders->getOrdersByUserId($userId), 0, 5)
            ];
            break;

        case 'seller':
            // Seller dashboard: sales data, products, analytics
            $seller = new Seller();
            $orders = new Orders();

            $response['dashboard_data'] = [
                'user_details' => $seller->getDetails($userId),
                'total_sales' => $orders->getSalesTrend('month', $userId),
                'top_products' => $orders->getTopProducts(5)
            ];
            break;

        case 'admin':
            // Admin dashboard: system statistics, revenue, user metrics
            $admin = new Admin();
            $orders = new Orders();

            $response['dashboard_data'] = [
                'user_details' => $admin->getDetails($userId),
                'total_revenue' => $orders->getTotalRevenue(),
                'total_orders' => $orders->getTotalOrders(),
                'user_stats' => $admin->getUserTypeCount()
            ];
            break;

        case 'technician':
            // Technician dashboard: assignments, profile, status
            require_once 'Main Classes/technician.php';
            $technician = new technician();

            $response['dashboard_data'] = [
                'user_details' => $technician->getTechnicianDetails($userId),
                'assignments' => [] // Placeholder for future assignment implementation
            ];
            break;

        default:
            // Fallback for unknown user types
            $response['dashboard_data'] = [
                'message' => 'Dashboard data not available for this user type'
            ];
    }

    echo json_encode($response);
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get dashboard data',
        'error' => $e->getMessage()
    ]);
}
