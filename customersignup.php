<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

require_once './Main Classes/Customer.php';
require_once './Main Classes/Mailer.php';

// Handle FormData from frontend (same as technician signup)
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$user_type = 'customer';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid email format."]);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$customerRegister = new Customer();
$result = $customerRegister->registerUser($name, $email, $hashed_password, $contact_number, $address, $user_type);

if ($result) {
    // Clear any verification session
    if (isset($_SESSION['email_verified'])) {
        unset($_SESSION['email_verified'], $_SESSION['verification_timestamp']);
    }
    
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
    echo json_encode(["status" => "success", "message" => "Registration successful! Welcome to GearSphere!"]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to register. Email might already exist or registration failed."]);
}
