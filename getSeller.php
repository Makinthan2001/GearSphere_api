<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once 'sessionConfig.php';
require_once 'Main Classes/Seller.php';

try {
    // Get user_id from session
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
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
