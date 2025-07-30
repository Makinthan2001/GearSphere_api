<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once "Main Classes/Compare_product.php";

try {
    $compareProduct = new Compare_product();
    $storage = $compareProduct->getAllStorageWithDetails();

    echo json_encode([
        "success" => true,
        "data" => $storage
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch storage: " . $e->getMessage()
    ]);
}
