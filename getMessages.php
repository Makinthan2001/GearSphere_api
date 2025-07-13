<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
header('Content-Type: application/json');
require_once __DIR__ . '/Main Classes/Message.php';

$msgObj = new Message();
$messages = $msgObj->getAllMessages();
echo json_encode($messages); 