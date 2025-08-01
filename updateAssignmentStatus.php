<?php
/**
 * Technician Assignment Status Update API Endpoint
 * 
 * This endpoint handles updating the status of technician assignments for build requests
 * in the GearSphere system. It processes status changes, sends email notifications to
 * customers, and creates in-app notifications about assignment updates.
 * 
 * HTTP Method: POST
 * Content-Type: application/json
 * Authentication: Required (Session-based)
 * 
 * JSON Parameters:
 * - assignment_id (int, required): The ID of the technician assignment to update
 * - status (string, required): The new status for the assignment (e.g., "accepted", "completed", "declined")
 * 
 * Response Format:
 * Success (200): {"success": true}
 * Error (400): {"success": false, "message": "Missing assignment_id or status."}
 * Error (500): {"success": false, "message": "Internal server error: <error details>"}
 * 
 * Features:
 * - Assignment status tracking and updates
 * - Automated email notifications to customers
 * - In-app notification system
 * - Customer and technician data retrieval
 * - Comprehensive error handling and logging
 * 
 * Email Notifications:
 * - Sends build request status update emails to customers
 * - Includes technician name and status information
 * - Uses professional email templates
 */

require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

require_once __DIR__ . '/Main Classes/Technician.php';
require_once __DIR__ . '/Main Classes/Notification.php';
require_once __DIR__ . '/Main Classes/Mailer.php';

// ==========================================
// REQUEST DATA EXTRACTION & VALIDATION
// ==========================================

// Parse JSON request body
$data = json_decode(file_get_contents('php://input'), true);

// Extract assignment parameters
$assignment_id = isset($data['assignment_id']) ? (int)$data['assignment_id'] : null;
$status = isset($data['status']) ? trim($data['status']) : '';

// Validate required parameters
if (!$assignment_id || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing assignment_id or status.']);
    exit;
}

try {
    // ==========================================
    // DATABASE CONNECTION & STATUS UPDATE
    // ==========================================
    
    require_once __DIR__ . '/DbConnector.php';
    $pdo = (new DBConnector())->connect();
    
    // Update the assignment status in database
    $stmt = $pdo->prepare("UPDATE technician_assignments SET status = :status WHERE assignment_id = :assignment_id");
    $stmt->execute([':status' => $status, ':assignment_id' => $assignment_id]);
    
    // ==========================================
    // FETCH ASSIGNMENT DETAILS FOR NOTIFICATIONS
    // ==========================================
    
    // Get comprehensive assignment details including customer and technician information
    $stmt = $pdo->prepare("
        SELECT ta.*, u.name as customer_name, u.email as customer_email, u.user_id as customer_id,
               tech_user.name as technician_name
        FROM technician_assignments ta 
        JOIN users u ON ta.customer_id = u.user_id 
        JOIN technician t ON ta.technician_id = t.technician_id
        JOIN users tech_user ON t.user_id = tech_user.user_id
        WHERE ta.assignment_id = :assignment_id
    ");
    $stmt->execute([':assignment_id' => $assignment_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ==========================================
    // NOTIFICATION SYSTEM PROCESSING
    // ==========================================
    
    if ($customer && !empty($customer['customer_email'])) {
        // Send professional email notification to customer
        $mailer = new Mailer();
        $mailer->sendBuildRequestStatusEmail(
            $customer['customer_email'],
            $customer['customer_name'],
            $status,
            $customer['technician_name']
        );
        $mailer->send();
        
        // Create in-app notification for customer dashboard
        $notif = new Notification();
        $notif->addNotification(
            $customer['customer_id'], 
            "Your request was $status by technician: " . $customer['technician_name'] . "."
        );
    }

    // Return success response
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // ==========================================
    // ERROR HANDLING & LOGGING
    // ==========================================
    
    http_response_code(500);
    error_log("updateAssignmentStatus Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>
