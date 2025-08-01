<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Customer.php';

$data = json_decode(file_get_contents("php://input"));

$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
$password = $data->password;

$userLogin = new Customer();

$signinResult = $userLogin->login($email, $password);

if ($signinResult['success']) {
    // Set PHP session variables
    $_SESSION['user_id'] = $signinResult['user_id'];
    $_SESSION['user_type'] = $signinResult['user_type'];
    $_SESSION['email'] = $email;
    $_SESSION['last_activity'] = time(); // Set initial last activity timestamp

    // Add technician_id to session if present
    if (isset($signinResult['technician_id'])) {
        $_SESSION['technician_id'] = $signinResult['technician_id'];
    }

    // Get user name from database
    $userDetails = $userLogin->getDetails($signinResult['user_id']);
    if ($userDetails && isset($userDetails['name'])) {
        $_SESSION['name'] = $userDetails['name'];
    }

    // Force session write and restart for reliability
    session_write_close();
    session_start();

    // Log successful login
    error_log("GearSphere: Login successful - User ID: " . $signinResult['user_id'] . ", Type: " . $signinResult['user_type'] . ", Session ID: " . session_id());

    // Add debug information to response (for troubleshooting)
    $signinResult['session_debug'] = [
        'session_id' => session_id(),
        'session_name' => session_name(),
        'origin' => isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'Not set',
        'session_saved' => [
            'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
            'user_type' => $_SESSION['user_type'] ?? 'NOT SET',
            'email' => $_SESSION['email'] ?? 'NOT SET'
        ],
        'session_status' => session_status(),
        'session_save_path' => session_save_path()
    ];

    http_response_code(200);
    echo json_encode($signinResult);
} else {
    echo json_encode($signinResult);
}
// end of login