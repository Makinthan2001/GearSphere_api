<?php

/**
 * Review Deletion API Endpoint
 * 
 * This endpoint handles deleting customer reviews from the GearSphere system.
 * It validates review IDs, processes deletion requests, and manages review
 * moderation with proper error handling and response formatting.
 * 
 * @method POST
 * @endpoint /deleteReview.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');

// Import Review class for review operations
require_once __DIR__ . '/Main Classes/Review.php';

// ====================================================================
// REQUEST METHOD VALIDATION
// ====================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Support both JSON and form-encoded data for flexibility
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

// Extract review ID to delete
$id = $data['id'] ?? null;

// Validate required review ID
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing review id']);
    exit;
}

try {
    // ====================================================================
    // PROCESS REVIEW DELETION
    // ====================================================================
    
    $review = new Review();
    $affected = $review->deleteReview($id);
    
    if ($affected) {
        // Review deletion successful
        echo json_encode(['success' => true]);
    } else {
        // Review not found
        http_response_code(404);
        echo json_encode(['error' => 'Review not found']);
    }
} catch (Exception $e) {
    // Handle any errors in review deletion
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
