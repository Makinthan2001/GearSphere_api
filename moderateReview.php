<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Review.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !in_array($status, ['approved', 'rejected'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid fields']);
    exit;
}

try {
    $review = new Review();
    $affected = $review->updateReviewStatus($id, $status);
    if ($affected) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Review not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
