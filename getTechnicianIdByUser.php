<?php
/**
 * Get Technician ID by User API Endpoint
 * 
 * This script retrieves the technician_id associated with a specific user_id.
 * It handles:
 * - User ID validation and lookup
 * - Database query to find technician mapping
 * - Error handling for users who aren't technicians
 * - Simple ID translation service
 * 
 * Method: GET
 * Required parameter: user_id
 * Returns: Technician ID for the specified user
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');

// Include database connector for direct queries
require_once './DbConnector.php';

// Extract and validate user_id parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "user_id is required"]);
    exit();
}

try {
    // Establish database connection
    $db = new DBConnector();
    $pdo = $db->connect();
    
    // Query to find technician_id for the given user_id
    $stmt = $pdo->prepare("SELECT technician_id FROM technician WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if technician record exists for this user
    if ($row && isset($row['technician_id'])) {
        // Return successful response with technician_id
        echo json_encode(["success" => true, "technician_id" => $row['technician_id']]);
    } else {
        // Return error if user is not a technician
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Technician not found for this user_id"]);
    }
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
