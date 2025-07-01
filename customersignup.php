<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Customer.php';

// Get form data via $_POST (since using multipart/form-data)
$name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
$address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
$user_type = isset($_POST['userType']) ? $_POST['userType'] : 'customer'; // optional fallback

if (!$name || !$email || !$password || !$contact_number || !$address) {
    http_response_code(400);
    echo json_encode(["message" => "All fields are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid email format."]);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$customerRegister = new Customer();
$result = $customerRegister->registerUser($name, $email, $hashed_password, $contact_number, $address, $user_type); // update method

if ($result) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Customer was successfully registered."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to register the Customer."]);
}

































