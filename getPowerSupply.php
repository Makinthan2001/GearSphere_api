<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once "Main Classes/Compare_product.php";

try {
    $compareProduct = new Compare_product();
    $psus = $compareProduct->getAllPowerSuppliesWithDetails();

    echo json_encode([
        "success" => true,
        "data" => $psus
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch power supplies: " . $e->getMessage()
    ]);
}
