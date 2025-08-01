<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Message.php';
require_once __DIR__ . '/DbConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support JSON input
    if (strpos($_SERVER["CONTENT_TYPE"] ?? '', "application/json") === 0) {
        $input = json_decode(file_get_contents("php://input"), true);
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $subject = trim($input['subject'] ?? '');
        $message = trim($input['message'] ?? '');
    } else {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    }

    if ($name && $email && $subject && $message) {
        $msgObj = new Message();
        $result = $msgObj->addMessage($name, $email, $subject, $message);
        
        if ($result && isset($result['success']) && $result['success']) {
            // Create notification for admin when new message is received
            require_once __DIR__ . '/Main Classes/Notification.php';
            
            // Get admin user ID (assuming admin has user_id = 28, or we can query for admin)
            try {
                $db = new DBConnector();
                $pdo = $db->connect();
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_type = 'admin' LIMIT 1");
                $stmt->execute();
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin) {
                    $adminId = $admin['user_id'];
                    $notificationMessage = "New Message Received!\n\nFrom: $name ($email)\nSubject: $subject\n\nMessage: " . substr($message, 0, 100) . (strlen($message) > 100 ? "..." : "");
                    
                    $notification = new Notification();
                    $notification->addUniqueNotification($adminId, $notificationMessage, 1); // 1-hour window for message notifications
                }
            } catch (Exception $e) {
                // Log error but don't fail the message creation
                error_log("Failed to create admin notification: " . $e->getMessage());
            }
        }
        
        echo json_encode($result);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
