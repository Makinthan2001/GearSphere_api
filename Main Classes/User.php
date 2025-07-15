<?php
require_once 'DbConnector.php';

abstract class User
{
    protected $user_id;
    protected $name;
    protected $email;
    protected $password;
    protected $contact_number;
    protected $address;
    protected $user_type;
    protected $status;
    protected $specialization;
    protected $experience;
    protected $hourly_rate;
    protected $cv_path;
    protected $disable_status;
    protected $pdo;
    protected $profile_image;



    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function isAlreadyExists()
    {
        $query = "SELECT email FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }




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
            return false; // Make sure to return false on exception
        }
    }


    public function login($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
        try {
            $sql = "SELECT user_id, password, user_type, disable_status FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $this->email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($this->password, $user['password'])) {
                if ($user['disable_status'] === 'disabled') {
                    return ['success' => false, 'message' => 'Your account has been disabled. Please contact support.'];
                }
                $this->user_id = $user['user_id'];
                $this->user_type = $user['user_type'];
                $response = [
                    'user_type' => $this->user_type,
                    'user_id' => $this->user_id,
                    'success' => true,
                    'message' => 'Login Successful...'
                ];
                // If technician, fetch technician_id and add to response
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
            return ["success" => false, "message" => "Incorrect email or password..."];
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to login. " . $e->getMessage()]);
        }
    }

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



    public function registerUser($name, $email, $password, $contact_number, $address, $user_type = 'customer')
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->contact_number = $contact_number;
        $this->address = $address;
        $this->user_type = $user_type;

        if ($this->isAlreadyExists()) {
            // Optionally: throw error or log that user exists
            // Just returning false here means frontend will show generic message
            return false;
        }

        try {
            $sql = "INSERT INTO users (name, email, password, contact_number, address, user_type) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $this->name);
            $stmt->bindParam(2, $this->email);
            $stmt->bindParam(3, $this->password);
            $stmt->bindParam(4, $this->contact_number);
            $stmt->bindParam(5, $this->address);
            $stmt->bindParam(6, $this->user_type);
            $rs = $stmt->execute();

            if ($rs) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Log error to PHP error log for debugging
            error_log("PDOException in registerUser: " . $e->getMessage());
            // Return false after catching exception
            return false;
        }
    }


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

            // Step 2: Get the last inserted user ID
            $user_id = $this->pdo->lastInsertId();

            // Step 3: Insert into `technician` table
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

    public function updateDetails($user_id, $name, $contact_number, $address, $profile_image)
    {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->contact_number = $contact_number;
        $this->address = $address;
        $this->profile_image = $profile_image;

        try {
            error_log('Updating user_id: ' . $this->user_id . ' with: ' . print_r([
                'name' => $this->name,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
                'profile_image' => $this->profile_image
            ], true));

            if ($this->profile_image) {
                $sql = "UPDATE users SET name = :name,  contact_number = :contact_number, address = :address, profile_image = :profile_image WHERE user_id = :user_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'name' => $this->name,
                    'contact_number' => $this->contact_number,
                    'address' => $this->address,
                    'profile_image' => $this->profile_image,
                    'user_id' => $this->user_id,
                ]);
            } else {
                $sql = "UPDATE users SET name = :name,  contact_number = :contact_number, address = :address WHERE user_id = :user_id";
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
