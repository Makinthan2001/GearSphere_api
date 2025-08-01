<?php

/**
 * Add Notification API Endpoint
 * 
 * This endpoint handles adding notifications to users in the GearSphere system.
 * It validates user authentication, processes notification creation with duplicate
 * prevention, and manages notification delivery with proper security checks.
 * 
 * @method POST
 * @endpoint /addNotification.php
 * @param int $user_id Target user ID for the notification
 * @param string $message Notification message content
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

// Set JSON response header
header('Content-Type: application/json');

// Import Notification class for notification operations
require_once __DIR__ . '/Main Classes/Notification.php';

// ====================================================================
// AUTHENTICATION CHECK - Verify user session
// ====================================================================

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit;
}

// ====================================================================
// REQUEST METHOD VALIDATION
// ====================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Get JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);

// Extract notification parameters
$user_id = $data['user_id'] ?? null;    // Target user ID for notification
$message = $data['message'] ?? null;     // Notification message content

// Validate required fields
if (!$user_id || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID and message are required.']);
    exit;
}

// ====================================================================
// AUTHORIZATION CHECK - Verify notification permissions
// ====================================================================

// Verify that the logged-in user matches the target user_id or is an admin
// This prevents users from creating notifications for other users
if ($_SESSION['user_id'] != $user_id && $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden. You can only add notifications for yourself.']);
    exit;
}

try {
    // ====================================================================
    // PROCESS NOTIFICATION CREATION
    // ====================================================================
    
    $notification = new Notification();
    
    // Add notification with 24-hour duplicate prevention window
    // This prevents spam notifications with the same content
    $success = $notification->addUniqueNotification($user_id, $message, 24);
    
    if ($success) {
        // Notification added successfully
        echo json_encode(['success' => true, 'message' => 'Notification processed successfully.']);
    } else {
        // Notification creation failed (possibly duplicate)
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add notification.']);
    }
} catch (Exception $e) {
    // Handle any errors in notification processing
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>