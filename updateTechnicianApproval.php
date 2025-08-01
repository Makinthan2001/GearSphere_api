<?php
/**
 * Update Technician Approval Status API Endpoint
 * 
 * This script allows administrators to approve or reject technician applications.
 * It handles:
 * - Admin authentication verification
 * - Technician existence validation
 * - Approval status updates (approved/not approved)
 * - Audit logging for approval actions
 * 
 * Method: POST
 * Authentication: Required (admin session)
 * Required JSON body: technician_id, approve_status
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include database connection class
require_once __DIR__ . '/DbConnector.php';

// Set response content type to JSON
header("Content-Type: application/json");

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Debug logging for session information (remove in production)
error_log("Session data: " . print_r($_SESSION, true));
error_log("User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
error_log("User Type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'not set'));

// Authentication bypass flag for testing (REMOVE IN PRODUCTION)
$skip_auth = false; // Set to true for testing without login

// Verify admin authentication (skip if testing flag is set)
if (!$skip_auth && (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin')) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'debug_info' => [
            'session_user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'session_user_type' => isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null,
            'session_id' => session_id()
        ]
    ]);
    exit;
}

// Parse JSON request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
if (!isset($data['technician_id']) || !isset($data['approve_status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Extract and validate input data
$technician_id = intval($data['technician_id']);
$approve_status = $data['approve_status'];

// Validate approval status values
if (!in_array($approve_status, ['approved', 'not approved'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid approval status']);
    exit;
}

try {
    // Establish database connection
    $pdo = (new DBConnector())->connect();

    // Check if the technician exists and get their details
    $checkStmt = $pdo->prepare("
        SELECT t.technician_id, u.name, u.email 
        FROM technician t 
        JOIN users u ON t.user_id = u.user_id 
        WHERE t.user_id = ?
    ");
    $checkStmt->execute([$technician_id]);
    $technician = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // Return error if technician not found
    if (!$technician) {
        echo json_encode(['success' => false, 'message' => 'Technician not found']);
        exit;
    }

    // Update the technician's approval status
    $updateStmt = $pdo->prepare("
        UPDATE technician 
        SET approve_status = ? 
        WHERE user_id = ?
    ");

    $result = $updateStmt->execute([$approve_status, $technician_id]);

    // Handle successful update
    if ($result) {
        // Log the approval action for audit purposes
        $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'unknown';
        error_log("Admin " . $admin_id . " updated technician " . $technician_id . " approval status to: " . $approve_status);

        // Return success response with technician details
        echo json_encode([
            'success' => true,
            'message' => 'Technician approval status updated successfully',
            'technician_name' => $technician['name'],
            'new_status' => $approve_status
        ]);
    } else {
        // Return error if database update failed
        echo json_encode(['success' => false, 'message' => 'Failed to update approval status']);
    }
} catch (Exception $e) {
    // Handle database errors and log them
    error_log("Error updating technician approval: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
