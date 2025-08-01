<?php
require_once 'sessionConfig.php';
require_once 'corsConfig.php';
initializeEndpoint();

require_once 'Main Classes/Admin.php';

try {
    // Get user_id from session
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }

    // Create Admin object
    $admin = new Admin();

    // Get admin data
    $adminData = $admin->getDetails($user_id);

    if ($adminData) {
        // Check if user is admin
        if ($adminData['user_type'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Admin privileges required.']);
            exit;
        }

        // Remove sensitive data
        unset($adminData['password']);

        echo json_encode($adminData);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Admin not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
