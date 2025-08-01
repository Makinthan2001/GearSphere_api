<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

require_once __DIR__ . '/Main Classes/Technician.php';
require_once __DIR__ . '/Main Classes/Notification.php';
require_once __DIR__ . '/Main Classes/Mailer.php';

$data = json_decode(file_get_contents('php://input'), true);

$assignment_id = isset($data['assignment_id']) ? (int)$data['assignment_id'] : null;
$status = isset($data['status']) ? trim($data['status']) : '';

if (!$assignment_id || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing assignment_id or status.']);
    exit;
}

try {
    require_once __DIR__ . '/DbConnector.php';
    $pdo = (new DBConnector())->connect();
    
    // Update assignment status
    $stmt = $pdo->prepare("UPDATE technician_assignments SET status = :status WHERE assignment_id = :assignment_id");
    $stmt->execute([':status' => $status, ':assignment_id' => $assignment_id]);
    
    // Get assignment details for email
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
    
    if ($customer && !empty($customer['customer_email'])) {
        // Send build request status email using new template
        $mailer = new Mailer();
        $mailer->sendBuildRequestStatusEmail(
            $customer['customer_email'],
            $customer['customer_name'],
            $status,
            $customer['technician_name']
        );
        $mailer->send();
        
        // Add notification for customer
        $notif = new Notification();
        $notif->addNotification(
            $customer['customer_id'], 
            "Your request was $status by technician: " . $customer['technician_name'] . "."
        );
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("updateAssignmentStatus Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>
