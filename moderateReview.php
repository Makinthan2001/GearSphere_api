<?php

/**
 * Review Moderation API Endpoint
 * 
 * This endpoint handles review moderation functionality for the GearSphere system.
 * It allows administrators to approve or reject customer reviews, managing
 * content quality and appropriateness with proper validation and error handling.
 * 
 * @method POST
 * @endpoint /moderateReview.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

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

// Extract moderation parameters
$id = $data['id'] ?? null;           // Review ID to moderate
$status = $data['status'] ?? null;   // New status: 'approved' or 'rejected'

// Validate required fields and status values
if (!$id || !in_array($status, ['approved', 'rejected'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid fields']);
    exit;
}

try {
    // ====================================================================
    // PROCESS REVIEW MODERATION
    // ====================================================================
    
    $review = new Review();
    $affected = $review->updateReviewStatus($id, $status);
    
    if ($affected) {
        // Review moderation successful
        echo json_encode(['success' => true]);
    } else {
        // Review not found
        http_response_code(404);
        echo json_encode(['error' => 'Review not found']);
    }
} catch (Exception $e) {
    // Handle any errors in review moderation
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
