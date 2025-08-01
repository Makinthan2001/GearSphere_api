<?php

/**
 * Shopping Cart Management Class
 * 
 * This class handles all shopping cart operations for the GearSphere system.
 * It manages adding, removing, updating, and retrieving cart items for users.
 * Includes functionality for quantity management and cart persistence.
 * 
 * @author GearSphere Team
 * @version 1.0
 */

require_once __DIR__ . '/../DbConnector.php';

class Cart
{
    private $pdo;  // Database connection object

    /**
     * Constructor - Initialize database connection
     * 
     * Establishes connection to the database for all cart operations
     */
    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Add item to user's shopping cart
     * 
     * Adds a product to the user's cart or increments quantity if the item
     * already exists. Handles duplicate prevention and quantity management.
     *
     * @param int $user_id ID of the user adding items to cart
     * @param int $product_id ID of the product to add
     * @param int $quantity Number of items to add (default: 1)
     * @return bool True if item added successfully, false otherwise
     */
    public function addToCart($user_id, $product_id, $quantity = 1)
    {
        try {
            // Check if item already exists in user's cart
            $sql_check = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $stmt_check = $this->pdo->prepare($sql_check);
            $stmt_check->execute([':user_id' => $user_id, ':product_id' => $product_id]);
            $existing_item = $stmt_check->fetch();

            if ($existing_item) {
                // Item exists - increment quantity
                $new_quantity = $existing_item['quantity'] + $quantity;
                $sql_update = "UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id";
                $stmt_update = $this->pdo->prepare($sql_update);
                return $stmt_update->execute([':quantity' => $new_quantity, ':cart_id' => $existing_item['cart_id']]);
            } else {
                // New item - insert into cart
                $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
                $stmt_insert = $this->pdo->prepare($sql_insert);
                return $stmt_insert->execute([':user_id' => $user_id, ':product_id' => $product_id, ':quantity' => $quantity]);
            }
        } catch (PDOException $e) {
            // Return false on database error
            return false;
        }
    }

    /**
     * Retrieve all items in user's shopping cart
     * 
     * Fetches complete cart information including product details by joining
     * cart and products tables. Provides all necessary data for cart display.
     *
     * @param int $user_id ID of the user whose cart to retrieve
     * @return array Array of cart items with product information
     */
    public function getCart($user_id)
    {
        try {
            // Join cart with products table to get complete item information
            $sql = "SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.image_url, p.stock, p.category, p.description, p.status
                    FROM cart c
                    JOIN products p ON c.product_id = p.product_id
                    WHERE c.user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Return empty array on error
            return [];
        }
    }

    /**
     * Remove specific product from user's cart
     * 
     * Completely removes a product from the user's shopping cart.
     * Used when user wants to delete an item entirely rather than
     * just reducing quantity.
     *
     * @param int $user_id ID of the user
     * @param int $product_id ID of the product to remove
     * @return bool True if item removed successfully, false otherwise
     */
    public function removeFromCart($user_id, $product_id)
    {
        try {
            $sql = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update quantity of specific cart item
     * 
     * Modifies the quantity of a product in the user's cart.
     * If quantity is set to 0 or negative, the item is removed entirely.
     *
     * @param int $user_id ID of the user
     * @param int $product_id ID of the product to update
     * @param int $quantity New quantity for the product
     * @return bool True if quantity updated successfully, false otherwise
     */
    public function updateQuantity($user_id, $product_id, $quantity)
    {
        try {
            // Remove item if quantity is 0 or negative
            if ($quantity <= 0) {
                return $this->removeFromCart($user_id, $product_id);
            }
            
            // Update quantity for the specified item
            $sql = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':quantity' => $quantity, ':user_id' => $user_id, ':product_id' => $product_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Clear all items from user's cart
     * 
     * Removes all products from the user's shopping cart.
     * Typically used after successful checkout or when user
     * wants to start fresh.
     *
     * @param int $user_id ID of the user whose cart to clear
     * @return bool True if cart cleared successfully, false otherwise
     */
    public function clearCart($user_id)
    {
        try {
            $sql = "DELETE FROM cart WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':user_id' => $user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
