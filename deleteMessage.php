<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Message.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support JSON input
    if (strpos($_SERVER["CONTENT_TYPE"] ?? '', "application/json") === 0) {
        $input = json_decode(file_get_contents("php://input"), true);
        $message_id = $input['message_id'] ?? null;
    } else {
        $message_id = $_POST['message_id'] ?? null;
    }
    if ($message_id) {
        $msgObj = new Message();
        $result = $msgObj->deleteMessage($message_id);
        echo json_encode($result);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'message_id is required.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
