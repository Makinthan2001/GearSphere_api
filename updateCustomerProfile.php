<?php
/**
 * Update Customer Profile API Endpoint
 * 
 * This script handles updating customer profile information including:
 * - Basic details (name, contact number, address)
 * - Profile image upload
 * 
 * Method: POST
 * Required: user_id
 * Optional: name, contact_number, address, profile_image (file)
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include the Customer class for database operations
require_once './Main Classes/Customer.php';

// Get and sanitize input data from POST request
$user_id = $_POST['user_id']; // Required: Customer's user ID
$name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
$contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
$address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
$profile_image = null; // Will be set if image is uploaded

// Handle profile image upload if provided
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    // Define upload directory
    $uploadDir = 'profile_images/';
    $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);
    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

    // Validate the uploaded file
    $check = getimagesize($_FILES['profile_image']['tmp_name']); // Check if it's a real image
    
    // Validate image and file size (max 3MB)
    if ($check !== false && $_FILES['profile_image']['size'] <= 3000000) {
        // Attempt to move uploaded file to destination
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
            $profile_image = basename($uploadFile); // Store filename for database
        } else {
            // Return error if file upload fails
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload profile image."]);
            exit;
        }
    } else {
        // Return error for invalid image or file too large
        http_response_code(400);
        echo json_encode(["message" => "Invalid profile image."]);
        exit;
    }
}

// Create Customer object to handle database operations
$updateCustomerProfile = new Customer();

// Update customer details in database
$result = $updateCustomerProfile->updateDetails($user_id, $name, $contact_number, $address, $profile_image);

// Return response based on operation result
if ($result) {
    // Success response
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Customer details updated successfully."]);
} else {
    // Error response
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $result['message']]);
}
