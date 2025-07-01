<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Customer.php';

$data = json_decode(file_get_contents("php://input"));

$email = filter_var($data->email,FILTER_SANITIZE_EMAIL);
$password = $data->password;

$userLogin = new Customer();

$signinResult = $userLogin->login($email,$password);

if ($signinResult['success']) {
    http_response_code(200);
    echo json_encode($signinResult);
} else {
    echo json_encode($signinResult);
}
// end of login