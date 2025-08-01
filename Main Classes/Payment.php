<?php

/**
 * Payment Processing Class
 * 
 * This class handles payment transactions and financial operations in the GearSphere system.
 * It manages payment records, status tracking, and integration with order processing
 * for secure financial transaction management.
 * 
 */

require_once __DIR__ . '/../DbConnector.php';

class Payment
{
    private $pdo;  // Database connection object

    /**
     * Constructor - Initialize database connection
     * 
     * Establishes connection to the database for all payment operations
     */
    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Add payment record to the system
     * 
     * Creates a payment transaction record linking an order to payment
     * details including amount, method, and status. Used during checkout
     * process to track financial transactions.
     * 
     * @param int $order_id ID of the order being paid for
     * @param int $user_id ID of the user making payment
     * @param float $amount Payment amount
     * @param string $payment_method Payment method used (default: 'Card')
     * @param string $payment_status Initial payment status (default: 'pending')
     * @return int|false Payment ID if successful, false on failure
     */
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

    /**
     * Retrieve payment information by order ID
     * 
     * Fetches payment details for a specific order including
     * transaction status, amount, and payment method for
     * order tracking and financial reconciliation.
     * 
     * @param int $order_id ID of the order
     * @return array|false Payment data array or false if not found
     */
    public function getPaymentByOrderId($order_id)
    {
        $sql = "SELECT * FROM payment WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
