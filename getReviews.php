<?php
/**
 * Get Reviews API Endpoint
 * 
 * This script retrieves reviews with optional filtering capabilities.
 * It handles:
 * - Review data retrieval with multiple filter options
 * - Dynamic query building based on provided parameters
 * - Error handling for database issues
 * - JSON response formatting
 * 
 * Method: GET
 * Optional parameters: user_id, target_type, target_id, status
 * Returns: Array of reviews matching the specified filters
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');

// Include the Review class for database operations
require_once __DIR__ . '/Main Classes/Review.php';

// Build filters array based on provided GET parameters
$filters = [];
if (isset($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];           // Filter by specific user
if (isset($_GET['target_type'])) $filters['target_type'] = $_GET['target_type']; // Filter by review target type (product, seller, etc.)
if (isset($_GET['target_id'])) $filters['target_id'] = $_GET['target_id'];     // Filter by specific target ID
if (isset($_GET['status'])) $filters['status'] = $_GET['status'];             // Filter by review status (approved, pending, etc.)

try {
    // Create Review object for database operations
    $review = new Review();
    
    // Retrieve reviews based on applied filters
    $reviews = $review->getReviews($filters);
    
    // Return filtered reviews data
    echo json_encode($reviews);
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
