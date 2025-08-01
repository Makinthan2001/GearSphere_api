<?php

/**
 * User Authentication API Endpoint
 * 
 * This endpoint handles user login authentication for the GearSphere system.
 * It validates credentials, creates secure sessions, and returns user information
 * for successful authentication attempts.
 * 
 * @method POST
 * @endpoint /login.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();

// Import Customer class which provides login functionality
require_once './Main Classes/Customer.php';

// Get JSON input from request body
$data = json_decode(file_get_contents("php://input"));

// Sanitize and validate input data
$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
$password = $data->password;

// Create user instance for authentication
$userLogin = new Customer();

// Attempt user login with provided credentials
$signinResult = $userLogin->login($email, $password);

if ($signinResult['success']) {
    // ====================================================================
    // LOGIN SUCCESSFUL - Create secure session
    // ====================================================================
    
    // Set core session variables for authenticated user
    $_SESSION['user_id'] = $signinResult['user_id'];
    $_SESSION['user_type'] = $signinResult['user_type'];
    $_SESSION['email'] = $email;
    $_SESSION['last_activity'] = time(); // Track session activity for timeout

    // Add technician-specific session data if applicable
    if (isset($signinResult['technician_id'])) {
        $_SESSION['technician_id'] = $signinResult['technician_id'];
    }

    // Fetch and store user's display name in session
    $userDetails = $userLogin->getDetails($signinResult['user_id']);
    if ($userDetails && isset($userDetails['name'])) {
        $_SESSION['name'] = $userDetails['name'];
    }

    // Force session write and restart for reliability across requests
    session_write_close();
    session_start();

    // Log successful authentication for security monitoring
    error_log("GearSphere: Login successful - User ID: " . $signinResult['user_id'] . ", Type: " . $signinResult['user_type'] . ", Session ID: " . session_id());

    // Add debugging information to response for troubleshooting
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

    // Return success response with user data
    http_response_code(200);
    echo json_encode($signinResult);
} else {
    // ====================================================================
    // LOGIN FAILED - Return error message
    // ====================================================================
    echo json_encode($signinResult);
}
// End of login endpoint