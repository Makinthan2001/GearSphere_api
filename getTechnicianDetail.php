<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Technician.php';
require_once './DbConnector.php';

$technician_id = isset($_GET['technician_id']) ? intval($_GET['technician_id']) : null;

if (!$technician_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Technician ID is required"]);
    exit();
}

// Fetch user_id for the technician
try {
    $db = new DBConnector();
    $pdo = $db->connect();
    $stmt = $pdo->prepare("SELECT user_id FROM technician WHERE technician_id = :technician_id");
    $stmt->execute([':technician_id' => $technician_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Technician not found"]);
        exit();
    }
    $user_id = $row['user_id'];
    $technicianDetail = new Technician();
    $result = $technicianDetail->getTechnicianDetails($user_id);
    if ($result) {
        http_response_code(200);
        echo json_encode(["success" => true, "technician" => $result]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Technician details not found"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
