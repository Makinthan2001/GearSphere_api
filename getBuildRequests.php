<?php
require_once 'corsConfig.php';
initializeEndpoint();

header('Content-Type: application/json');
require_once __DIR__ . '/Main Classes/technician.php';
require_once __DIR__ . '/DbConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$technician_id = isset($_GET['technician_id']) ? intval($_GET['technician_id']) : null;
if (!$technician_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid technician_id']);
    exit;
}

try {
    $tech = new technician();
    $requests = $tech->getBuildRequests($technician_id);
    echo json_encode(['success' => true, 'data' => $requests]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
