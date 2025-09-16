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

    // Create order status notification for customer
    public function createOrderStatusNotification($order_id, $customer_id, $new_status) {
        try {
            // Create appropriate message based on status
            $status_messages = [
                'pending' => "Your order #{$order_id} has been confirmed and is being processed.",
                'processing' => "Your order #{$order_id} is now being processed by the seller.",
                'shipped' => "Great news! Your order #{$order_id} has been shipped and is on its way to you.",
                'delivered' => "Your order #{$order_id} has been delivered successfully!",
                'cancelled' => "Your order #{$order_id} has been cancelled. Please contact support if you have questions."
            ];

            $message = $status_messages[$new_status] ?? "Your order #{$order_id} status has been updated to: " . ucfirst($new_status);

            // Insert notification for customer using your existing table structure
            $stmt = $this->pdo->prepare("
                INSERT INTO notifications (user_id, message, date) 
                VALUES (?, ?, NOW())
            ");
            
            $result = $stmt->execute([$customer_id, $message]);
            
            if ($result) {
                error_log("Notification created successfully for customer {$customer_id}, order {$order_id}, status {$new_status}");
                return true;
            } else {
                error_log("Failed to create notification for customer {$customer_id}, order {$order_id}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error creating order status notification: " . $e->getMessage());
            return false;
        }
    }

    // Create new order notification for sellers
    public function createNewOrderNotification($order_id, $customer_id, $total_amount, $item_count) {
        try {
            // Get customer name for the notification
            $stmt = $this->pdo->prepare("SELECT name FROM users WHERE user_id = ?");
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch();
            $customer_name = $customer ? $customer['name'] : 'Customer';
            
            // Only notify sellers, not admins
            $stmt = $this->pdo->prepare("SELECT user_id, name FROM users WHERE user_type = 'seller'");
            $stmt->execute();
            $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $message = "🛒 New Order Received! Order #{$order_id} from {$customer_name} - {$item_count} item(s) - LKR " . number_format($total_amount, 2);
            
            // Send notification to each seller only
            $notifications_sent = 0;
            foreach ($sellers as $seller) {
                $seller_id = $seller['user_id'];
                
                // Add notification for this seller
                $stmt = $this->pdo->prepare("
                    INSERT INTO notifications (user_id, message, date) 
                    VALUES (?, ?, NOW())
                ");
                
                if ($stmt->execute([$seller_id, $message])) {
                    $notifications_sent++;
                    error_log("New order notification sent to seller {$seller_id} for order {$order_id}");
                }
            }
            
            return $notifications_sent > 0;
            
        } catch (Exception $e) {
            error_log("Error creating new order notification: " . $e->getMessage());
            return false;
        }
    }

    // Create product order notification for specific seller
    public function createSellerProductOrderNotification($seller_id, $order_id, $customer_name, $product_details) {
        try {
            $product_list = [];
            $total_seller_amount = 0;
            
            foreach ($product_details as $product) {
                $product_list[] = $product['name'] . " (Qty: " . $product['quantity'] . ")";
                $total_seller_amount += ($product['price'] * $product['quantity']);
            }
            
            $products_text = implode(", ", $product_list);
            $message = "🎯 New Order for Your Products! Order #{$order_id} from {$customer_name} - Products: {$products_text} - Amount: LKR " . number_format($total_seller_amount, 2);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO notifications (user_id, message, date) 
                VALUES (?, ?, NOW())
            ");
            
            return $stmt->execute([$seller_id, $message]);
            
        } catch (Exception $e) {
            error_log("Error creating seller product order notification: " . $e->getMessage());
            return false;
        }
    }

    // Send notification to all sellers
    public function notifyAllSellers($message) {
        try {
            // Get all sellers
            $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE user_type = 'seller'");
            $stmt->execute();
            $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Send notification to each seller
            $stmt = $this->pdo->prepare("
                INSERT INTO notifications (user_id, message, date) 
                VALUES (?, ?, NOW())
            ");
            
            $success = true;
            foreach ($sellers as $seller) {
                $result = $stmt->execute([$seller['user_id'], $message]);
                if (!$result) {
                    $success = false;
                    error_log("Failed to send notification to seller {$seller['user_id']}");
                }
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Error notifying all sellers: " . $e->getMessage());
            return false;
        }
    }
}
?>