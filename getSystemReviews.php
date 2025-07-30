<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once 'DbConnector.php';

try {
    $pdo = (new DBConnector())->connect();

    // Get last 8 approved system reviews with user details
    $reviewsQuery = "SELECT r.*, u.name as username, u.profile_image 
                     FROM reviews r 
                     JOIN users u ON r.user_id = u.user_id 
                     WHERE r.target_type = 'system' AND r.status = 'approved' 
                     ORDER BY r.created_at DESC 
                     LIMIT 8";
    $stmt = $pdo->prepare($reviewsQuery);
    $stmt->execute();

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

    $response = [
        'success' => true,
        'reviews' => $reviews,
        'total_reviews' => count($reviews)
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch system reviews',
        'error' => $e->getMessage()
    ]);
}
