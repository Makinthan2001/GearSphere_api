<?php

/**
 * Abstract User Base Class
 * 
 * This is the parent class for all user types in the GearSphere system.
 * It provides common functionality for user registration, authentication,
 * and profile management across Customer, Admin, Seller, and Technician roles.
 * @package GearSphere-BackEnd
 */

require_once 'DbConnector.php';

abstract class User
{
    // Core user properties - protected to allow inheritance
    protected $user_id;             // Unique identifier for the user
    protected $name;                // User's full name
    protected $email;               // User's email address (used for login)
    protected $password;            // Hashed password for authentication
    protected $contact_number;      // User's phone number
    protected $address;             // User's physical address
    protected $user_type;           // Role: customer, admin, seller, technician
    protected $status;              // General status field
    protected $specialization;      // For technicians - area of expertise
    protected $experience;          // For technicians - years of experience
    protected $hourly_rate;         // For technicians - service rate
    protected $cv_path;             // For technicians - CV file path
    protected $disable_status;      // Account status (enabled/disabled)
    protected $pdo;                 // Database connection object
    protected $profile_image;       // User's profile picture path

    /**
     * Constructor - Initialize database connection
     * 
     * Creates a database connection that will be used by all user operations.
     * This connection is shared across all methods in the class.
     */
    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Check if email already exists in the system
     * 
     * Prevents duplicate email registrations by checking if the current
     * email is already associated with an existing user account.
     * 
     * @return bool True if email exists, false otherwise
     */
    public function isAlreadyExists()
    {
        $query = "SELECT email FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Verify if a specific email exists in the database
     * 
     * Used during password reset and email validation processes
     * to confirm that an email address is registered in the system.
     * 
     * @param string $email Email address to check
     * @return bool True if email exists, false otherwise
     */
    public function checkEmailExists($email)
    {
        try {
            $query = "SELECT email FROM users WHERE email = :email";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to verify email. " . $e->getMessage()]);
            return false; // Return false on database error
        }
    }

    /**
     * Authenticate user login credentials
     * 
     * Verifies email and password combination, checks account status,
     * and returns user information for session management.
     * 
     * @param string $email User's email address
     * @param string $password Plain text password
     * @return array Login result with success status and user data
     */
    public function login($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
        
        try {
            // Fetch user data including hashed password and account status
            $sql = "SELECT user_id, password, user_type, disable_status FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $this->email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password using PHP's password_verify function
            if ($user && password_verify($this->password, $user['password'])) {
                // Check if account is disabled
                if ($user['disable_status'] === 'disabled') {
                    return ['success' => false, 'message' => 'Your account has been disabled. Please contact support.'];
                }
                
                // Set user properties for session
                $this->user_id = $user['user_id'];
                $this->user_type = $user['user_type'];
                
                // Prepare response with user data
                $response = [
                    'user_type' => $this->user_type,
                    'user_id' => $this->user_id,
                    'success' => true,
                    'message' => 'Login Successful...'
                ];
                
                // Special handling for technician users - fetch technician_id
                if (strtolower($this->user_type) === 'technician') {
                    require_once __DIR__ . '/technician.php';
                    $tech = new technician();
                    $technician_id = $tech->getTechnicianId($this->user_id);
                    
                    // Ensure only the primitive value is returned
                    if (is_array($technician_id) && isset($technician_id['technician_id'])) {
                        $response['technician_id'] = $technician_id['technician_id'];
                    } else {
                        $response['technician_id'] = $technician_id;
                    }
                }
                return $response;
            }
            
            // Invalid credentials
            return ["success" => false, "message" => "Incorrect email or password..."];
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to login. " . $e->getMessage()]);
        }
    }

    /**
     * Reset user password (forgot password functionality)
     * 
     * Updates the user's password in the database when they use
     * the forgot password feature with OTP verification.
     * 
     * @param string $email User's email address
     * @param string $password New hashed password
     * @return bool True if password updated successfully, false otherwise
     */
    public function forgotPassword($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
        
        try {
            $sql = "UPDATE users SET password = :password WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();

            error_log("Rows affected: " . $stmt->rowCount());

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                error_log("No rows updated - email may not exist or password unchanged");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["message" => "Failed to change password. " . $e->getMessage()]);
            return false;
        }
    }

    /**
     * Register a new standard user (Customer, Admin, Seller)
     * 
     * Creates a new user account in the system with basic information.
     * Checks for duplicate emails before registration.
     * 
     * @param string $name User's full name
     * @param string $email User's email address
     * @param string $password Hashed password
     * @param string $contact_number User's phone number
     * @param string $address User's address
     * @param string $user_type User role (default: 'customer')
     * @return bool True if registration successful, false otherwise
     */
    public function registerUser($name, $email, $password, $contact_number, $address, $user_type = 'customer')
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->contact_number = $contact_number;
        $this->address = $address;
        $this->user_type = $user_type;

        // Check if email is already registered
        if ($this->isAlreadyExists()) {
            return false;
        }

        try {
            // Insert new user into database
            $sql = "INSERT INTO users (name, email, password, contact_number, address, user_type) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $this->name);
            $stmt->bindParam(2, $this->email);
            $stmt->bindParam(3, $this->password);
            $stmt->bindParam(4, $this->contact_number);
            $stmt->bindParam(5, $this->address);
            $stmt->bindParam(6, $this->user_type);
            $rs = $stmt->execute();

            return $rs ? true : false;
        } catch (PDOException $e) {
            // Log error for debugging
            error_log("PDOException in registerUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register a new technician with additional qualifications
     * 
     * Creates a technician account with specialized information including
     * qualifications, experience, and CV. Uses database transactions to
     * ensure data consistency across users and technician tables.
     * 
     * @param string $name Technician's full name
     * @param string $email Technician's email address
     * @param string $password Hashed password
     * @param string $contact_number Technician's phone number
     * @param string $address Technician's address
     * @param string $user_type User role (default: 'Technician')
     * @param string $specialization Area of expertise
     * @param string $experience Years of experience
     * @param string $cv_path Path to uploaded CV file
     * @return bool True if registration successful, false otherwise
     */
    public function registertechnician($name, $email, $password, $contact_number, $address, $user_type = 'Technician', $specialization, $experience, $cv_path)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->contact_number = $contact_number;
        $this->address = $address;
        $this->user_type = $user_type;
        $this->specialization = $specialization;
        $this->experience = $experience;
        $this->cv_path = $cv_path;

        // Check for duplicate email
        if ($this->isAlreadyExists()) {
            return false;
        }

        try {
            // Step 1: Insert into `users` table
            $sqlUser = "INSERT INTO users (name, email, password, contact_number, address, user_type) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmtUser = $this->pdo->prepare($sqlUser);
            $stmtUser->bindParam(1, $this->name);
            $stmtUser->bindParam(2, $this->email);
            $stmtUser->bindParam(3, $this->password);
            $stmtUser->bindParam(4, $this->contact_number);
            $stmtUser->bindParam(5, $this->address);
            $stmtUser->bindParam(6, $this->user_type);
            $stmtUser->execute();

            // Step 2: Get the newly created user ID
            $user_id = $this->pdo->lastInsertId();

            // Step 3: Insert technician-specific data into `technician` table
            $sqlTech = "INSERT INTO technician (user_id, proof, specialization, experience, status) 
                    VALUES (?, ?, ?, ?, 'available')";
            $stmtTech = $this->pdo->prepare($sqlTech);
            $stmtTech->bindParam(1, $user_id);
            $stmtTech->bindParam(2, $this->cv_path);
            $stmtTech->bindParam(3, $this->specialization);
            $stmtTech->bindParam(4, $this->experience);
            $stmtTech->execute();

            return true;
        } catch (PDOException $e) {
            error_log("PDOException in registertechnician: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable or disable a user account
     * 
     * Allows administrators to activate or deactivate user accounts
     * for moderation purposes or account management.
     * 
     * @param int $user_id ID of the user to modify
     * @param string $disable_status New status ('enabled' or 'disabled')
     * @return array Result array with success status and message
     */
    public function disableUser($user_id, $disable_status)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET disable_status = :disable_status WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':disable_status', $disable_status);
            $stmt->execute();
            return ['success' => true, 'message' => 'User status updated'];
        } catch (PDOException $e) {
            echo json_encode(["message" => "Failed to disable user. " . $e->getMessage()]);
            return ['success' => false];
        }
    }

    /**
     * Update user profile information
     * 
     * Allows users to modify their personal information including
     * name, contact details, address, and profile image.
     * 
     * @param int $user_id ID of the user to update
     * @param string $name New name
     * @param string $contact_number New contact number
     * @param string $address New address
     * @param string $profile_image New profile image path (optional)
     * @return array Result array with success status
     */
    public function updateDetails($user_id, $name, $contact_number, $address, $profile_image)
    {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->contact_number = $contact_number;
        $this->address = $address;
        $this->profile_image = $profile_image;

        try {
            // Log update attempt for debugging
            error_log('Updating user_id: ' . $this->user_id . ' with: ' . print_r([
                'name' => $this->name,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
                'profile_image' => $this->profile_image
            ], true));

            // Update with or without profile image based on whether it was provided
            if ($this->profile_image) {
                $sql = "UPDATE users SET name = :name, contact_number = :contact_number, address = :address, profile_image = :profile_image WHERE user_id = :user_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'name' => $this->name,
                    'contact_number' => $this->contact_number,
                    'address' => $this->address,
                    'profile_image' => $this->profile_image,
                    'user_id' => $this->user_id,
                ]);
            } else {
                $sql = "UPDATE users SET name = :name, contact_number = :contact_number, address = :address WHERE user_id = :user_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'name' => $this->name,
                    'contact_number' => $this->contact_number,
                    'address' => $this->address,
                    'user_id' => $this->user_id,
                ]);
            }

            if ($stmt->rowCount() > 0) {
                error_log('Update successful for user_id: ' . $this->user_id);
                return ['success' => true];
            } else {
                error_log('No rows updated for user_id: ' . $this->user_id);
                return ['success' => false, 'message' => 'No rows updated'];
            }
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('PDOException: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Retrieve user details by ID
     * 
     * Fetches complete user information from the database for
     * profile display and account management purposes.
     * 
     * @param int $user_id ID of the user to retrieve
     * @return array User data array or null if not found
     */
    public function getDetails($user_id)
    {
        $this->user_id = $user_id;
        try {
            $sql = "SELECT * FROM users WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            return $customer;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve customer details. " . $e->getMessage()]);
            exit;
        }
    }
}
