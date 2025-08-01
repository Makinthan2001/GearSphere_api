<?php
/**
 * Get Build Requests API Endpoint
 * 
 * This script retrieves build requests assigned to a specific technician.
 * It handles:
 * - Technician ID validation from GET parameters
 * - Build request data retrieval for assigned technician
 * - Error handling for invalid inputs and database errors
 * 
 * Method: GET
 * Required parameter: technician_id
 * Returns: Array of build requests assigned to the technician
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');
require_once __DIR__ . '/Main Classes/technician.php';
require_once __DIR__ . '/DbConnector.php';

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Extract and validate technician ID from GET parameters
$technician_id = isset($_GET['technician_id']) ? intval($_GET['technician_id']) : null;
if (!$technician_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid technician_id']);
    exit;
}

try {
    // Create Technician object and retrieve build requests
    $tech = new technician();
    $requests = $tech->getBuildRequests($technician_id);
    
    // Return successful response with build requests data
    echo json_encode(['success' => true, 'data' => $requests]);
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
