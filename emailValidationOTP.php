<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
$message = "Dear User, <br> Your verification code is: <strong>$otp</strong><br>Use this 6-digit code to verify and proceed with registration.";
$mailer->setInfo($email, 'OTP Verification', $message);

if ($mailer->send()) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "OTP sent to your email.", "otp" => $otp]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error while sending OTP to your email."]);
}
