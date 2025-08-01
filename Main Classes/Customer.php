<?php

/**
 * Customer Class
 * 
 * This class handles customer-specific functionality in the GearSphere system.
 * It extends the base User class and provides methods for customer management,
 * including retrieving customer lists and updating customer profiles.
 * @package GearSphere-BackEnd
 */

include_once 'Main Classes/User.php';

class Customer extends User
{
    /**
     * Constructor - Initialize Customer with database connection
     * 
     * Calls the parent User constructor to establish database connection
     * and inherit all base user functionality.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve all customers from the database
     * 
     * Fetches all users with 'customer' user type from the database,
     * ordered by user ID in descending order (newest first).
     * Used by admin dashboard for customer management.
     * 
     * @return array Array of customer data or empty array if none found
     */
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

    /**
     * Get the most recently registered customers
     * 
     * Retrieves a limited number of the latest customer registrations,
     * ordered by creation date. Useful for dashboard displays and
     * recent activity monitoring.
     * 
     * @param int $limit Maximum number of customers to retrieve (default: 5)
     * @return array Array of latest customer data
     */
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

    /**
     * Update customer profile details
     * 
     * Overrides the parent updateDetails method to provide customer-specific
     * profile updating. Delegates to parent implementation while maintaining
     * the same interface for consistency.
     * 
     * @param int $user_id Customer's user ID
     * @param string $name Updated name
     * @param string $contact_number Updated contact number
     * @param string $address Updated address
     * @param string $profile_image Updated profile image path
     * @return array Result array with success status
     */
    public function updateDetails($user_id, $name, $contact_number, $address, $profile_image)
    {
        // Delegate to parent class implementation
        return parent::updateDetails($user_id, $name, $contact_number, $address, $profile_image);
    }
}
