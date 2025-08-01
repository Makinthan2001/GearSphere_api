<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Customer.php';

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Please login first."]);
    exit();
}

$user_id = $_SESSION['user_id'];

$customerDetail = new Customer();

$result = $customerDetail->getDetails($user_id);

if ($result) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(404);
    echo json_encode(["message" => "User not found"]);
}
