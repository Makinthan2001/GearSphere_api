<?php
require_once 'corsConfig.php';
initializeEndpoint();

header('Content-Type: application/json');

require_once './DbConnector.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "user_id is required"]);
    exit();
}

try {
    $db = new DBConnector();
    $pdo = $db->connect();
    $stmt = $pdo->prepare("SELECT technician_id FROM technician WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['technician_id'])) {
        echo json_encode(["success" => true, "technician_id" => $row['technician_id']]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Technician not found for this user_id"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
