<?php
/**
 * Get Technician Detail API Endpoint
 * 
 * This script retrieves detailed information for a specific technician.
 * It handles:
 * - Technician ID validation and user lookup
 * - Database queries to fetch technician details
 * - Error handling for missing technicians
 * - Complete technician profile data retrieval
 * 
 * Method: GET
 * Required parameter: technician_id
 * Returns: Detailed technician information
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include required classes for technician operations
require_once './Main Classes/Technician.php';
require_once './DbConnector.php';

// Extract and validate technician_id parameter
$technician_id = isset($_GET['technician_id']) ? intval($_GET['technician_id']) : null;

if (!$technician_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Technician ID is required"]);
    exit();
}

// Fetch technician details with user_id lookup
try {
    // Establish database connection
    $db = new DBConnector();
    $pdo = $db->connect();
    
    // First, get the user_id associated with the technician_id
    $stmt = $pdo->prepare("SELECT user_id FROM technician WHERE technician_id = :technician_id");
    $stmt->execute([':technician_id' => $technician_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if technician exists
    if (!$row) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Technician not found"]);
        exit();
    }
    
    // Get user_id for detailed technician information retrieval
    $user_id = $row['user_id'];
    
    // Create Technician object and fetch complete details
    $technicianDetail = new Technician();
    $result = $technicianDetail->getTechnicianDetails($user_id);
    
    if ($result) {
        // Return successful response with technician details
        http_response_code(200);
        echo json_encode(["success" => true, "technician" => $result]);
    } else {
        // Return error if technician details not found
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Technician details not found"]);
    }
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
