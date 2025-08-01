<?php
require_once __DIR__ . '/../DbConnector.php';

class Cart
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Adds an item to the cart for a specific user.
     * If the item already exists, its quantity is incremented.
     *
     * @param int $user_id
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function addToCart($user_id, $product_id, $quantity = 1)
    {
        try {
            $sql_check = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $stmt_check = $this->pdo->prepare($sql_check);
            $stmt_check->execute([':user_id' => $user_id, ':product_id' => $product_id]);
            $existing_item = $stmt_check->fetch();

            if ($existing_item) {
                $new_quantity = $existing_item['quantity'] + $quantity;
                $sql_update = "UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id";
                $stmt_update = $this->pdo->prepare($sql_update);
                return $stmt_update->execute([':quantity' => $new_quantity, ':cart_id' => $existing_item['cart_id']]);
            } else {
                $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
                $stmt_insert = $this->pdo->prepare($sql_insert);
                return $stmt_insert->execute([':user_id' => $user_id, ':product_id' => $product_id, ':quantity' => $quantity]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retrieves all cart items for a given user, joining with the products table.
     *
     * @param int $user_id
     * @return array
     */
    public function getCart($user_id)
    {
        try {
            $sql = "SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.image_url, p.stock, p.category, p.description, p.status
                    FROM cart c
                    JOIN products p ON c.product_id = p.product_id
                    WHERE c.user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Removes a specific product from a user's cart.
     *
     * @param int $user_id
     * @param int $product_id
     * @return bool
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
     * Updates the quantity of a specific product in a user's cart.
     *
     * @param int $user_id
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity($user_id, $product_id, $quantity)
    {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($user_id, $product_id);
            }
            $sql = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':quantity' => $quantity, ':user_id' => $user_id, ':product_id' => $product_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Clears all items from a user's cart.
     *
     * @param int $user_id
     * @return bool
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
