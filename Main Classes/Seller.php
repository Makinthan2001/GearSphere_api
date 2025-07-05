<?php
include_once 'Main Classes/User.php';

class Seller extends User{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDetails($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllSellers()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_type = 'seller' ORDER BY user_id DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: []; // Returns empty array if no users found
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch sellers. " . $e->getMessage()]);
            exit;
        }
    }
}
?> 