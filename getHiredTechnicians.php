<?php
require_once 'corsConfig.php';
initializeEndpoint();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// ✅ FIXED: DbConnector is in the same folder
require_once(__DIR__ . '/DbConnector.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing user_id"]);
    exit;
}

try {
    $db = new DBConnector();
    $pdo = $db->connect();

    $sql = "SELECT t.technician_id AS id, u.name
            FROM technician_assignments ta
            JOIN technician t ON ta.technician_id = t.technician_id
            JOIN users u ON t.user_id = u.user_id
            WHERE ta.customer_id = ? AND ta.status = 'accepted'
            GROUP BY t.technician_id, u.name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
