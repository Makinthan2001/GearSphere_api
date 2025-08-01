<?php

/**
 * Message Management Class
 * 
 * This class handles customer contact messages and communication in the GearSphere system.
 * It manages message storage, retrieval, and deletion for customer support and
 * administrative communication purposes.
 * 
 */

require_once __DIR__ . '/../DbConnector.php';

class Message
{
    private $pdo;  // Database connection object

    /**
     * Constructor - Initialize database connection
     * 
     * Establishes connection to the database for all message operations
     */
    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Add new customer message to the system
     * 
     * Stores customer contact form submissions with combined subject
     * and message content for administrative review and response.
     * 
     * @param string $name Customer's name
     * @param string $email Customer's email address
     * @param string $subject Message subject line
     * @param string $message Message content
     * @return array Result array with success status and message
     */
    public function addMessage($name, $email, $subject, $message)
    {
        try {
            // Combine subject and message for storage
            $fullMessage = "Subject: $subject\nMessage: $message";
            
            $sql = "INSERT INTO message (name, email, message) VALUES (:name, :email, :message)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':message' => $fullMessage
            ]);
            
            return [
                'success' => true,
                'message' => 'Message sent successfully.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve all customer messages
     * 
     * Fetches all messages from the database ordered by date (newest first)
     * for administrative review and customer support management.
     * 
     * @return array Array of all messages or empty array on error
     */
    public function getAllMessages()
    {
        try {
            $sql = "SELECT * FROM message ORDER BY date DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get recent customer messages
     * 
     * Retrieves the most recent messages for dashboard displays
     * and quick administrative overview of customer communications.
     * 
     * @param int $limit Maximum number of messages to retrieve (default: 5)
     * @return array Array of latest messages or empty array on error
     */
    public function getLatestMessages($limit = 5)
    {
        try {
            $sql = "SELECT * FROM message ORDER BY date DESC LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Delete customer message
     * 
     * Removes a message from the system after administrative review
     * or when message is no longer needed for reference.
     * 
     * @param int $message_id ID of the message to delete
     * @return array Result array with success status and message
     */
    public function deleteMessage($message_id)
    {
        try {
            $sql = "DELETE FROM message WHERE message_id = :message_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':message_id' => $message_id]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Message deleted successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Message not found.'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
