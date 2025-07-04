<?php
include_once 'Main Classes/User.php';
class technician extends User{

    public function __construct()
    {
        parent::__construct();
    }
    public function getTechnicianId($user_id) {
        $this->user_id = $user_id;
        try {
            $sql = "SELECT technician_id FROM provider WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result === false) {
                return null;
            }

            return $result['technician_id'];
        } catch (PDOException $e) {
            return ['error' => 'An error occurred while fetching data: ' . $e->getMessage()];
        }
    }

     public function getTechnicianDetails($user_id)
    {
        try {
            $userDetails = parent::getDetails($user_id);

            $sql = "SELECT * FROM technician WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $technicianDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            $result = array_merge($userDetails, $technicianDetails);

            return $result;
        } catch (PDOException $e) {
            return ['error' => 'An error occurred while fetching data: ' . $e->getMessage()];
        }
    }
    public function setStatus($technician_id,$status) {
         $this->technician_id = $technician_id;
         $this->status = $status;
         try {
            $sql = "UPDATE technician SET status = :status WHERE technician_id = :technician_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":technician_id", $this->technician_id);
            $stmt->bindParam(":status", $this->status);
            $rs = $stmt->execute();
            return $rs;
        } catch (PDOException $e) {
            return false; // Return false on error
        }
    }

   
    // public function getAllTechnicians() {
    //     try {
    //         $stmt = $this->pdo->prepare("SELECT
    //         user.user_id,
    //         user.name,
    //         user.username,
    //         user.email,
    //         user.contact_number,
    //         user.user_type,
    //         user.address,
    //         user.disable_status,
    //         user.national_id,
    //         user.profile_image,
    //         technician.technician_id AS technician_id,
    //         technician.experience AS experience,
    //         technician.charge_per_day AS charge,
    //         technician.specialization AS specialization,
    //         technician.status AS status,
    //         service.service_name AS service_category
    //       FROM user 
    //       INNER JOIN provider ON user.user_id = provider.user_id
    //       INNER JOIN service ON provider.service_category_id = service.service_category_id
    //       WHERE user.user_type = 'provider' ORDER BY user.user_id DESC");
    //         $stmt->execute();
    //         $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //         return $users;
    //     } catch (PDOException $e) {
    //         http_response_code(500);
    //         echo json_encode(["message" => "Failed to fetch providers. " . $e->getMessage()]);
    //         exit;
    //     }
    // }

    public function updateTechnicianDetails($user_id, $name, $contact_number, $address, $profile_image, $technician_id, $experience, $specialization, $charge_per_day)
    {
        parent::updateDetails($user_id, $name, $contact_number, $address, $profile_image);

        $this->technician_id = $technician_id;
        $this->specialization = $specialization;
        $this->experience = $experience;
        $this->charge_per_day = $charge_per_day;
        try {
            $sql = "UPDATE technician SET specialization = :specialization, experience = :experience, charge_per_day = :charge_per_day WHERE technician_id = :technician_id";
            $stmt = $this->pdo->prepare($sql);
            $rs = $stmt->execute([
                'specialization' => $this->specialization,
                'experience' => $this->experience,
                'charge_per_day' => $this->charge_per_day,
                'technician_id' => $this->technician_id,
            ]);

            if ($rs) {
                return ['success' => true];
            } else {
                return ['success' => false];
            }
        } catch (PDOException $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

}






























    
    // public function registerTechnician($name, $email, $password, $contact_number, $address, $specialization, $experience, $cv_path)
    // {
    //     $this->name = $name;
    //     $this->email = $email;
    //     $this->password = password_hash($password, PASSWORD_BCRYPT);
    //     $this->contact_number = $contact_number;
    //     $this->address = $address;
    //     $this->user_type = 'technician';
    //     $this->specialization = $specialization;
    //     $this->experience = $experience;
    //     $this->cv_path = $cv_path;

    //     if ($this->isAlreadyExists()) {
    //         return ['success' => false, 'message' => "Technician Already Verified"];
    //     }

    //     try {
    //         $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, contact_number, address, user_type, cv_path, specialization, experience) 
    //             VALUES (:name, :email, :password, :contact_number, :address, :user_type, :cv_path, :specialization, :experience)");
    //         $stmt->bindParam(':name', $this->name);
    //         $stmt->bindParam(':email', $this->email);
    //         $stmt->bindParam(':password', $this->password);
    //         $stmt->bindParam(':contact_number', $this->contact_number);
    //         $stmt->bindParam(':address', $this->address);
    //         $stmt->bindParam(':user_type', $this->user_type);
    //         $stmt->bindParam(':cv_path', $this->cv_path);
    //         $stmt->bindParam(':specialization', $this->specialization);
    //         $stmt->bindParam(':experience', $this->experience);

    //         if ($stmt->execute()) {
    //             return ['success' => true, 'message' => "Technician registered successfully"];
    //         } else {
    //             return ['success' => false, 'message' => "Failed to register Technician"];
    //         }
    //     } catch (PDOException $e) {
    //         return ['success' => false, 'message' => "Failed to process registration: " . $e->getMessage()];
    //     }
    // }
















// class Technician extends User
// {
//     private $technician_id;
//     private $verify_id;
//     private $experience;
//     private $charge_per_day;
//     private $qualification;
    
    

//     public function __construct()
//     {
//         parent::__construct();
//     }

//     public function registerTechnician($name, $email, $password,$contact_number , $address, $specialization,$experience)
//     {  
//         $this->name = $name;
//         $this->email = $email;
//         $this->password = $password;
//         $this->contact_number = $contact_number;
//         $this->address = $address;
//         $this->specialization=$specialization;
//         $this->experience = $experience;
//         $this->user_type = 'Technician';

//         if($this->isAlreadyExists())
//       {
//         return ['success' => false, 'message' => "Technician Already Verified"];;
//       }

//         try{
//             $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, user_type, address, contact_number) 
//                 VALUES (:name, :email, :password, :user_type, :address, :contact_number)");
//             $stmt->bindParam(':name', $this->name);
//             $stmt->bindParam(':email', $this->email);
//             $stmt->bindParam(':password', $this->password);
//             $stmt->bindParam(':user_type', $this->user_type);
//             $stmt->bindParam(':address', $this->address);
//             $stmt->bindParam(':contact_number', $this->contact_number);
    
//             if ($stmt->execute()) {
//                 $this->user_id = $this->pdo->lastInsertId();
    
          
//             $st = $this->pdo->prepare("INSERT INTO technician (specialization, user_id, experience) 
//             VALUES ( :sspecialization, :user_id, :experience)");
            
//             $st->bindParam(':specialization', $this->specialization);
//             $st->bindParam(':user_id', $this->user_id);
//             $st->bindParam(':experience', $this->experience);
    
//                 if ($st->execute()) {
//                     return ['success' => true, 'message' => "Technician verified successfully"];
//                 } else {
//                     return ['success' => false, 'message' => "Failed to verify Technician: "];
//                 }
//             } else {
//                 return ['success' => false, 'message' => "Failed to verify Technician as an user: "];
//             }
//         }
//         catch(PDOException $e)
//         {
//             return ['success' => false, 'message' => "Failed to process verification: " . $e->getMessage()];
//         }
//     }

//     public function checkEmailExists($email)
//     {
//         $this->email = $email;
//         try{
//             $stmt = $this->pdo->prepare("SELECT email FROM users WHERE email=:email");
//             $stmt->bindParam(':email',$this->email);
//             $stmt->execute();
            
//             if ($stmt->rowCount() > 0) {
//                 return true; // Email exists
//             } else {
//                 return false; // Email does not exist
//             }
//     }
//     catch (PDOException $e) {
//         http_response_code(500);
//         echo json_encode(["message" => "Failed to verify email. " . $e->getMessage()]);
//         return false;
//     }
// }
// }