<?php
/**
 * Add Review API Endpoint
 * 
 * This endpoint handles the submission of new reviews in the GearSphere system.
 * Users can submit reviews for various targets including products, technicians,
 * and system feedback with ratings and comments.
 * 
 * HTTP Method: POST
 * Content-Type: application/json
 * 
 * Request Body Parameters:
 * - user_id (int, required): ID of the user submitting the review
 * - target_type (string, required): Type of entity being reviewed ('product', 'technician', 'system')
 * - target_id (int, optional): ID of the target entity (null for system reviews)
 * - rating (int, required): Rating score (typically 1-5 scale)
 * - comment (string, optional): Review comment text
 * 
 * Response Format:
 * Success (200): {"success": true, "review_id": <int>}
 * Error (400): {"error": "Missing required fields"}
 * Error (405): {"error": "Method not allowed"}
 * Error (500): {"error": "<error_message>"}
 * 
 * Features:
 * - Input validation for required fields
 * - Support for system feedback without target_id
 * - Comprehensive error handling and logging
 * - JSON response format
 * - CORS support for cross-origin requests
 * 
 */

require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Review.php';
header('Content-Type: application/json');

// ==========================================
// DEBUG CONFIGURATION
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to file for debugging and monitoring
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// ==========================================
// HTTP METHOD VALIDATION
// ==========================================
// Only accept POST requests for review submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ==========================================
// INPUT DATA PROCESSING
// ==========================================
// Parse JSON input data or fall back to POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

// Extract and validate required parameters
$user_id = $data['user_id'] ?? null;
$target_type = $data['target_type'] ?? null;
$target_id = isset($data['target_id']) ? $data['target_id'] : null;
$rating = $data['rating'] ?? null;
$comment = $data['comment'] ?? '';

// ==========================================
// INPUT VALIDATION
// ==========================================
// Validate that all required fields are provided
if (!$user_id || !$target_type || !$rating) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Special handling for system feedback reviews
// System reviews don't require a specific target_id
if ($target_type === 'system') {
    $target_id = null;
}

// ==========================================
// REVIEW PROCESSING
// ==========================================
try {
    // Create new review instance and add review to database
    $review = new Review();
    $id = $review->addReview($user_id, $target_type, $target_id, $rating, $comment);
    
    // Return success response with the new review ID
    echo json_encode(['success' => true, 'review_id' => $id]);
    
} catch (Exception $e) {
    // Handle any errors during review creation
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
