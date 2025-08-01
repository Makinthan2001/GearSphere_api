<?php

/**
 * Email Validation OTP API Endpoint
 * 
 * This endpoint handles generating and sending One-Time Passwords (OTP) for
 * email verification during registration in the GearSphere system. It validates
 * email addresses, generates secure OTPs, and sends verification emails.
 * 
 * @method POST
 * @endpoint /emailValidationOTP.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import Mailer class for email operations
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
    echo json_encode(["success" => false, "message" => "A valid email is required"]);
    exit();
}

// ====================================================================
// GENERATE AND SEND VERIFICATION OTP
// ====================================================================

// Generate secure 6-digit OTP for email verification
$otp = random_int(100000, 999999);

$mailer = new Mailer();
// Send email verification OTP using structured template
$mailer->sendOTPEmail($email, 'User', $otp, 'verification');

if ($mailer->send()) {
    // OTP sent successfully
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "OTP sent to your email.", "otp" => $otp]);
} else {
    // Email sending failed
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error while sending OTP to your email."]);
}
