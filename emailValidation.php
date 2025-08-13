<?php
// Start session first if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'corsConfig.php';
initializeEndpoint();
require_once './Main Classes/Mailer.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Try to get email from multiple sources
$email = null;
if (isset($data['email']) && !empty($data['email'])) {
    $email = $data['email'];
} elseif (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = $_POST['email'];
} elseif (isset($_GET['email']) && !empty($_GET['email'])) {
    $email = $_GET['email'];
}

// Validate email
if ($email) {
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

if (!$email) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Valid email is required"]);
    exit();
}

try {
    // Generate 6-digit OTP
    $otp = random_int(100000, 999999);

    // Store OTP in session with expiry (5 minutes)
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_expire'] = time() + 300; // expires in 300 seconds (5 minutes)

    // Send OTP via email
    $mailer = new Mailer();
    $mailer->sendOTPEmail($email, 'User', $otp, 'verification');

    if ($mailer->send()) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "OTP sent to your email."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to send OTP email. Please check your email address and try again."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error. Please try again later."]);
}
