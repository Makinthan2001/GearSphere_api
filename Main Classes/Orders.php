<?php
require_once __DIR__ . '/../DbConnector.php';

class Orders
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function createOrder($user_id, $total_amount, $assignment_id = null, $status = 'pending')
    {
        try {
            $sql = "INSERT INTO orders (user_id, total_amount, assignment_id, status) VALUES (:user_id, :total_amount, :assignment_id, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':total_amount' => $total_amount,
                ':assignment_id' => $assignment_id,
                ':status' => $status
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getOrderById($order_id)
    {
        $sql = "SELECT * FROM orders WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAssignment($order_id, $assignment_id)
    {
        try {
            $sql = "UPDATE orders SET assignment_id = :assignment_id WHERE order_id = :order_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':assignment_id' => $assignment_id,
                ':order_id' => $order_id
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Fetch all orders for a user
    public function getOrdersByUserId($user_id)
    {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
