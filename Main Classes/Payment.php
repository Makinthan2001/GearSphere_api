<?php
require_once __DIR__ . '/../DbConnector.php';

class Payment
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addPayment($order_id, $user_id, $amount, $payment_method = 'Card', $payment_status = 'pending')
    {
        try {
            $sql = "INSERT INTO payment (order_id, user_id, amount, payment_method, payment_status) VALUES (:order_id, :user_id, :amount, :payment_method, :payment_status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':order_id' => $order_id,
                ':user_id' => $user_id,
                ':amount' => $amount,
                ':payment_method' => $payment_method,
                ':payment_status' => $payment_status
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getPaymentByOrderId($order_id)
    {
        $sql = "SELECT * FROM payment WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
