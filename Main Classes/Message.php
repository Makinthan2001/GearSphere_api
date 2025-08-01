<?php
require_once __DIR__ . '/../DbConnector.php';

class Message
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addMessage($name, $email, $subject, $message)
    {
        try {
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
