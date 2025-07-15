<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include_once 'Main Classes/Notification.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$notification = new Notification();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse input for DELETE
    $input = json_decode(file_get_contents('php://input'), true);
    $notification_id = $input['notification_id'] ?? null;
    $user_id = $input['user_id'] ?? null;
    if (!$notification_id || !$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing notification_id or user_id.']);
        exit;
    }
    $success = $notification->deleteNotification($notification_id, $user_id);
    echo json_encode(['success' => $success]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id']) && isset($_GET['count'])) {
    $user_id = intval($_GET['user_id']);
    $count = $notification->getNotificationCount($user_id);
    echo json_encode(['count' => $count]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    try {
        $notifications = $notification->getNotifications($user_id);
        echo json_encode(['notifications' => $notifications]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch notifications.', 'details' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']); 