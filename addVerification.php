<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Verification.php';
require_once './Main Classes/Technician.php';

$technician_name = isset($_POST['name']) ? $_POST['name'] : null;
$technician_password = isset($_POST['password']) ? $_POST['password'] : null;
$technician_email = isset($_POST['email']) ? $_POST['email'] : null;
$technician_address = isset($_POST['address']) ? $_POST['address'] : null;
$specialization = isset($_POST['specialization']) ? $_POST['specialization'] : null;
$file = isset($_FILES['proof']) ? $_FILES['proof'] : null;

// Check required fields
if (
    !$technician_name ||  !$technician_email || !$technician_password || 
     !$file ||  !$technician_address || !$specialization
) {
    http_response_code(400);
    echo json_encode(["message" => "All data are required."]);
    exit();
}

$checkEmail = new Technician();
$rs = $checkEmail->checkEmailExists($technician_email);

if ($rs) {
    http_response_code(409);
    echo json_encode(["message" => "Email already exists."]);
    exit();
} 

$addVerification = new Verification();
$Result = $addVerification->addVerification(
    $technician_name,
    $technician_password,
    $technician_email,
    $file,
    $technician_address,
    $specialization
);

if ($Result) {
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Verification request submitted successfully.",
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to submit verification request."
    ]);
}
