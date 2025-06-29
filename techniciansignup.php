
<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Verification.php';
require_once './Main Classes/Technician.php';
require_once './Main Classes/Mailer.php';

$data = json_decode(file_get_contents("php://input"));

if ($data === null) {
    echo json_encode(["success" => false, "message" => "No data received."]);
    exit();
}

if (isset($data->verify_id)) {
    $verify_id = $data->verify_id;

    $getVerifyDetail = new Verification();
    $verificationDetails = $getVerifyDetail->verifyTechnician($verify_id); // Use the correct method name

    if ($verificationDetails) {
        $verify_id = $verificationDetails['verify_id'];
        $name = $verificationDetails['provider_name'];
        $username = $verificationDetails['provider_username'];
        $email = $verificationDetails['provider_email'];
        $password = $verificationDetails['provider_password'];
        $address = $verificationDetails['provider_address'];
        $specialization = $verificationDetails['service_category'];

        $technician = new Technician();

        $result = $technician->registerTechnician(
            $name,
            $email,
            $password,
            $username,
            $address,
            $verify_id,
            $specialization
        );

        if ($result['success']) {
            $mailer = new Mailer();
            $mailer->setInfo(
                $email,
                'Technician Account Verified',
                "Dear Technician,<br>Your account has been successfully verified by the admin.<br>You may now log in and offer your services on GearSphere.<br><br>For support, contact support@gearsphere.com."
            );
            if ($mailer->send()) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Technician has been successfully verified and notified."]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Technician registered but email failed to send."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => $result['message']]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "No verification record found for the given ID."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "verify_id is required."]);
}

