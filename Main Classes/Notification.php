<?php
include_once __DIR__ . '/../DbConnector.php';

class Notification {
    private $pdo;

    public function __construct() {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    // Add a notification for a user (seller)
    public function addNotification($user_id, $message) {
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, message, date) VALUES (?, ?, NOW())");
        return $stmt->execute([$user_id, $message]);
    }

    // Add a notification only if it doesn't already exist (prevents duplicates)
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

    // Get all notifications for a user (seller)
    public function getNotifications($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get count of notifications for a user (seller)
    public function getNotificationCount($user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return $row ? (int)$row['count'] : 0;
    }

    // Delete a notification by ID
    public function deleteNotification($notification_id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
        return $stmt->execute([$notification_id, $user_id]);
    }
}
?>