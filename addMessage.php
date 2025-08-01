<?php

/**
 * Customer Message Submission API Endpoint
 * 
 * This endpoint handles customer contact form submissions for the GearSphere system.
 * It processes customer inquiries, creates message records, and sends notifications
 * to administrators for prompt customer service response.
 * 
 * @method POST
 * @endpoint /addMessage.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import required classes for message handling
require_once __DIR__ . '/Main Classes/Message.php';
require_once __DIR__ . '/DbConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ====================================================================
    // EXTRACT MESSAGE DATA - Support multiple input formats
    // ====================================================================
    
    // Support JSON input for API flexibility
    if (strpos($_SERVER["CONTENT_TYPE"] ?? '', "application/json") === 0) {
        $input = json_decode(file_get_contents("php://input"), true);
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $subject = trim($input['subject'] ?? '');
        $message = trim($input['message'] ?? '');
    } else {
        // Support form-encoded data for traditional forms
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    }

    // ====================================================================
    // VALIDATE AND PROCESS MESSAGE
    // ====================================================================
    
    if ($name && $email && $subject && $message) {
        // Create message record in database
        $msgObj = new Message();
        $result = $msgObj->addMessage($name, $email, $subject, $message);
        
        if ($result && isset($result['success']) && $result['success']) {
            // ====================================================================
            // NOTIFY ADMIN OF NEW CUSTOMER MESSAGE
            // ====================================================================
            
            // Create notification for admin when new message is received
            require_once __DIR__ . '/Main Classes/Notification.php';
            
            try {
                // Find admin user to notify
                $db = new DBConnector();
                $pdo = $db->connect();
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_type = 'admin' LIMIT 1");
                $stmt->execute();
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin) {
                    $adminId = $admin['user_id'];
                    
                    // Create notification with message preview
                    $notificationMessage = "New Message Received!\n\nFrom: $name ($email)\nSubject: $subject\n\nMessage: " . substr($message, 0, 100) . (strlen($message) > 100 ? "..." : "");
                    
                    // Add notification with 1-hour deduplication window
                    $notification = new Notification();
                    $notification->addUniqueNotification($adminId, $notificationMessage, 1); // 1-hour window for message notifications
                }
            } catch (Exception $e) {
                // Log error but don't fail the message creation
                error_log("Failed to create admin notification: " . $e->getMessage());
            }
        }
        
        // Return result to customer
        echo json_encode($result);
    } else {
        // Missing required fields
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
    }
} else {
    // Invalid request method
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
