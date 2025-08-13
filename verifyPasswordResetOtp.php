<?php
session_start();
require_once 'corsConfig.php';
initializeEndpoint();

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$enteredOtp = isset($data['otp']) ? trim($data['otp']) : null;

if (!$enteredOtp) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "OTP is required"]);
    exit();
}

// Check if password reset OTP exists and not expired
if (!isset($_SESSION['password_reset_otp']) || time() > $_SESSION['password_reset_expire']) {
    echo json_encode(["success" => false, "message" => "OTP expired or not found"]);
    unset($_SESSION['password_reset_otp'], $_SESSION['password_reset_email'], $_SESSION['password_reset_expire']);
    exit();
}

// Validate OTP
if ($enteredOtp == $_SESSION['password_reset_otp']) {
    // Mark as verified but keep session data for password reset
    $_SESSION['otp_verified'] = true;
    echo json_encode(["success" => true, "message" => "OTP verified successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid OTP"]);
}
