<?php

/**
 * Review Management Class
 * 
 * This class handles all review-related operations in the GearSphere system.
 * It manages customer reviews for products, technicians, and other entities,
 * including review submission, moderation, status updates, and retrieval
 * with comprehensive filtering and validation capabilities.
 * 
 * Features:
 * - Review submission with validation
 * - Review moderation and status management
 * - Multi-target review support (products, technicians, users)
 * - Advanced filtering and search capabilities
 * - Review analytics and reporting
 * @package GearSphere-BackEnd
 */

require_once __DIR__ . '/../DbConnector.php';

class Review
{
    /**
     * @var PDO Database connection instance
     */
    private $pdo;

    /**
     * Review Constructor
     * 
     * Initializes the Review class with a database connection
     * for performing review-related database operations.
     */
    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Add New Review
     * 
     * Creates a new review in the system with pending status for moderation.
     * Supports reviews for multiple target types including products, technicians, and users.
     * 
     * @param int $user_id ID of the user submitting the review
     * @param string $target_type Type of entity being reviewed ('product', 'technician', 'user')
     * @param int $target_id ID of the entity being reviewed
     * @param int $rating Rating score (typically 1-5 scale)
     * @param string $comment Review comment text
     * @return int The ID of the newly created review
     * @throws Exception If review creation fails
     */
    public function addReview($user_id, $target_type, $target_id, $rating, $comment)
    {
        try {
            // Log review submission for debugging and audit purposes
            error_log("addReview params: user_id=$user_id, target_type=$target_type, target_id=$target_id, rating=$rating, comment=$comment");
            
            // Insert review with pending status for admin moderation
            $sql = "INSERT INTO reviews (user_id, target_type, target_id, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $target_type, $target_id, $rating, $comment]);
            
            // Return the ID of the newly created review
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in addReview: " . $e->getMessage());
            throw new Exception("Failed to add review. Please try again later. SQL Error: " . $e->getMessage());
        }
    }

    /**
     * Get Reviews with Advanced Filtering
     * 
     * Retrieves reviews from the database with comprehensive filtering options
     * and joins user information for complete review data including target details.
     * 
     * @param array $filters Associative array of filter criteria:
     *                      - 'user_id': Filter by reviewer user ID
     *                      - 'target_type': Filter by review target type
     *                      - 'target_id': Filter by specific target ID
     *                      - 'status': Filter by review status ('pending', 'approved', 'rejected')
     * @return array Array of review records with user and target information
     */
    public function getReviews($filters = [])
    {
        // Build comprehensive query with user joins and target email resolution
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
        
        // Apply dynamic filtering based on provided criteria
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
        
        // Order by most recent reviews first
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Update Review Status (Moderation)
     * 
     * Updates the status of a review for moderation purposes.
     * Used by administrators to approve or reject submitted reviews.
     * 
     * @param int $id Review ID to update
     * @param string $status New status ('approved', 'rejected', 'pending')
     * @return int Number of affected rows (1 if successful, 0 if review not found)
     */
    public function updateReviewStatus($id, $status)
    {
        $sql = "UPDATE reviews SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status, $id]);
        return $stmt->rowCount();
    }

    /**
     * Delete Review
     * 
     * Permanently removes a review from the database.
     * This action cannot be undone and should be used with caution.
     * 
     * @param int $id Review ID to delete
     * @return int Number of deleted rows (1 if successful, 0 if review not found)
     * @throws Exception If deletion fails due to database error
     */
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

    /**
     * Get Latest Reviews
     * 
     * Retrieves the most recent reviews from the system with complete
     * user information and target details for dashboard displays.
     * 
     * @param int $limit Maximum number of reviews to retrieve (default: 5)
     * @return array Array of recent review records with user and target information
     */
    public function getLatestReviews($limit = 5)
    {
        // Query for latest reviews with comprehensive user and target information
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
