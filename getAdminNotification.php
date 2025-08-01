<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

header('Content-Type: application/json');
include_once 'Main Classes/Notification.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$notification = new Notification();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse input for DELETE
    $input = json_decode(file_get_contents('php://input'), true);
    $notification_id = $input['notification_id'] ?? null;
    if (!$notification_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing notification_id.']);
        exit;
    }
    $success = $notification->deleteNotification($notification_id, $_SESSION['user_id']);
    echo json_encode(['success' => $success]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['count'])) {
    $count = $notification->getNotificationCount($_SESSION['user_id']);
    echo json_encode(['count' => $count]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $notifications = $notification->getNotifications($_SESSION['user_id']);
        echo json_encode(['notifications' => $notifications]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch notifications.', 'details' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
?>