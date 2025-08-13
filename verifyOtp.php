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

// Check if OTP exists and not expired
if (!isset($_SESSION['otp']) || time() > $_SESSION['otp_expire']) {
    echo json_encode(["success" => false, "message" => "OTP expired"]);
    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expire']);
    exit();
}

// Validate OTP
if ($enteredOtp == $_SESSION['otp']) {
    echo json_encode(["success" => true, "message" => "OTP verified successfully"]);
    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expire']); // clear after success
} else {
    echo json_encode(["success" => false, "message" => "Invalid OTP"]);
}
