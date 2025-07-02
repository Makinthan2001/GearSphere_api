<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Customer.php';
require_once './Main Classes/Mailer.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL) : null;

if (!$email) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Valid email is required."]);
    exit();
}

$checkEmail = new Customer();

if ($checkEmail->checkEmailExists($email)) {  // Change this function to only check email existence
    $otp = random_int(100000, 999999);
    $mailer = new Mailer();
    $msg = "Dear User, <br> Your verification code is: <strong>$otp</strong><br>Use this 6-digit code to verify and change your password.";
    $mailer->setInfo($email, 'OTP Verification', $msg);

    if ($mailer->send()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "OTP sent to your email. Check your inbox.",
            "otp" => $otp
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to send OTP email."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email not registered."]);
}
