<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/technician.php';

if ($_GET['action'] === 'getAll') {
    $getAllTechnicians = new technician();
    $result = $getAllTechnicians->getAllTechnicians();
    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "technicians not found"]);
    }
} else {
    echo json_encode(["message" => "error occured"]);
}
