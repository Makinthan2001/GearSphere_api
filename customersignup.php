<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once './Main Classes/Customer.php';
require_once './Main Classes/Mailer.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = isset($data['name']) ? trim($data['name']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';
$contact_number = isset($data['contact_number']) ? trim($data['contact_number']) : '';
$address = isset($data['address']) ? trim($data['address']) : '';
$user_type = 'customer'; // default user type for customer registration

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid email format."]);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$customerRegister = new Customer();
$result = $customerRegister->registerUser($name, $email, $hashed_password, $contact_number, $address, $user_type);

if ($result) {
    // Send welcome email to new customer
    $mailer = new Mailer();
    $mailer->sendWelcomeEmail($email, $name, 'customer');
    $mailer->send();
    
    // Create notification for admin when new customer registers
    require_once __DIR__ . '/Main Classes/Notification.php';
    require_once __DIR__ . '/DbConnector.php';
    
    try {
        $db = new DBConnector();
        $pdo = $db->connect();
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_type = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $adminId = $admin['user_id'];
            $notificationMessage = "New Customer Registered!\n\nName: $name\nEmail: $email\nContact: $contact_number\nAddress: $address\n\nPlease review the new customer registration.";
            
            $notification = new Notification();
            $notification->addUniqueNotification($adminId, $notificationMessage, 1); // 1-hour window for registration notifications
        }
    } catch (Exception $e) {
        // Log error but don't fail the registration
        error_log("Failed to create admin notification for customer registration: " . $e->getMessage());
    }
    
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Customer was successfully registered."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to register the Customer."]);
}
