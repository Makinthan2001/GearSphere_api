<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

header('Content-Type: application/json');
require_once __DIR__ . '/Main Classes/Notification.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$user_id = $data['user_id'] ?? null;
$message = $data['message'] ?? null;

if (!$user_id || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID and message are required.']);
    exit;
}

// Verify that the logged-in user matches the user_id or is an admin
if ($_SESSION['user_id'] != $user_id && $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden. You can only add notifications for yourself.']);
    exit;
}

try {
    $notification = new Notification();
    $success = $notification->addUniqueNotification($user_id, $message, 24); // 24-hour window to prevent duplicates
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Notification processed successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add notification.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>