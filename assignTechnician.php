<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

require_once './Main Classes/technician.php';
require_once './Main Classes/Mailer.php';
require_once './Main Classes/Notification.php';

$data = json_decode(file_get_contents('php://input'), true);

$customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
$technician_id = isset($data['technician_id']) ? (int)$data['technician_id'] : null;
$instructions = isset($data['instructions']) ? trim($data['instructions']) : '';

if (!$customer_id || !$technician_id) {
    echo json_encode(['success' => false, 'message' => 'Missing customer_id or technician_id.']);
    exit;
}

$tech = new Technician();
$result = $tech->assignTechnician($customer_id, $technician_id, $instructions);

if ($result && isset($result['assignment_id'])) {
    // Get technician details for email
    $technician = $tech->getTechnicianByTechnicianId($technician_id);
    // Fetch customer details for notification
    $customerDetails = $tech->getDetails($customer_id);
    $customerName = $customerDetails['name'] ?? '';
    $customerEmail = $customerDetails['email'] ?? '';
    
    if ($technician && !empty($technician['email'])) {
        $mailer = new Mailer();
        
        // Create assignment details for template
        $assignmentDetails = [
            'assignment_id' => $result['assignment_id'],
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'instructions' => $instructions,
            'date' => date('F j, Y')
        ];
        
        // Use new technician assignment template
        $mailer->sendTechnicianAssignmentEmail(
            $technician['email'], 
            $technician['name'], 
            $assignmentDetails
        );
        
        $mailer->send();
        
        // Add notification for technician
        require_once 'Main Classes/Notification.php';
        $technician_user_id = $technician['user_id'];
        $notif = new Notification();
        $notif->addNotification($technician_user_id, "You have been assigned to a new customer. Name: $customerName, Email: $customerEmail. Please check your dashboard for details.");
    }
    echo json_encode(['success' => true, 'assignment_id' => $result['assignment_id']]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
