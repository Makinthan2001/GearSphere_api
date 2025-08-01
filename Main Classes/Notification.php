<?php

/**
 * Notification Management Class
 * 
 * This class handles the notification system for the GearSphere platform.
 * It manages real-time notifications for users including duplicate prevention,
 * notification retrieval, and cleanup operations for system alerts and updates.
 * It provides methods for adding, retrieving, and deleting notifications,
 */

include_once __DIR__ . '/../DbConnector.php';

class Notification {
    private $pdo;  // Database connection object

    /**
     * Constructor - Initialize database connection
     * 
     * Establishes connection to the database for all notification operations
     */
    public function __construct() {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Add basic notification for a user
     * 
     * Creates a new notification entry with timestamp for immediate
     * user alert delivery. Used for system notifications and alerts.
     * 
     * @param int $user_id ID of the user to notify
     * @param string $message Notification message content
     * @return bool True if notification added successfully, false otherwise
     */
    public function addNotification($user_id, $message) {
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, message, date) VALUES (?, ?, NOW())");
        return $stmt->execute([$user_id, $message]);
    }

    /**
     * Add unique notification with duplicate prevention
     * 
     * Creates a notification only if a similar one doesn't exist within
     * the specified time window. Prevents notification spam for recurring
     * events like low stock alerts or system updates.
     * 
     * @param int $user_id ID of the user to notify
     * @param string $message Notification message content
     * @param int $hours_window Time window in hours to check for duplicates (default: 24)
     * @return bool True if notification processed successfully
     */
    public function addUniqueNotification($user_id, $message, $hours_window = 24) {
        // Check if a similar notification exists within the specified time window
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? 
            AND message = ? 
            AND date >= DATE_SUB(NOW(), INTERVAL ? HOUR)
        ");
        $stmt->execute([$user_id, $message, $hours_window]);
        $result = $stmt->fetch();
        
        // If no similar notification exists, create a new one
        if ($result['count'] == 0) {
            return $this->addNotification($user_id, $message);
        }
        
        // Return true but don't create duplicate
        return true;
    }

    /**
     * Retrieve all notifications for a user
     * 
     * Fetches all notifications for the specified user ordered by
     * date (newest first) for notification center display.
     * 
     * @param int $user_id ID of the user whose notifications to retrieve
     * @return array Array of notification data
     */
    public function getNotifications($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get notification count for a user
     * 
     * Returns the total number of notifications for badge display
     * and notification center counters.
     * 
     * @param int $user_id ID of the user
     * @return int Total number of notifications
     */
    public function getNotificationCount($user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Delete specific notification
     * 
     * Removes a notification after user interaction or when
     * notification is no longer relevant. Includes user verification
     * for security.
     * 
     * @param int $notification_id ID of the notification to delete
     * @param int $user_id ID of the user (for security verification)
     * @return bool True if notification deleted successfully, false otherwise
     */
    public function deleteNotification($notification_id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
        return $stmt->execute([$notification_id, $user_id]);
    }
}
?>