<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    http_response_code(200);
    exit();
}

require_once './Main Classes/Technician.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    error_log('POST: ' . print_r($_POST, true));

    $user_id = $_POST['user_id'];
    $technician_id = $_POST['technician_id'];
    $name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
    $contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
    $address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
    $experience = isset($_POST['experience']) ? htmlspecialchars(strip_tags($_POST['experience'])) : null;
    $specialization = isset($_POST['specialization']) ? htmlspecialchars(strip_tags($_POST['specialization'])) : null;
    $charge_per_day = isset($_POST['charge_per_day']) ? htmlspecialchars(strip_tags($_POST['charge_per_day'])) : null;
    $profile_image = null;

    // Log all received values for debugging
    error_log("[DEBUG] user_id: $user_id, technician_id: $technician_id, name: $name, contact_number: $contact_number, address: $address, experience: $experience, specialization: $specialization, charge_per_day: $charge_per_day");

    if (empty($user_id) || empty($technician_id)) {
        http_response_code(400);
        echo json_encode(["message" => "User ID and Technician ID are required."]);
        exit;
    }

    if (!is_numeric($charge_per_day)) {
        http_response_code(400);
        echo json_encode(["message" => "Charge per day must be a number."]);
        exit;
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'profile_images/';

        // Generate a random file name to prevent conflicts
        $uniqueName = uniqid('img_', true);
        $imageFileType = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $uploadFile = $uploadDir . $uniqueName . '.' . $imageFileType;

        // Check if the file is an actual image
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if ($check === false) {
            http_response_code(400);
            echo json_encode(["message" => "File is not an image."]);
            exit;
        }

        // Limit file size to 500KB
        if ($_FILES['profile_image']['size'] > 5000000) {
            http_response_code(400);
            echo json_encode(["message" => "File is too large. Maximum size is 500KB."]);
            exit;
        }

        // Only allow certain file formats
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            http_response_code(400);
            echo json_encode(["message" => "Only JPG, JPEG, PNG, and GIF files are allowed."]);
            exit;
        }

        // Move the file to the upload directory
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload profile image."]);
            exit;
        }

        $profile_image = basename($uploadFile);
    }


    $updateTechnicianProfile = new Technician();
    $result = $updateTechnicianProfile->updateTechnicianDetails(
        $user_id,
        $name,
        $contact_number,
        $address,
        $profile_image,
        $technician_id,
        // $experience,
        // $specialization,
        $charge_per_day
    );

    // Log the result of the update
    error_log("[DEBUG] updateTechnicianDetails result: " . print_r($result, true));

    if ($result) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Technician details updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to update profile."]);
    }
}
