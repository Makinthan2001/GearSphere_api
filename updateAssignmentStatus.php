<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

require_once __DIR__ . '/DbConnector.php';
require_once __DIR__ . '/Main Classes/Mailer.php';
require_once __DIR__ . '/Main Classes/Notification.php';
require_once __DIR__ . '/Main Classes/technician.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$assignment_id = isset($data['assignment_id']) ? intval($data['assignment_id']) : null;
$status = isset($data['status']) ? $data['status'] : null;

if (!$assignment_id || !in_array($status, ['accepted', 'rejected'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
    exit;
}

try {
    $db = new DBConnector();
    $pdo = $db->connect();
    // Update status
    $sql = "UPDATE technician_assignments SET status = :status WHERE assignment_id = :assignment_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch customer email and name
    $sql2 = "SELECT ta.customer_id, u.email, u.name, ta.technician_id FROM technician_assignments ta INNER JOIN users u ON ta.customer_id = u.user_id WHERE ta.assignment_id = :assignment_id";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
    $stmt2->execute();
    $customer = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Fetch technician name
    $technicianName = '';
    if ($customer && !empty($customer['technician_id'])) {
        $tech = new technician();
        $technicianDetails = $tech->getTechnicianByTechnicianId($customer['technician_id']);
        $technicianName = $technicianDetails['name'] ?? '';
    }

    if ($customer && !empty($customer['email'])) {
        $to = $customer['email'];
        $name = $customer['name'];
        $subject = "Your Build Request Has Been " . ucfirst($status);
        $body = "Hello $name,<br><br>Your build request has been <b>" . ucfirst($status) . "</b> by the technician.";
        $mailer = new Mailer();
        $mailer->setInfo($to, $subject, $body);
        $mailer->send();
        // Add notification for customer
        $notif = new Notification();
        $notif->addNotification($customer['customer_id'], "Your request was $status by technician: $technicianName.");
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
