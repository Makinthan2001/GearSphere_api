<?php
/**
 * Customer Notification Management API Endpoint
 * 
 * This script handles customer notification operations including:
 * - Retrieving customer notifications
 * - Getting notification count for a specific user
 * - Deleting specific notifications
 * - Multiple HTTP method support (GET, DELETE)
 * 
 * Methods: GET, DELETE
 * GET with user_id: Returns all notifications for user
 * GET with user_id & count: Returns notification count only
 * DELETE: Removes specific notification
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');
include_once 'Main Classes/Notification.php';

// Create Notification object for database operations
$notification = new Notification();

// Handle DELETE request to remove a notification
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse JSON input from request body
    $input = json_decode(file_get_contents('php://input'), true);
    $notification_id = $input['notification_id'] ?? null;
    $user_id = $input['user_id'] ?? null;
    
    // Validate required parameters
    if (!$notification_id || !$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing notification_id or user_id.']);
        exit;
    }
    
    // Delete the notification and return result
    $success = $notification->deleteNotification($notification_id, $user_id);
    echo json_encode(['success' => $success]);
    exit;
}

// Handle GET request with count parameter to get notification count only
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id']) && isset($_GET['count'])) {
    $user_id = intval($_GET['user_id']);
    $count = $notification->getNotificationCount($user_id);
    echo json_encode(['count' => $count]);
    exit;
}

// Handle GET request to retrieve all notifications for a user
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    try {
        // Fetch all notifications for the specified user
        $notifications = $notification->getNotifications($user_id);
        echo json_encode(['notifications' => $notifications]);
    } catch (Exception $e) {
        // Handle database errors
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch notifications.', 'details' => $e->getMessage()]);
    }
    exit;
}

// Return error for unsupported HTTP methods or missing parameters
http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
