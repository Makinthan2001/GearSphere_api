<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Customer.php';

$data = json_decode(file_get_contents("php://input"));

$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->new_password));
$password = password_hash($password, PASSWORD_BCRYPT);

$customerChangePassword = new Customer();

$Result = $customerChangePassword->forgotPassword($email, $password);

if ($Result) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Password reset successfully..."]);
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Unable to reset the password."]);
}
