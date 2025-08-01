<?php

/**
 * Customer Registration API Endpoint
 * 
 * This endpoint handles new customer account registration for the GearSphere system.
 * It validates input data, creates user accounts, sends notifications, and manages
 * the registration process with proper error handling.
 * 
 * @method POST
 * @endpoint /customersignup.php
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

// Import required classes for registration and notifications
require_once './Main Classes/Customer.php';
require_once './Main Classes/Mailer.php';

// ====================================================================
// EXTRACT AND VALIDATE FORM DATA
// ====================================================================

// Handle FormData from frontend (supports file uploads and form data)
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$user_type = 'customer';  // Fixed user type for this endpoint

// Validate email format before proceeding
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid email format."]);
    exit();
}

// Hash password for secure storage
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// ====================================================================
// ATTEMPT USER REGISTRATION
// ====================================================================

$customerRegister = new Customer();
$result = $customerRegister->registerUser($name, $email, $hashed_password, $contact_number, $address, $user_type);

if ($result) {
    // ====================================================================
    // REGISTRATION SUCCESSFUL - Clean up and notify
    // ====================================================================
    
    // Clear any existing verification session data
    if (isset($_SESSION['email_verified'])) {
        unset($_SESSION['email_verified'], $_SESSION['verification_timestamp']);
    }
    
    // Create notification for admin about new customer registration
    require_once __DIR__ . '/Main Classes/Notification.php';
    require_once __DIR__ . '/DbConnector.php';
    
    try {
        // Find admin user to notify
        $db = new DBConnector();
        $pdo = $db->connect();
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_type = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $adminId = $admin['user_id'];
            
            // Create comprehensive notification message for admin
            $notificationMessage = "New Customer Registered!\n\nName: $name\nEmail: $email\nContact: $contact_number\nAddress: $address\n\nPlease review the new customer registration.";
            
            // Add notification with 1-hour deduplication window
            $notification = new Notification();
            $notification->addUniqueNotification($adminId, $notificationMessage, 1); // 1-hour window for registration notifications
        }
    } catch (Exception $e) {
        // Log error but don't fail the registration process
        error_log("Failed to create admin notification for customer registration: " . $e->getMessage());
    }
    
    // Return success response to client
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Registration successful! Welcome to GearSphere!"]);
} else {
    // ====================================================================
    // REGISTRATION FAILED - Return error message
    // ====================================================================
    http_response_code(400);
    echo json_encode(["message" => "Unable to register. Email might already exist or registration failed."]);
}
