<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "Main Classes/Compare_product.php";
require_once "DbConnector.php";

try {
    $db = new DBConnector();
    $pdo = $db->connect();
    
    $compareProduct = new Compare_product($pdo);

    // Check if a specific product ID is requested
    if (isset($_GET['id'])) {
        $productId = (int)$_GET['id'];
        $operatingSystem = $compareProduct->getOperatingSystemById($productId);

        if ($operatingSystem) {
            echo json_encode([
                'success' => true,
                'data' => $operatingSystem
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Operating System not found'
            ]);
        }
    } else {
        // Fetch all operating systems
        $operatingSystems = $compareProduct->getAllOperatingSystemsWithDetails();

        echo json_encode([
            'success' => true,
            'data' => $operatingSystems
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
