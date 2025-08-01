<?php
/**
 * Admin Notification Management API Endpoint
 * 
 * This script handles admin notification operations including:
 * - Retrieving all admin notifications
 * - Getting notification count
 * - Deleting specific notifications
 * - Admin authentication verification
 * 
 * Methods: GET, DELETE
 * Authentication: Required (admin session)
 * GET: Returns notifications list or count
 * DELETE: Removes specific notification
 */

// Initialize CORS configuration and session management
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

// Set JSON response header
header('Content-Type: application/json');
include_once 'Main Classes/Notification.php';

// Verify admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Create Notification object for database operations
$notification = new Notification();

// Handle DELETE request to remove a notification
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse JSON input from request body
    $input = json_decode(file_get_contents('php://input'), true);
    $notification_id = $input['notification_id'] ?? null;
    
    // Validate required notification ID
    if (!$notification_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing notification_id.']);
        exit;
    }
    
    // Delete the notification and return result
    $success = $notification->deleteNotification($notification_id, $_SESSION['user_id']);
    echo json_encode(['success' => $success]);
    exit;
}

// Handle GET request with 'count' parameter to get notification count
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['count'])) {
    $count = $notification->getNotificationCount($_SESSION['user_id']);
    echo json_encode(['count' => $count]);
    exit;
}

// Handle GET request to retrieve all notifications
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Fetch all notifications for the admin user
        $notifications = $notification->getNotifications($_SESSION['user_id']);
        echo json_encode(['notifications' => $notifications]);
    } catch (Exception $e) {
        // Handle database errors
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch notifications.', 'details' => $e->getMessage()]);
    }
    exit;
}

// Return error for unsupported HTTP methods
http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
?>