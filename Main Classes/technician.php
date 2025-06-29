

<?php
include_once 'Main Classes/User.php';

class Technician extends User
{
    private $technician_id;
    private $verify_id;
    private $experience;
    private $charge_per_day;
    private $qualification;
    
    

    public function __construct()
    {
        parent::__construct();
    }

    public function registerTechnician($name, $email, $password,$contact_number , $address, $verify_id,$specialization)
    {  
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->contact_number = $contact_number;
        $this->address = $address;
        $this->verify_id = $verify_id;
        $this->specialization=$specialization;
        $this->user_type = 'provider';

        if($this->isAlreadyExists())
      {
        return ['success' => false, 'message' => "Provider Already Verified"];;
      }

        try{
            $stmt = $this->pdo->prepare("INSERT INTO user (name, email, password, user_type, address, contact_number) 
                VALUES (:name, :email, :password, :user_type, :address, :contact_number)");
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':user_type', $this->user_type);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':contact_number', $this->contact_number);
    
            if ($stmt->execute()) {
                $this->user_id = $this->pdo->lastInsertId();
    
          
            $st = $this->pdo->prepare("INSERT INTO technician (verify_id, specialization, user_id) 
                    VALUES (:verify_id, :description, :service_category_id, :services, :user_id)");
            $st->bindParam(':verify_id', $this->verify_id);
            $st->bindParam(':specialization', $this->specialization);
            $st->bindParam(':user_id', $this->user_id);
    
                if ($st->execute()) {
                    return ['success' => true, 'message' => "Provider verified successfully"];
                } else {
                    return ['success' => false, 'message' => "Failed to verify provider: "];
                }
            } else {
                return ['success' => false, 'message' => "Failed to verify provider as an user: "];
            }
        }
        catch(PDOException $e)
        {
            return ['success' => false, 'message' => "Failed to process verification: " . $e->getMessage()];
        }
    }

    public function checkEmailExists($email)
    {
        $this->email = $email;
        try{
            $stmt = $this->pdo->prepare("SELECT email FROM user WHERE email=:email");
            $stmt->bindParam(':email',$this->email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return true; // Email exists
            } else {
                return false; // Email does not exist
            }
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to verify email. " . $e->getMessage()]);
        return false;
    }
}
}