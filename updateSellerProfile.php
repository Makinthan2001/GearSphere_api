<?php
/**
 * Update Seller Profile API Endpoint
 * 
 * This script handles updating seller profile information including:
 * - Basic details (name, contact number, address)
 * - Profile image upload with validation and old image cleanup
 * - Session-based authentication for sellers
 * 
 * Method: POST
 * Authentication: Required (seller session)
 * Optional: name, contact_number, address, profile_image (file)
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Include session management and Seller class
require_once 'sessionConfig.php';
require_once 'Main Classes/Seller.php';

try {
    // Get form data from POST request and session
    $user_id = $_SESSION['user_id'] ?? null; // Get user ID from active session
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;

    // Check if user is authenticated (has active session)
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }

    // Create Seller object for database operations
    $seller = new Seller();

    // Verify the authenticated user is actually a seller
    $sellerData = $seller->getDetails($user_id);
    if (!$sellerData || $sellerData['user_type'] !== 'seller') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Seller privileges required.']);
        exit;
    }

    // Handle profile image upload with comprehensive validation
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'profile_images/';

        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Get file information and validate extension
        $file_info = pathinfo($_FILES['profile_image']['name']);
        $file_extension = strtolower($file_info['extension']);

        // Define allowed file types for security
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_extensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit;
        }

        // Generate unique filename to prevent conflicts
        $filename = 'seller_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        // Move uploaded file to destination
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $profile_image = $filename;

            // Clean up old profile image (if not default image)
            if ($sellerData['profile_image'] && $sellerData['profile_image'] !== 'user_image.jpg') {
                $old_image_path = $upload_dir . $sellerData['profile_image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path); // Delete old image file
                }
            }
        } else {
            // Return error if file upload fails
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }
    }

    // Update seller profile using inherited updateDetails method from User class
    $result = $seller->updateDetails($user_id, $name, $contact_number, $address, $profile_image ? $profile_image : $sellerData['profile_image']);

    // Check if update was successful and return appropriate response
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
    // Handle any database or system errors
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
