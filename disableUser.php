<?php

/**
 * User Account Status Management API Endpoint
 * 
 * This endpoint handles enabling/disabling user accounts in the GearSphere system.
 * It processes admin requests to change user status, sends email notifications,
 * and manages account state transitions with proper validation and logging.
 * 
 * @method GET
 * @endpoint /disableUser.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import required classes for user management and notifications
require_once './Main Classes/Admin.php';
require_once './Main Classes/Mailer.php';

if (isset($_GET['id'])) {
    // ====================================================================
    // EXTRACT AND VALIDATE REQUEST PARAMETERS
    // ====================================================================

    $user_id = $_GET['id'];           // ID of user to modify
    $disable_status = $_GET['status']; // New status: 'disabled', 'active', or 'suspended'

    // ====================================================================
    // PROCESS USER STATUS CHANGE
    // ====================================================================

    $disableUser = new Admin();

    // Attempt to update user status
    $result = $disableUser->disableUser($user_id, $disable_status);

    if ($result) {
        // ====================================================================
        // SEND EMAIL NOTIFICATION TO USER
        // ====================================================================
        
        $mailer = new Mailer();
        $userData = $disableUser->getDetails($user_id);
        
        // Map internal status values to email template status
        $statusMap = [
            'disabled' => 'disabled',
            'active' => 'enabled',
            'suspended' => 'suspended'
        ];
        
        $emailStatus = $statusMap[$disable_status] ?? 'disabled';
        
        // Send structured account status notification email
        $mailer->sendAccountStatusEmail($userData['email'], $userData['name'] ?? $userData['email'], $emailStatus);
        
        // Attempt to send email and log results
        if ($mailer->send()) {
            error_log("Account status email sent successfully to: " . $userData['email']);
        } else {
            error_log("Failed to send account status email to: " . $userData['email']);
        }
        
        // Return successful status change response
        http_response_code(200);
        echo json_encode($result);
    } else {
        // Status change failed
        http_response_code(404);
        echo json_encode($result);
    }
} else {
    // ====================================================================
    // MISSING REQUIRED PARAMETERS - Return error
    // ====================================================================
    http_response_code(400);
    echo json_encode(["message" => "User ID is required."]);
    exit();
}
