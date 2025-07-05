<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'Main Classes/Admin.php';

try {
    // Get user_id from query parameters
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID is required']);
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
?> 