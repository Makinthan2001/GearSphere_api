<?php

/**
 * Administrator Management Class
 * 
 * This class handles administrator-specific functionality in the GearSphere system.
 * It extends the base User class and provides methods for system administration,
 * user management, analytics, and administrative operations.
 * 
 */

include_once 'Main Classes/User.php';

class Admin extends User{
    
    /**
     * Constructor - Initialize admin with database connection
     * 
     * Calls the parent User constructor to establish database connection
     * and inherit all base user functionality.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get user type distribution statistics
     * 
     * Retrieves count of users by type (excluding admins) for
     * dashboard analytics and user management overview.
     * 
     * @return array|null Array of user type counts or null on error
     */
    public function getUserTypeCount()
    {
        try {
            $query = "SELECT user_type, COUNT(*) as count 
                      FROM users 
                      WHERE user_type != 'admin'
                      GROUP BY user_type";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get top performing services analytics
     * 
     * Retrieves the highest revenue-generating services based on
     * booking data for business intelligence and performance tracking.
     * 
     * @return array|null Array of top services with income data or null on error
     */
    public function getTopPerformingServices()
    {
        try {
            $query = "SELECT s.service_name, COUNT(*) * 500 as income 
                      FROM booking b
                      JOIN service s ON b.service_category_id = s.service_category_id
                      WHERE b.booking_status != 'Declined-provider'
                      GROUP BY s.service_name 
                      ORDER BY income DESC 
                      LIMIT 5"; 
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Enable or disable user accounts
     * 
     * Delegates to parent class implementation for user account
     * management and moderation purposes.
     * 
     * @param int $user_id ID of the user to modify
     * @param string $disable_status New account status
     * @return array Result with success status and message
     */
    public function disableUser($user_id, $disable_status) {
        return parent::disableUser($user_id, $disable_status);
    }

    /**
     * Get user details by ID
     * 
     * Retrieves complete user information for administrative
     * purposes and user management operations.
     * 
     * @param int $user_id ID of the user to retrieve
     * @return array|false User data array or false if not found
     */
    public function getDetails($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}