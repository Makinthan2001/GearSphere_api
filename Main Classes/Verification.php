

<?php

require_once 'DbConnector.php';

class Verification {
    private $verify_id;
    private $technician_name;
    private $technician_password;
    private $technician_email;
    private $registered_date;
    private $file;
    private $technician_address;
    private $technician_specialization;
    private $pdo;

    public function __construct() {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addVerification($technician_name, $technician_password, $technician_email, $file, $technician_address, $technician_specialization) {
        $this->technician_name = $technician_name;
        $this->technician_password = $technician_password;
        $this->technician_email = $technician_email;
        $this->file = $file;
        $this->technician_address = $technician_address;
        $this->technician_specialization = $technician_specialization;

        try {
            $targetDir = "verifypdfs/";
            $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

            // Only allow PDF files
            if ($fileType != "pdf") {
                http_response_code(400);
                echo json_encode(["message" => "Only PDF files are allowed."]);
                return false;
            }

            // Limit file size (5MB max)
            if ($file["size"] > 5000000) {
                http_response_code(400);
                echo json_encode(["message" => "File is too large."]);
                return false;
            }

            // Unique filename for storage
            $uniqueFileName = uniqid() . "_" . basename($file["name"]);
            $targetFile = $targetDir . $uniqueFileName;

            // Upload file to server
            if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
                http_response_code(500);
                echo json_encode(["message" => "Failed to upload file."]);
                return false;
            }

            // Hash password securely
            $hashedPassword = password_hash($this->technician_password, PASSWORD_BCRYPT);

            // Insert verification record in database
            $stmt = $this->pdo->prepare(
                "INSERT INTO verification (technician_name,  technician_password, technician_email, proof, technician_address, technician_specialization) 
                VALUES (:technician_name, :technician_password, :technician_email, :proof, :technician_address, :technician_specialization)"
            );
            $stmt->bindParam(':provider_name', $this->technician_name);
            $stmt->bindParam(':provider_password', $hashedPassword);
            $stmt->bindParam(':provider_email', $this->technician_email);
            $stmt->bindParam(':proof', $uniqueFileName);
            $stmt->bindParam(':provider_address', $this->technician_address);
            $stmt->bindParam(':services', $this->technician_specialization);

            if ($stmt->execute()) {
                http_response_code(200);
                return true;
            } else {
                http_response_code(500);
                return false;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to process verification. " . $e->getMessage()]);
            return false;
        }
    }

    public function getVerifications() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM verification");
            $stmt->execute();
            $verification = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $verification;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve verifications. " . $e->getMessage()]);
            exit;
        }
    }

    public function deleteVerification($verify_id) {
        $this->verify_id = $verify_id;
        try {
            $stmt = $this->pdo->prepare("DELETE FROM verification WHERE verify_id = :verify_id");
            $stmt->bindParam(':verify_id', $this->verify_id);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete verification data. " . $e->getMessage()]);
            exit;
        }
    }

    
    public function verifyTechnician($verify_id) {
    try {
        $stmt = $this->pdo->prepare("SELECT * FROM verification WHERE verify_id = :verify_id");
        $stmt->bindParam(':verify_id', $verify_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $verificationData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($verificationData) {
            return $verificationData;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => "Failed to fetch provider verification data: " . $e->getMessage()
        ];
    }
    }
}


