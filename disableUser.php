<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Admin.php';
require_once './Main Classes/Mailer.php';

if (isset($_GET['id'])) {

    $user_id = $_GET['id'];
    $disable_status = $_GET['status'];

    $disableUser = new Admin();

    $result = $disableUser->disableUser($user_id, $disable_status);

    if ($result) {
        $mailer = new Mailer();
        $userData = $disableUser->getDetails($user_id);
        
        // Use the structured account status template
        $statusMap = [
            'disabled' => 'disabled',
            'active' => 'enabled',
            'suspended' => 'suspended'
        ];
        
        $emailStatus = $statusMap[$disable_status] ?? 'disabled';
        
        // Fix: Remove the extra $subject parameter - the method generates its own subject
        $mailer->sendAccountStatusEmail($userData['email'], $userData['name'] ?? $userData['email'], $emailStatus);
        
        if ($mailer->send()) {
            error_log("Account status email sent successfully to: " . $userData['email']);
        } else {
            error_log("Failed to send account status email to: " . $userData['email']);
        }
        
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode($result);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required."]);
    exit();
}
