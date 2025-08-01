<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Mailer.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL) : null;

if (!$email) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A valid email is required"]);
    exit();
}

// Generate 6-digit OTP
$otp = random_int(100000, 999999);

$mailer = new Mailer();
// Use the new OTP template method
$mailer->sendOTPEmail($email, 'User', $otp, 'verification');

if ($mailer->send()) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "OTP sent to your email.", "otp" => $otp]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error while sending OTP to your email."]);
}
