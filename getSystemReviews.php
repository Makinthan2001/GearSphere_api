<?php
/**
 * Get System Reviews API Endpoint
 * 
 * This script retrieves approved system reviews for display on the website.
 * It handles:
 * - Approved system review retrieval with user information
 * - User profile data inclusion (name, profile image)
 * - Limited result set for performance (8 most recent)
 * - Complete review data formatting
 * 
 * Method: GET
 * Returns: Array of approved system reviews with user details
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include database connector for direct queries
require_once 'DbConnector.php';

try {
    // Establish database connection
    $pdo = (new DBConnector())->connect();

    // Get last 8 approved system reviews with user details
    // Joins reviews and users tables to get complete review information
    // Filters for system reviews only and approved status
    $reviewsQuery = "SELECT r.*, u.name as username, u.profile_image 
                     FROM reviews r 
                     JOIN users u ON r.user_id = u.user_id 
                     WHERE r.target_type = 'system' AND r.status = 'approved' 
                     ORDER BY r.created_at DESC 
                     LIMIT 8";
    $stmt = $pdo->prepare($reviewsQuery);
    $stmt->execute();

    // Format review data for frontend consumption
    $reviews = [];
    while ($row = $stmt->fetch()) {
        $reviews[] = [
            'review_id' => $row['id'],
            'user_id' => $row['user_id'],
            'username' => $row['username'],
            'profile_image' => $row['profile_image'],
            'rating' => $row['rating'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at'],
            'target_type' => $row['target_type'],
            'status' => $row['status']
        ];
    }

    // Compile response with review data and metadata
    $response = [
        'success' => true,
        'reviews' => $reviews,
        'total_reviews' => count($reviews)
    ];

    echo json_encode($response);
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch system reviews',
        'error' => $e->getMessage()
    ]);
}
