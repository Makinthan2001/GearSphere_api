<?php
include_once 'Main Classes/User.php';
class technician extends User
{
    private $technician_id;
    private $charge_per_day;

    public function __construct()
    {
        parent::__construct();
    }
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

    public function getTechnicianDetails($user_id)
    {
        try {
            $userDetails = parent::getDetails($user_id);

            $sql = "SELECT * FROM technician WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $technicianDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            $result = array_merge($userDetails, $technicianDetails);

            return $result;
        } catch (PDOException $e) {
            return ['error' => 'An error occurred while fetching data: ' . $e->getMessage()];
        }
    }
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

    public function getAllTechnicians()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT t.technician_id, u.*, t.proof, t.charge_per_day, t.specialization, t.experience, t.status FROM users u INNER JOIN technician t ON u.user_id = t.user_id WHERE u.user_type = 'technician' ORDER BY u.user_id DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Ensure every technician has a profile_image value
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
        parent::updateDetails($user_id, $name, $contact_number, $address, $profile_image);
        $this->technician_id = $technician_id;
        $this->charge_per_day = $charge_per_day;
        $this->status = $status;
        error_log("Updating technician_id {$this->technician_id} with charge_per_day: {$this->charge_per_day} and status: {$this->status}");
        try {
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
}
