<?php

/**
 * OTP Generation API Endpoint
 * 
 * This endpoint handles generating and sending One-Time Passwords (OTP) for
 * password reset functionality in the GearSphere system. It validates email
 * addresses, generates secure OTPs, and sends them via email notifications.
 * 
 * @method POST
 * @endpoint /generateOTP.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import required classes for email validation and mailing
require_once './Main Classes/Customer.php';
require_once './Main Classes/Mailer.php';

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Get JSON input from request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Extract and validate email address
$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL) : null;

// Validate email format
if (!$email) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Valid email is required."]);
    exit();
}

// ====================================================================
// VERIFY EMAIL EXISTS AND GENERATE OTP
// ====================================================================

$checkEmail = new Customer();

// Check if email is registered in the system
if ($checkEmail->checkEmailExists($email)) {
    // Generate secure 6-digit OTP
    $otp = random_int(100000, 999999);
    
    // ====================================================================
    // SEND OTP VIA EMAIL
    // ====================================================================
    
    $mailer = new Mailer();
    // Send password reset email with OTP using structured template
    $mailer->sendPasswordResetEmail($email, 'User', $otp);

    if ($mailer->send()) {
        // OTP sent successfully
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "OTP sent to your email. Check your inbox.",
            "otp" => $otp
        ]);
    } else {
        // Email sending failed
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to send OTP email."]);
    }
} else {
    // ====================================================================
    // EMAIL NOT REGISTERED - Return error
    // ====================================================================
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email not registered."]);
}
