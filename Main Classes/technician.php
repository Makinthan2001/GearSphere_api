<?php

/**
 * Technician Management Class
 * 
 * This class handles all technician-specific functionality in the GearSphere system.
 * It extends the base User class and provides methods for technician management,
 * assignment handling, build request tracking, and profile management.
 * @package GearSphere-BackEnd
 */

include_once 'Main Classes/User.php';

class technician extends User
{
    private $technician_id;      // Unique technician identifier
    private $charge_per_day;     // Daily service charge rate

    /**
     * Constructor - Initialize technician with database connection
     * 
     * Calls the parent User constructor to establish database connection
     * and inherit all base user functionality.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get technician ID by user ID
     * 
     * Retrieves the technician-specific ID for a given user ID.
     * Used to link user accounts with technician records.
     * 
     * @param int $user_id User ID to look up
     * @return int|null|array Technician ID, null if not found, or error array
     */
    public function getTechnicianId($user_id)
    {
        $this->user_id = $user_id;
        try {
            $sql = "SELECT technician_id FROM technician WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result === false) {
                return null;
            }

            return $result['technician_id'];
        } catch (PDOException $e) {
            return ['error' => 'An error occurred while fetching data: ' . $e->getMessage()];
        }
    }

    /**
     * Get complete technician details
     * 
     * Combines user details from parent class with technician-specific
     * information including specialization, experience, and qualifications.
     * 
     * @param int $user_id User ID of the technician
     * @return array Combined user and technician data or error array
     */
    public function getTechnicianDetails($user_id)
    {
        try {
            // Get base user details from parent class
            $userDetails = parent::getDetails($user_id);

            // Get technician-specific details
            $sql = "SELECT * FROM technician WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $technicianDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            // Merge user and technician data
            $result = array_merge($userDetails, $technicianDetails);

            return $result;
        } catch (PDOException $e) {
            return ['error' => 'An error occurred while fetching data: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update technician availability status
     * 
     * Changes the technician's status (available, busy, unavailable).
     * Used for managing technician workload and assignment availability.
     * 
     * @param int $technician_id ID of the technician
     * @param string $status New status value
     * @return bool True if updated successfully, false otherwise
     */
    public function setStatus($technician_id, $status)
    {
        $this->technician_id = $technician_id;
        $this->status = $status;
        try {
            $sql = "UPDATE technician SET status = :status WHERE technician_id = :technician_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":technician_id", $this->technician_id);
            $stmt->bindParam(":status", $this->status);
            $rs = $stmt->execute();
            return $rs;
        } catch (PDOException $e) {
            return false; // Return false on error
        }
    }

    /**
     * Retrieve all technicians in the system
     * 
     * Fetches complete technician list with user details, qualifications,
     * and current status. Used for admin management and assignment selection.
     * 
     * @return array Array of all technician data with default profile images
     */
    public function getAllTechnicians()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT t.technician_id, u.*, t.proof, t.charge_per_day, t.specialization, t.experience, t.status, t.approve_status FROM users u INNER JOIN technician t ON u.user_id = t.user_id WHERE u.user_type = 'technician' ORDER BY u.user_id DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ensure every technician has a profile image (set default if missing)
            foreach ($users as &$user) {
                if (empty($user['profile_image'])) {
                    $user['profile_image'] = 'user_image.jpg'; // default image
                }
            }
            return $users ?: [];
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch technicians. " . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Update technician profile and service details
     * 
     * Updates both user information (via parent class) and technician-specific
     * data including daily rates and availability status.
     * 
     * @param int $user_id User ID of the technician
     * @param string $name Updated name
     * @param string $contact_number Updated contact number
     * @param string $address Updated address
     * @param string $profile_image Updated profile image path
     * @param int $technician_id Technician ID
     * @param float $charge_per_day Daily service charge
     * @param string $status Availability status
     * @return array Result array with success status
     */
    public function updateTechnicianDetails(
        $user_id,
        $name,
        $contact_number,
        $address,
        $profile_image,
        $technician_id,
        $charge_per_day,
        $status
    ) {
        // Update user details via parent class
        parent::updateDetails($user_id, $name, $contact_number, $address, $profile_image);
        
        $this->technician_id = $technician_id;
        $this->charge_per_day = $charge_per_day;
        $this->status = $status;
        
        error_log("Updating technician_id {$this->technician_id} with charge_per_day: {$this->charge_per_day} and status: {$this->status}");
        
        try {
            // Update technician-specific details
            $sql = "UPDATE technician SET charge_per_day = :charge_per_day, status = :status WHERE technician_id = :technician_id";
            $stmt = $this->pdo->prepare($sql);
            $rs = $stmt->execute([
                'charge_per_day' => $this->charge_per_day,
                'status' => $this->status,
                'technician_id' => $this->technician_id,
            ]);
            
            if ($rs) {
                error_log("Update succeeded.");
                return ['success' => true];
            } else {
                error_log("Update failed.");
                return ['success' => false];
            }
        } catch (PDOException $e) {
            error_log("PDOException: " . $e->getMessage());
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Assign technician to customer build request
     * 
     * Creates a new assignment linking a customer with a technician
     * for custom PC build services. Includes optional build instructions.
     * 
     * @param int $customer_id ID of the customer requesting service
     * @param int $technician_id ID of the assigned technician
     * @param string|null $instructions Special build instructions
     * @return array Result array with assignment ID or error message
     */
    public function assignTechnician($customer_id, $technician_id, $instructions = null)
    {
        try {
            $sql = "INSERT INTO technician_assignments (customer_id, technician_id, instructions, status) VALUES (:customer_id, :technician_id, :instructions, 'pending')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':technician_id', $technician_id, PDO::PARAM_INT);
            $stmt->bindParam(':instructions', $instructions, PDO::PARAM_STR);
            $stmt->execute();
            return ['success' => true, 'assignment_id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get technician information by technician ID
     * 
     * Retrieves complete technician profile including user details
     * for assignment and contact purposes.
     * 
     * @param int $technician_id Technician ID to look up
     * @return array|null Technician data or null if not found
     */
    public function getTechnicianByTechnicianId($technician_id)
    {
        try {
            $sql = "SELECT t.*, u.email, u.name, u.contact_number, u.address FROM technician t INNER JOIN users u ON t.user_id = u.user_id WHERE t.technician_id = :technician_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':technician_id', $technician_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get build requests assigned to a technician
     * 
     * Retrieves all build assignments for a specific technician including
     * customer details and project status. Used for technician dashboard.
     * 
     * @param int $technician_id ID of the technician
     * @return array Array of build request data with customer information
     */
    public function getBuildRequests($technician_id)
    {
        try {
            $sql = "SELECT 
                        ta.assignment_id,
                        ta.assigned_at,
                        ta.status,
                        ta.instructions,
                        u.name AS customer_name,
                        u.email AS customer_email,
                        u.contact_number AS customer_phone,
                        u.address AS customer_address,
                        u.profile_image AS customer_profile_image
                    FROM technician_assignments ta
                    INNER JOIN users u ON ta.customer_id = u.user_id
                    WHERE ta.technician_id = :technician_id
                    ORDER BY ta.assigned_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':technician_id', $technician_id, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results ?: [];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get recently registered technicians
     * 
     * Retrieves the most recently registered technicians for
     * dashboard displays and administrative monitoring.
     * 
     * @param int $limit Maximum number of technicians to return (default: 5)
     * @return array Array of latest technician data with default profile images
     */
    public function getLatestTechnicians($limit = 5)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT t.technician_id, u.*, t.proof, t.charge_per_day, t.specialization, t.experience, t.status FROM users u INNER JOIN technician t ON u.user_id = t.user_id WHERE u.user_type = 'technician' ORDER BY u.created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set default profile images for technicians without images
            foreach ($users as &$user) {
                if (empty($user['profile_image'])) {
                    $user['profile_image'] = 'user_image.jpg';
                }
            }
            return $users ?: [];
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch latest technicians. " . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Get total count of technicians in the system
     * 
     * Returns the total number of registered technicians for
     * dashboard statistics and administrative reporting.
     * 
     * @return int Total number of technicians
     */
    public function getTechnicianCount()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE user_type = 'technician'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch technician count. " . $e->getMessage()]);
            exit;
        }
    }
}
