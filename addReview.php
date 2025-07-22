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

require_once __DIR__ . '/Main Classes/Review.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$user_id = $data['user_id'] ?? null;
$target_type = $data['target_type'] ?? null;
$target_id = isset($data['target_id']) ? $data['target_id'] : null;
$rating = $data['rating'] ?? null;
$comment = $data['comment'] ?? '';

if (!$user_id || !$target_type || !$rating) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// For system feedback, allow target_id to be null
if ($target_type === 'system') {
    $target_id = null;
}

try {
    $review = new Review();
    $id = $review->addReview($user_id, $target_type, $target_id, $rating, $comment);
    echo json_encode(['success' => true, 'review_id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}