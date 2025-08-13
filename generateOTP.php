<?php
session_start();
require_once 'corsConfig.php';
initializeEndpoint();

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

if ($checkEmail->checkEmailExists($email)) {
    $otp = random_int(100000, 999999);

    // Store OTP in session with expiry (5 minutes) for password reset
    $_SESSION['password_reset_otp'] = $otp;
    $_SESSION['password_reset_email'] = $email;
    $_SESSION['password_reset_expire'] = time() + 300; // 5 minutes

    $mailer = new Mailer();
    // Use the new password reset template
    $mailer->sendPasswordResetEmail($email, 'User', $otp);

    if ($mailer->send()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "OTP sent to your email. Check your inbox."
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to send OTP email."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email not registered."]);
}
