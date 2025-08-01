<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Compare_product.php';

try {
    $product = new Compare_product();
    $monitors = $product->getAllMonitorsWithDetails();
    echo json_encode([
        'success' => true,
        'data' => $monitors
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
