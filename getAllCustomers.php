<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Customer.php';

if ($_GET['action'] === 'getAll') {

    $getAllCustomers = new Customer();

    $result = $getAllCustomers->getAllCustomers();

    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "customer not found"]);
    }
} else {
    echo json_encode(["message" => "error occured"]);
}
