<?php
/**
 * Order Assignment Update API Endpoint
 * 
 * This endpoint handles updating the assignment of orders to specific technicians
 * in the GearSphere system. It allows for reassigning orders to different
 * technicians based on availability or requirements.
 * 
 * HTTP Method: POST
 * Content-Type: application/json
 * Authentication: Required (Session-based)
 * 
 * JSON Parameters:
 * - order_id (int, required): The ID of the order to update assignment for
 * - assignment_id (int, required): The ID of the new technician assignment
 * 
 * Response Format:
 * Success (200): {"success": true}
 * Error (400): {"success": false, "message": "Missing order_id or assignment_id."}
 * Error (500): {"success": false, "message": "Failed to update order assignment."}
 * 
 * Features:
 * - Order-to-technician assignment management
 * - Assignment validation and updates
 * - Simple JSON-based communication
 * - Error handling for missing parameters
 */

require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Orders.php';

// ==========================================
// REQUEST DATA EXTRACTION & VALIDATION
// ==========================================

// Parse JSON request body
$data = json_decode(file_get_contents('php://input'), true);

// Extract required parameters
$order_id = isset($data['order_id']) ? (int)$data['order_id'] : null;
$assignment_id = isset($data['assignment_id']) ? (int)$data['assignment_id'] : null;

// Validate required parameters
if (!$order_id || !$assignment_id) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id or assignment_id.']);
    exit;
}

// ==========================================
// ORDER ASSIGNMENT UPDATE PROCESSING
// ==========================================

// Create Orders object and update assignment
$orderObj = new Orders();
$success = $orderObj->updateAssignment($order_id, $assignment_id);

// ==========================================
// RESPONSE HANDLING
// ==========================================

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order assignment.']);
}
