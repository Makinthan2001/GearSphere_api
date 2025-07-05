<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'Main Classes/Seller.php';

try {
    // Get form data
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    // Create Seller object
    $seller = new Seller();
    
    // First, verify the user is a seller
    $sellerData = $seller->getDetails($user_id);
    if (!$sellerData || $sellerData['user_type'] !== 'seller') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Seller privileges required.']);
        exit;
    }
    
    // Handle profile image upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'profile_images/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_info = pathinfo($_FILES['profile_image']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        // Validate file type
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_extensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit;
        }
        
        // Generate unique filename
        $filename = 'seller_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $profile_image = $filename;
            
            // Delete old profile image if it exists and is not the default
            if ($sellerData['profile_image'] && $sellerData['profile_image'] !== 'user_image.jpg') {
                $old_image_path = $upload_dir . $sellerData['profile_image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }
    }
    
    // Update seller profile using the updateDetails method from parent User class
    $result = $seller->updateDetails($user_id, $name, $contact_number, $address, $profile_image ? $profile_image : $sellerData['profile_image']);
    
    if ($result && isset($result['success']) && $result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'profile_image' => $profile_image ? $profile_image : $sellerData['profile_image']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 