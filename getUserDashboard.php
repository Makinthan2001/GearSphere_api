<?php
require_once 'corsConfig.php';
initializeEndpoint();

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

try {
    require_once 'Main Classes/Customer.php';
    require_once 'Main Classes/Seller.php';
    require_once 'Main Classes/Admin.php';
    require_once 'Main Classes/Orders.php';

    $response = [
        'success' => true,
        'user_id' => $userId,
        'user_type' => $userType,
        'dashboard_data' => []
    ];

    // Get user-specific dashboard data based on user type
    switch (strtolower($userType)) {
        case 'customer':
            $customer = new Customer();
            $orders = new Orders();

            // Get customer dashboard data
            $response['dashboard_data'] = [
                'total_orders' => $orders->getOrdersByUserId($userId),
                'user_details' => $customer->getDetails($userId),
                'recent_orders' => array_slice($orders->getOrdersByUserId($userId), 0, 5)
            ];
            break;

        case 'seller':
            $seller = new Seller();
            $orders = new Orders();

            // Get seller dashboard data
            $response['dashboard_data'] = [
                'user_details' => $seller->getDetails($userId),
                'total_sales' => $orders->getSalesTrend('month', $userId),
                'top_products' => $orders->getTopProducts(5)
            ];
            break;

        case 'admin':
            $admin = new Admin();
            $orders = new Orders();

            // Get admin dashboard data
            $response['dashboard_data'] = [
                'user_details' => $admin->getDetails($userId),
                'total_revenue' => $orders->getTotalRevenue(),
                'total_orders' => $orders->getTotalOrders(),
                'user_stats' => $admin->getUserTypeCount()
            ];
            break;

        case 'technician':
            // For technician, just return basic user details
            require_once 'Main Classes/technician.php';
            $technician = new technician();

            $response['dashboard_data'] = [
                'user_details' => $technician->getTechnicianDetails($userId),
                'assignments' => [] // You can implement assignment logic here
            ];
            break;

        default:
            $response['dashboard_data'] = [
                'message' => 'Dashboard data not available for this user type'
            ];
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get dashboard data',
        'error' => $e->getMessage()
    ]);
}
