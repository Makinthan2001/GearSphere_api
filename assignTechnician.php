<?php

/**
 * Technician Assignment API Endpoint
 * 
 * This endpoint handles assigning technicians to customer build requests in the GearSphere system.
 * It creates assignments, sends email notifications, and manages the technician-customer
 * relationship for custom PC build services.
 * 
 * @method POST
 * @endpoint /assignTechnician.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

// Import required classes for assignment processing
require_once './Main Classes/technician.php';
require_once './Main Classes/Mailer.php';
require_once './Main Classes/Notification.php';

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Get JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);

// Extract assignment parameters
$customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
$technician_id = isset($data['technician_id']) ? (int)$data['technician_id'] : null;
$instructions = isset($data['instructions']) ? trim($data['instructions']) : '';

// Validate required parameters
if (!$customer_id || !$technician_id) {
    echo json_encode(['success' => false, 'message' => 'Missing customer_id or technician_id.']);
    exit;
}

// ====================================================================
// CREATE TECHNICIAN ASSIGNMENT
// ====================================================================

$tech = new Technician();
$result = $tech->assignTechnician($customer_id, $technician_id, $instructions);

if ($result && isset($result['assignment_id'])) {
    // ====================================================================
    // FETCH PARTICIPANT DETAILS FOR NOTIFICATIONS
    // ====================================================================
    
    // Get technician details for email notification
    $technician = $tech->getTechnicianByTechnicianId($technician_id);
    
    // Fetch customer details for assignment information
    $customerDetails = $tech->getDetails($customer_id);
    $customerName = $customerDetails['name'] ?? '';
    $customerEmail = $customerDetails['email'] ?? '';
    
    if ($technician && !empty($technician['email'])) {
        // ====================================================================
        // SEND EMAIL NOTIFICATION TO TECHNICIAN
        // ====================================================================
        
        $mailer = new Mailer();
        
        // Create comprehensive assignment details for email template
        $assignmentDetails = [
            'assignment_id' => $result['assignment_id'],
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'instructions' => $instructions,
            'date' => date('F j, Y')
        ];
        
        // Send professional assignment notification email
        $mailer->sendTechnicianAssignmentEmail(
            $technician['email'], 
            $technician['name'], 
            $assignmentDetails
        );
        
        $mailer->send();
        
        // ====================================================================
        // CREATE IN-APP NOTIFICATION FOR TECHNICIAN
        // ====================================================================
        
        // Add notification to technician's dashboard
        require_once 'Main Classes/Notification.php';
        $technician_user_id = $technician['user_id'];
        $notif = new Notification();
        $notif->addNotification($technician_user_id, "You have been assigned to a new customer. Name: $customerName, Email: $customerEmail. Please check your dashboard for details.");
    }
    
    // Return successful assignment response
    echo json_encode(['success' => true, 'assignment_id' => $result['assignment_id']]);
} else {
    // Assignment failed - return error response
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
