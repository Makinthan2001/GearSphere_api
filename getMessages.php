<?php
require_once 'corsConfig.php';
initializeEndpoint();
header('Content-Type: application/json');
require_once __DIR__ . '/Main Classes/Message.php';

$msgObj = new Message();
$messages = $msgObj->getAllMessages();
echo json_encode($messages);
