<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once __DIR__ . '/Main Classes/Review.php';

$filters = [];
if (isset($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
if (isset($_GET['target_type'])) $filters['target_type'] = $_GET['target_type'];
if (isset($_GET['target_id'])) $filters['target_id'] = $_GET['target_id'];
if (isset($_GET['status'])) $filters['status'] = $_GET['status'];

try {
    $review = new Review();
    $reviews = $review->getReviews($filters);
    echo json_encode($reviews);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
