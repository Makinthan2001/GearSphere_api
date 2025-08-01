<?php
require_once __DIR__ . '/../DbConnector.php';

class OrderItems
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addOrderItem($order_id, $product_id, $quantity, $price)
    {
        try {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':price' => $price
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getItemsByOrderId($order_id)
    {
        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailedItemsByOrderId($order_id)
    {
        $sql = "SELECT oi.*, p.name, p.image_url, p.category FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
