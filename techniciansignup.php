<?php
require_once 'corsConfig.php';
initializeEndpoint();
require_once 'sessionConfig.php';

require_once './Main Classes/Technician.php';
require_once './Main Classes/Mailer.php';

$name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$contact_number = isset($_POST['contact_number']) ? htmlspecialchars(strip_tags($_POST['contact_number'])) : null;
$address = isset($_POST['address']) ? htmlspecialchars(strip_tags($_POST['address'])) : null;
$user_type = isset($_POST['userType']) ? $_POST['userType'] : 'customer';
$specialization = isset($_POST['specialization']) ? htmlspecialchars(strip_tags($_POST['specialization'])) : null;
$experience = isset($_POST['experience']) ? htmlspecialchars(strip_tags($_POST['experience'])) : null;
$file = isset($_FILES['cv']) ? $_FILES['cv'] : null;

if (!$name || !$email || !$password || !$contact_number || !$address) {
    http_response_code(400);
    echo json_encode(["message" => "All fields are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid email format."]);
    exit();
}

// Upload file
$targetDir = "verifypdfs/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$uniqueFileName = uniqid() . "_" . basename($file["name"]);
$targetFile = $targetDir . $uniqueFileName;

if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to upload CV."]);
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Call technician register
$TechnicianRegister = new technician();
$result = $TechnicianRegister->registertechnician(
    $name,
    $email,
    $hashed_password,
    $contact_number,
    $address,
    $user_type = 'Technician',
    $specialization,
    $experience,
    $uniqueFileName
);

if ($result) {
    // Create notification for admin when new technician registers
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
            $notificationMessage = "New Technician Registered!\n\nName: $name\nEmail: $email\nContact: $contact_number\nAddress: $address\nSpecialization: $specialization\nExperience: $experience years\n\nPlease review the technician registration and verify credentials.";
            
            $notification = new Notification();
            $notification->addUniqueNotification($adminId, $notificationMessage, 1); // 1-hour window for registration notifications
        }
    } catch (Exception $e) {
        // Log error but don't fail the registration
        error_log("Failed to create admin notification for technician registration: " . $e->getMessage());
    }
    
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Technician was successfully registered."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to register the Technician."]);
}
