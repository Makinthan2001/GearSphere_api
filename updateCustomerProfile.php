<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Customer.php';

// Handle both form data and JSON input
$input = null;
$user_id = null;
$name = null;
$contact_number = null;
$address = null;
$profile_image = null;

// Check if it's JSON request
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if (strpos($contentType, 'application/json') !== false) {
    // Handle JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? null;
    $name = isset($input['name']) ? htmlspecialchars(strip_tags($input['name'])) : null;
    $contact_number = isset($input['contact_number']) ? htmlspecialchars(strip_tags($input['contact_number'])) : null;
    $address = isset($input['address']) ? htmlspecialchars(strip_tags($input['address'])) : null;
} else {
    // Handle form data (including multipart/form-data)
    $user_id = $_POST['user_id'] ?? null;
    $name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
    $contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
    $address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
}

// Handle file upload (only for form data)
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'profile_images/';
    $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);
    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES['profile_image']['tmp_name']);
    if ($check !== false && $_FILES['profile_image']['size'] <= 3000000) {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
            $profile_image = basename($uploadFile);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload profile image."]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid profile image."]);
        exit;
    }
}

// Validate user_id
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "User ID is required."]);
    exit;
}

$updateCustomerProfile = new Customer();

$result = $updateCustomerProfile->updateDetails($user_id, $name, $contact_number, $address, $profile_image);

if (is_array($result) && isset($result['success']) && $result['success']) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Customer details updated successfully."]);
} else {
    http_response_code(500);
    $message = is_array($result) && isset($result['message']) ? $result['message'] : 'Update failed';
    echo json_encode(["success" => false, "message" => $message]);
}
