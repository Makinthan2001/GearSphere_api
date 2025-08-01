<?php

/**
 * Order Items Management Class
 * 
 * This class handles individual line items within orders in the GearSphere system.
 * It manages the relationship between orders and products, tracking quantities,
 * prices, and detailed product information for order processing and analytics.
 * It provides methods for adding items to orders, retrieving order item details,
 * and getting detailed order items with product information.
 */

require_once __DIR__ . '/../DbConnector.php';

class OrderItems
{
    private $pdo;  // Database connection object

    /**
     * Constructor - Initialize database connection
     * 
     * Establishes connection to the database for all order item operations
     */
    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Add item to an order
     * 
     * Creates an order line item linking a product to an order with
     * quantity and price information. Used during checkout process
     * to record what was purchased.
     * 
     * @param int $order_id ID of the order to add item to
     * @param int $product_id ID of the product being ordered
     * @param int $quantity Number of items ordered
     * @param float $price Price per unit at time of order
     * @return bool True if item added successfully, false otherwise
     */
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

    /**
     * Get basic order items by order ID
     * 
     * Retrieves all line items for a specific order with basic
     * order item information (IDs, quantities, prices).
     * 
     * @param int $order_id ID of the order
     * @return array Array of order item data
     */
    public function getItemsByOrderId($order_id)
    {
        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get detailed order items with product information
     * 
     * Retrieves order items joined with product details including
     * names, images, and categories for comprehensive order display
     * and customer order history.
     * 
     * @param int $order_id ID of the order
     * @return array Array of detailed order item data with product information
     */
    public function getDetailedItemsByOrderId($order_id)
    {
        $sql = "SELECT oi.*, p.name, p.image_url, p.category FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
