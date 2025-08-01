<?php
require_once __DIR__ . '/../DbConnector.php';

class Review
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addReview($user_id, $target_type, $target_id, $rating, $comment)
    {
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

    public function getReviews($filters = [])
    {
        $sql = "SELECT r.*, u.name AS username, u.name AS sender_name, u.user_type AS sender_type, u.profile_image,
            CASE 
                WHEN LOWER(r.target_type) = 'technician' THEN (
                    SELECT u2.email FROM technician t2 JOIN users u2 ON t2.user_id = u2.user_id WHERE t2.technician_id = r.target_id LIMIT 1
                )
                WHEN LOWER(r.target_type) IN ('user', 'customer') THEN (
                    SELECT u3.email FROM users u3 WHERE u3.user_id = r.target_id LIMIT 1
                )
                ELSE NULL
            END AS target_email
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            WHERE 1=1";
        $params = [];
        if (isset($filters['user_id'])) {
            $sql .= " AND r.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (isset($filters['target_type'])) {
            $sql .= " AND r.target_type = ?";
            $params[] = $filters['target_type'];
        }
        if (isset($filters['target_id'])) {
            $sql .= " AND r.target_id = ?";
            $params[] = $filters['target_id'];
        }
        if (isset($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function updateReviewStatus($id, $status)
    {
        $sql = "UPDATE reviews SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status, $id]);
        return $stmt->rowCount();
    }

    public function deleteReview($id)
    {
        try {
            $sql = "DELETE FROM reviews WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error in deleteReview: " . $e->getMessage());
            throw new Exception("Failed to delete review. Please try again later.");
        }
    }

    public function getLatestReviews($limit = 5)
    {
        $sql = "SELECT r.*, u.name AS username, u.name AS sender_name, u.user_type AS sender_type, u.profile_image,
            CASE 
                WHEN LOWER(r.target_type) = 'technician' THEN (
                    SELECT u2.email FROM technician t2 JOIN users u2 ON t2.user_id = u2.user_id WHERE t2.technician_id = r.target_id LIMIT 1
                )
                WHEN LOWER(r.target_type) IN ('user', 'customer') THEN (
                    SELECT u3.email FROM users u3 WHERE u3.user_id = r.target_id LIMIT 1
                )
                ELSE NULL
            END AS target_email
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            ORDER BY r.created_at DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
