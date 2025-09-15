<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once 'DbConnector.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['product_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }

    $product_id = intval($_GET['product_id']);
    $database = new DBConnector();
    $conn = $database->connect();

    // Get Operating System specifications
    $stmt = $conn->prepare("SELECT * FROM operating_system WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $specs = $stmt->fetch();

    if (!$specs) {
        echo json_encode([
            'success' => false,
            'message' => 'Operating System specifications not found'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'specs' => $specs
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
