<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
header('Content-Type: application/json');
require_once 'Main Classes/technician.php';
require_once 'DbConnector.php';
require_once 'Main Classes/Mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$customer_id = $data['customer_id'] ?? null;
$technician_id = $data['technician_id'] ?? null;
$instructions = $data['instructions'] ?? null;

if (!$customer_id || !$technician_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$tech = new technician();
$result = $tech->assignTechnician($customer_id, $technician_id, $instructions);

if ($result['success']) {
    // Fetch technician details for email
    $technician = $tech->getTechnicianByTechnicianId($technician_id);
    // Fetch customer details for notification
    $customerDetails = $tech->getDetails($customer_id);
    $customerName = $customerDetails['name'] ?? '';
    $customerEmail = $customerDetails['email'] ?? '';
    if ($technician && !empty($technician['email'])) {
        $subject = "New PC Build Assignment from GearSphere";
        $message = "<p>Dear {$technician['name']},</p>"
            . "<p>You have been assigned a new PC build project by a customer on GearSphere.</p>"
            . "<p><strong>Instructions from customer:</strong><br>" . nl2br(htmlspecialchars($instructions)) . "</p>"
            . "<p>Please log in to your GearSphere account to view more details and contact the customer.</p>"
            . "<p>Thank you,<br>GearSphere Team</p>";
        $mailer = new Mailer();
        $mailer->setInfo($technician['email'], $subject, $message);
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
