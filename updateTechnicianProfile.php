<?php
/**
 * Update Technician Profile API Endpoint
 * 
 * This script handles comprehensive technician profile updates including:
 * - Basic user details (name, contact number, address)
 * - Technician-specific information (experience, specialization, daily rate)
 * - Profile image upload with validation
 * - Status management (available/unavailable)
 * 
 * Method: POST
 * Required: user_id, technician_id
 * Optional: name, contact_number, address, experience, specialization, charge_per_day, profile_image (file), status
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Technician class for database operations
require_once './Main Classes/Technician.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Debug logging for troubleshooting (remove in production)
    error_log('POST: ' . print_r($_POST, true));

    // Extract and sanitize input data from POST request
    $user_id = $_POST['user_id']; // Required: User ID from users table
    $technician_id = $_POST['technician_id']; // Required: Technician ID from technician table
    $name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
    $contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
    $address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
    $experience = isset($_POST['experience']) ? htmlspecialchars(strip_tags($_POST['experience'])) : null;
    $specialization = isset($_POST['specialization']) ? htmlspecialchars(strip_tags($_POST['specialization'])) : null;
    $charge_per_day = isset($_POST['charge_per_day']) ? htmlspecialchars(strip_tags($_POST['charge_per_day'])) : null;
    $profile_image = null; // Will be set if image is uploaded
    $status = isset($_POST['status']) ? $_POST['status'] : 'available'; // Default to available

    // Debug logging for all received values
    error_log("[DEBUG] user_id: $user_id, technician_id: $technician_id, name: $name, contact_number: $contact_number, address: $address, experience: $experience, specialization: $specialization, charge_per_day: $charge_per_day");

    // Validate required fields
    if (empty($user_id) || empty($technician_id)) {
        http_response_code(400);
        echo json_encode(["message" => "User ID and Technician ID are required."]);
        exit;
    }

    // Validate charge per day is numeric
    if (!is_numeric($charge_per_day)) {
        http_response_code(400);
        echo json_encode(["message" => "Charge per day must be a number."]);
        exit;
    }

    // Handle profile image upload with comprehensive validation
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'profile_images/';

        // Generate unique filename to prevent conflicts and security issues
        $uniqueName = uniqid('img_', true);
        $imageFileType = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $uploadFile = $uploadDir . $uniqueName . '.' . $imageFileType;

        // Verify the uploaded file is actually an image
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if ($check === false) {
            http_response_code(400);
            echo json_encode(["message" => "File is not an image."]);
            exit;
        }

        // Enforce file size limit (5MB max)
        if ($_FILES['profile_image']['size'] > 5000000) {
            http_response_code(400);
            echo json_encode(["message" => "File is too large. Maximum size is 500KB."]);
            exit;
        }

        // Restrict allowed file types for security
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            http_response_code(400);
            echo json_encode(["message" => "Only JPG, JPEG, PNG, and GIF files are allowed."]);
            exit;
        }

        // Attempt to move uploaded file to destination
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload profile image."]);
            exit;
        }

        // Store the filename for database update
        $profile_image = basename($uploadFile);
    }

    // Create Technician object and update profile
    $updateTechnicianProfile = new Technician();
    $result = $updateTechnicianProfile->updateTechnicianDetails(
        $user_id,
        $name,
        $contact_number,
        $address,
        $profile_image,
        $technician_id,
        $charge_per_day,
        $status
    );

    // Debug logging for update result
    error_log("[DEBUG] updateTechnicianDetails result: " . print_r($result, true));

    // Return response based on operation result
    if ($result) {
        // Success response
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Technician details updated successfully."]);
    } else {
        // Error response
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to update profile."]);
    }
}
