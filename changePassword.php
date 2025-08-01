<?php

/**
 * Password Reset API Endpoint
 * 
 * This endpoint handles password reset functionality for the GearSphere system.
 * It validates user input, hashes new passwords securely, and updates
 * user credentials with proper security measures and error handling.
 * 
 * @method POST
 * @endpoint /changePassword.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import Customer class for password operations
require_once './Main Classes/Customer.php';

// ====================================================================
// EXTRACT AND VALIDATE REQUEST DATA
// ====================================================================

// Get JSON input from request body
$data = json_decode(file_get_contents("php://input"));

// Sanitize and extract password reset parameters
$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->new_password));

// Hash new password using secure bcrypt algorithm
$password = password_hash($password, PASSWORD_BCRYPT);

// ====================================================================
// PROCESS PASSWORD RESET
// ====================================================================

$customerChangePassword = new Customer();

// Attempt to update user password
$Result = $customerChangePassword->forgotPassword($email, $password);

if ($Result) {
    // Password reset successful
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Password reset successfully..."]);
} else {
    // Password reset failed
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Unable to reset the password."]);
}
