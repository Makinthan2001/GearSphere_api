<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'Main Classes/Seller.php';

try {
    // Get user_id from query parameters
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID is required']);
        exit;
    }
    
    // Create Seller object
    $seller = new Seller();
    
    // Get seller data
    $sellerData = $seller->getDetails($user_id);
    
    if ($sellerData) {
        // Check if user is seller
        if ($sellerData['user_type'] !== 'seller') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Seller privileges required.']);
            exit;
        }
        
        // Remove sensitive data
        unset($sellerData['password']);
        
        echo json_encode($sellerData);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Seller not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 