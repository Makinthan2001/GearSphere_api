<?php
/**
 * Admin Profile Update API Endpoint
 * 
 * This endpoint handles updating admin user profiles in the GearSphere system.
 * It processes profile information updates including personal details and profile
 * image uploads with authentication and authorization validation.
 * 
 * HTTP Method: POST
 * Content-Type: multipart/form-data
 * Authentication: Required (Session-based)
 * Authorization: Admin privileges required
 * 
 * Form Parameters:
 * - name (string, optional): Admin's full name
 * - contact_number (string, optional): Admin's contact phone number
 * - address (string, optional): Admin's address
 * - profile_image (file, optional): New profile image (JPG, PNG, GIF)
 * 
 * Response Format:
 * Success (200): {"success": true, "message": "Profile updated successfully", "profile_image": "<filename>"}
 * Error (401): {"success": false, "message": "Authentication required"}
 * Error (403): {"success": false, "message": "Access denied. Admin privileges required."}
 * Error (400): {"success": false, "message": "Invalid file type. Only JPG, PNG, and GIF are allowed."}
 * Error (500): {"success": false, "message": "Failed to upload image" | "Failed to update profile" | "Database error: <message>"}
 * 
 * Features:
 * - Session-based authentication validation
 * - Admin privilege verification
 * - Profile image upload with validation
 * - Automatic old image cleanup
 * - File type and security validation
 * - Comprehensive error handling
 * 
 */

require_once 'corsConfig.php';
initializeEndpoint();

require_once 'sessionConfig.php';
require_once 'Main Classes/Admin.php';

try {
    // ==========================================
    // AUTHENTICATION & DATA EXTRACTION
    // ==========================================
    
    // Get authenticated user ID from session
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Extract profile update data from form submission
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;

    // Validate user authentication
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }

    // ==========================================
    // AUTHORIZATION VALIDATION
    // ==========================================
    
    // Create Admin object and verify admin privileges
    $admin = new Admin();

    // Fetch current admin data and verify admin role
    $adminData = $admin->getDetails($user_id);
    if (!$adminData || $adminData['user_type'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
        exit;
    }

    // ==========================================
    // PROFILE IMAGE UPLOAD PROCESSING
    // ==========================================
    
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'profile_images/';

        // Ensure upload directory exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Extract file information for validation
        $file_info = pathinfo($_FILES['profile_image']['name']);
        $file_extension = strtolower($file_info['extension']);

        // Validate file type for security
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_extensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit;
        }

        // Generate unique filename to prevent conflicts
        $filename = 'admin_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        // Move uploaded file to permanent location
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $profile_image = $filename;

            // Clean up old profile image to save storage space
            if ($adminData['profile_image'] && $adminData['profile_image'] !== 'user_image.jpg') {
                $old_image_path = $upload_dir . $adminData['profile_image'];
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

    // ==========================================
    // PROFILE UPDATE PROCESSING
    // ==========================================
    
    // Update admin profile using inherited User class method
    // Preserve existing profile image if no new image uploaded
    $result = $admin->updateDetails(
        $user_id, 
        $name, 
        $contact_number, 
        $address, 
        $profile_image ? $profile_image : $adminData['profile_image']
    );

    // ==========================================
    // RESPONSE HANDLING
    // ==========================================
    
    if ($result && isset($result['success']) && $result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'profile_image' => $profile_image ? $profile_image : $adminData['profile_image']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
} catch (Exception $e) {
    // Handle any unexpected errors
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
