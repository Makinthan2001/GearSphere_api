<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    http_response_code(200);
    exit();
}

require_once './Main Classes/Customer.php';

$user_id = $_POST['user_id'];
$name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
$contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
$address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
$profile_image = null;

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


$updateCustomerProfile = new Customer();

$result = $updateCustomerProfile->updateDetails($user_id, $name, $contact_number, $address, $profile_image);

if ($result) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Customer details updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $result['message']]);
}
