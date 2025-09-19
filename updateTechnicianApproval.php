<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/DbConnector.php';
require_once __DIR__ . '/Main Classes/Notification.php';

header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in in and is admin
error_log("Session data: " . print_r($_SESSION, true));
error_log("User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
error_log("User Type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'not set'));

$skip_auth = false; // Set to true for testing without login

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

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['technician_id']) || !isset($data['approve_status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$technician_id = intval($data['technician_id']);
$approve_status = $data['approve_status'];

// Validate approve_status
if (!in_array($approve_status, ['approved', 'not approved'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid approval status']);
    exit;
}

try {
    $pdo = (new DBConnector())->connect();

    // First, check if the technician exists
    $checkStmt = $pdo->prepare("
        SELECT t.technician_id, u.name, u.email 
        FROM technician t 
        JOIN users u ON t.user_id = u.user_id 
        WHERE t.user_id = ?
    ");
    $checkStmt->execute([$technician_id]);
    $technician = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$technician) {
        echo json_encode(['success' => false, 'message' => 'Technician not found']);
        exit;
    }

    // Update the approval status
    $updateStmt = $pdo->prepare("
        UPDATE technician 
        SET approve_status = ? 
        WHERE user_id = ?
    ");

    $result = $updateStmt->execute([$approve_status, $technician_id]);

    if ($result) {
        // Log the approval action (handle case where session user_id might not be set)
        $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'unknown';
        error_log("Admin " . $admin_id . " updated technician " . $technician_id . " approval status to: " . $approve_status);

        // Send notification to the technician about approval status change
        try {
            $notification = new Notification();
            
            if ($approve_status === 'approved') {
                $message = "🎉 Congratulations! Your technician account has been approved by the admin.\n\nYou can now start accepting build requests from customers. Welcome to the GearSphere technician community!";
            } else {
                $message = "❌ Your technician account approval has been revoked by the admin.\n\nIf you believe this is an error, please contact the admin for clarification. You will not be able to accept new build requests until your account is re-approved.";
            }
            
            $notificationSent = $notification->addNotification($technician_id, $message);
            
            if ($notificationSent) {
                error_log("Notification sent to technician " . $technician_id . " about approval status change");
            } else {
                error_log("Failed to send notification to technician " . $technician_id);
            }
            
        } catch (Exception $e) {
            error_log("Error sending notification to technician: " . $e->getMessage());
            // Don't fail the approval update if notification fails
        }

        echo json_encode([
            'success' => true,
            'message' => 'Technician approval status updated successfully',
            'technician_name' => $technician['name'],
            'new_status' => $approve_status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update approval status']);
    }
} catch (Exception $e) {
    error_log("Error updating technician approval: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
