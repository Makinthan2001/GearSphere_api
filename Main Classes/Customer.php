<?php
include_once 'Main Classes/User.php';

class Customer extends User
{

    public function __construct()
    {
        parent::__construct();
    }




    public function getAllCustomers()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_type = 'customer' ORDER BY user_id DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: []; // Returns empty array if no users found
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch customers. " . $e->getMessage()]);
            exit;
        }
    }

    public function getLatestCustomers($limit = 5)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_type = 'customer' ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch latest customers. " . $e->getMessage()]);
            exit;
        }
    }

    public function updateDetails($user_id, $name, $contact_number, $address, $profile_image)
    {
        return parent::updateDetails($user_id, $name, $contact_number, $address, $profile_image);
    }
}
