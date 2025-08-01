<?php

/**
 * Technician Registration API Endpoint
 * 
 * This endpoint handles technician account registration for the GearSphere system.
 * It processes technician applications including CV uploads, credential verification,
 * and admin notification workflows for approval-based registration.
 * 
 * @method POST
 * @endpoint /techniciansignup.php
 * @param string $name Technician's name
 * @param string $email Technician's email
 * @param string $password Technician's password
 * @param string $contact_number Technician's contact number
 * @param string $address Technician's address
 * @param string $user_type User type (default: 'Technician')
 * @param string|null $specialization Technician's specialization
 * @param string|null $experience Technician's experience
 * @param string $cv_filename Filename of the uploaded CV
 * @return array Response indicating success or failure of registration
 */

// Initialize CORS and session configuration
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

// Import required classes for technician registration
require_once './Main Classes/Technician.php';
require_once './Main Classes/Mailer.php';

// ====================================================================
// EXTRACT AND SANITIZE FORM DATA
// ====================================================================

// Extract and sanitize user input data
$name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
$address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
$user_type = isset($_POST['userType']) ? $_POST['userType'] : 'customer';

// Extract technician-specific information
$specialization = isset($_POST['specialization']) ? htmlspecialchars(strip_tags($_POST['specialization'])) : null;
$experience = isset($_POST['experience']) ? htmlspecialchars(strip_tags($_POST['experience'])) : null;
$file = isset($_FILES['cv']) ? $_FILES['cv'] : null;

// ====================================================================
// VALIDATE REQUIRED FIELDS
// ====================================================================

// Check for required basic information
if (!$name || !$email || !$password || !$contact_number || !$address) {
    http_response_code(400);
    echo json_encode(["message" => "All fields are required."]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid email format."]);
    exit();
}

// ====================================================================
// HANDLE CV FILE UPLOAD
// ====================================================================

// Create upload directory if it doesn't exist
$targetDir = "verifypdfs/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Generate unique filename to prevent conflicts
$uniqueFileName = uniqid() . "_" . basename($file["name"]);
$targetFile = $targetDir . $uniqueFileName;

// Attempt file upload
if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to upload CV."]);
    exit();
}

// ====================================================================
// PROCESS TECHNICIAN REGISTRATION
// ====================================================================

// Hash password for secure storage
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Create technician account with uploaded credentials
$TechnicianRegister = new technician();
$result = $TechnicianRegister->registertechnician(
    $name,
    $email,
    $hashed_password,
    $contact_number,
    $address,
    $user_type = 'Technician',
    $specialization,
    $experience,
    $uniqueFileName
);

if ($result) {
    // ====================================================================
    // NOTIFY ADMIN OF NEW TECHNICIAN APPLICATION
    // ====================================================================
    
    // Create notification for admin review and approval
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
            
            // Create comprehensive notification message for admin review
            $notificationMessage = "New Technician Registered!\n\nName: $name\nEmail: $email\nContact: $contact_number\nAddress: $address\nSpecialization: $specialization\nExperience: $experience years\n\nPlease review the technician registration and verify credentials.";
            
            // Add notification with 1-hour deduplication window
            $notification = new Notification();
            $notification->addUniqueNotification($adminId, $notificationMessage, 1); // 1-hour window for registration notifications
        }
    } catch (Exception $e) {
        // Log error but don't fail the registration process
        error_log("Failed to create admin notification for technician registration: " . $e->getMessage());
    }
    
    // Return success response to applicant
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Technician was successfully registered."]);
} else {
    // Registration failed
    http_response_code(400);
    echo json_encode(["message" => "Unable to register the Technician."]);
}
