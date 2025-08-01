<?php
/**
 * Seller Management Class
 * 
 * This class extends the User class to handle seller-specific operations
 * in the GearSphere system. It manages seller accounts, profiles, and
 * provides specialized functionality for users with seller privileges.
 * 
 * Features:
 * - Seller profile management
 * - Seller account retrieval and listing
 * - Inheritance of core user functionality
 * - Seller-specific data operations
 * 
 * @extends User
 */

include_once 'Main Classes/User.php';

class Seller extends User
{
    /**
     * Seller Constructor
     * 
     * Initializes the Seller class by calling the parent User constructor
     * to inherit database connection and core user functionality.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Seller Details
     * 
     * Retrieves detailed information for a specific seller by their user ID.
     * This method fetches all user data associated with the seller account.
     * 
     * @param int $user_id The unique identifier of the seller
     * @return array|false Associative array of seller details or false if not found
     */
    public function getDetails($user_id) 
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All Sellers
     * 
     * Retrieves a list of all users with seller privileges in the system.
     * Results are ordered by user ID in descending order (newest first).
     * 
     * @return array Array of seller records, empty array if none found
     * @throws Exception Outputs JSON error response and exits on database failure
     */
    public function getAllSellers()
    {
        try {
            // Query for all sellers ordered by newest first
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_type = 'seller' ORDER BY user_id DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return sellers array or empty array if none found
            return $users ?: [];
        } catch (PDOException $e) {
            // Handle database errors with proper HTTP response
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch sellers. " . $e->getMessage()]);
            exit;
        }
    }
}
?>