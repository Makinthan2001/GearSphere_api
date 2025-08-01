<?php
/**
 * Get Hired Technicians API Endpoint
 * 
 * This script retrieves technicians that have been hired (accepted assignments) by a specific customer.
 * It handles:
 * - Customer-specific hired technician retrieval
 * - Validation of user_id parameter
 * - Database query with joins across multiple tables
 * - Error handling and logging
 * 
 * Method: GET
 * Required parameter: user_id (customer ID)
 * Returns: Array of hired technicians with ID and name
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Enable comprehensive error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Include database connector from same directory
require_once(__DIR__ . '/DbConnector.php');

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Extract and validate user_id parameter
$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing user_id"]);
    exit;
}

try {
    // Establish database connection
    $db = new DBConnector();
    $pdo = $db->connect();

    // Query to get hired technicians for the specific customer
    // Joins technician_assignments, technician, and users tables
    // Filters for accepted assignments only and groups by technician
    $sql = "SELECT t.technician_id AS id, u.name
            FROM technician_assignments ta
            JOIN technician t ON ta.technician_id = t.technician_id
            JOIN users u ON t.user_id = u.user_id
            WHERE ta.customer_id = ? AND ta.status = 'accepted'
            GROUP BY t.technician_id, u.name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the list of hired technicians
    echo json_encode($result);
} catch (Exception $e) {
    // Handle database or system errors
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
