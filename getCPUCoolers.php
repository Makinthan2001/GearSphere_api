<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

header('Content-Type: application/json');
require_once __DIR__ . '/Main Classes/Compare_product.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $product = new Compare_product();
    $cpuCoolers = $product->getAllCPUCoolersWithDetails();
    echo json_encode([
        'success' => true,
        'data' => $cpuCoolers
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
