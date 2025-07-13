<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
header('Content-Type: application/json');
require_once __DIR__ . '/Main Classes/Message.php';

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