<?php
include_once 'Main Classes/User.php';
class technician extends User{

    public function __construct()
    {
        parent::__construct();
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