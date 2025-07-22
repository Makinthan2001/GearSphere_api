<?php
require_once __DIR__ . '/../DbConnector.php';

class Review {
    private $pdo;

    public function __construct() {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addReview($user_id, $target_type, $target_id, $rating, $comment) {
        try {
            error_log("addReview params: user_id=$user_id, target_type=$target_type, target_id=$target_id, rating=$rating, comment=$comment");
            $sql = "INSERT INTO reviews (user_id, target_type, target_id, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $target_type, $target_id, $rating, $comment]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in addReview: " . $e->getMessage());
            throw new Exception("Failed to add review. Please try again later. SQL Error: " . $e->getMessage());
        }
    }

    public function getReviews($filters = []) {
        $sql = "SELECT id, user_id, 
                       COALESCE(target_type, 'system') AS target_type, 
                       target_id, rating, comment, status, created_at, updated_at
                FROM reviews WHERE 1=1";
    
        $params = [];
        if (isset($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (isset($filters['target_type'])) {
            $sql .= " AND target_type = ?";
            $params[] = $filters['target_type'];
        }
        if (isset($filters['target_id'])) {
            $sql .= " AND target_id = ?";
            $params[] = $filters['target_id'];
        }
        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    

    public function updateReviewStatus($id, $status) {
        $sql = "UPDATE reviews SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status, $id]);
        return $stmt->rowCount();
    }
}