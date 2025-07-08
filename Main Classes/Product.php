<?php
require_once __DIR__ . '/../DbConnector.php';

class Product {
    private $pdo;

    public function __construct() {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addProduct($data, $imageFile = null) {
        try {
            $this->pdo->beginTransaction();
            // Handle image upload
            $imageUrl = null;
            if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . '_' . basename($imageFile['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                    $imageUrl = $targetPath;
                }
            }
            // Calculate status based on stock
            $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
            if ($stock === 0) {
                $status = 'Out of Stock';
            } elseif ($stock <= 5) {
                $status = 'Low Stock';
            } else {
                $status = 'In Stock';
            }
            // Insert into products table
            $sql = "INSERT INTO products (name, category, price, image_url, description, manufacturer, stock, status) 
                    VALUES (:name, :category, :price, :image_url, :description, :manufacturer, :stock, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':category' => $data['category'],
                ':price' => $data['price'],
                ':image_url' => $imageUrl,
                ':description' => $data['description'] ?? null,
                ':manufacturer' => $data['manufacturer'],
                ':stock' => $stock,
                ':status' => $status
            ]);
            $productId = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Product added successfully',
                'product_id' => $productId
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error adding product: ' . $e->getMessage()
            ];
        }
    }

    public function updateProduct($productId, $data, $imageFile = null) {
        try {
            $this->pdo->beginTransaction();
            // Handle image upload if new image is provided
            $imageUrl = null;
            if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . '_' . basename($imageFile['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                    $imageUrl = $targetPath;
                }
            }
            // Calculate new status based on stock
            $newStock = isset($data['stock']) ? (int)$data['stock'] : 0;
            if ($newStock === 0) {
                $newStatus = 'Out of Stock';
            } elseif ($newStock <= 5) {
                $newStatus = 'Low Stock';
            } else {
                $newStatus = 'In Stock';
            }
            // Update products table
            $sql = "UPDATE products SET 
                    name = :name, 
                    category = :category, 
                    price = :price, 
                    description = :description, 
                    manufacturer = :manufacturer,
                    stock = :stock,
                    status = :status";
            $params = [
                ':product_id' => $productId,
                ':name' => $data['name'],
                ':category' => $data['category'],
                ':price' => $data['price'],
                ':description' => $data['description'] ?? null,
                ':manufacturer' => $data['manufacturer'],
                ':stock' => $data['stock'] ?? 0,
                ':status' => $newStatus
            ];
            if ($imageUrl) {
                $sql .= ", image_url = :image_url";
                $params[':image_url'] = $imageUrl;
            }
            $sql .= " WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Product updated successfully'
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ];
        }
    }

    public function updateStock($productId, $newStock, $newStatus = null, $lastRestockDate = null) {
        try {
            // Only allow manual override to Discontinued, otherwise auto-calculate
            if ($newStatus === 'Discontinued') {
                $finalStatus = 'Discontinued';
            } else {
                if ($newStock == 0) {
                    $finalStatus = 'Out of Stock';
                } elseif ($newStock <= 5) {
                    $finalStatus = 'Low Stock';
                } else {
                    $finalStatus = 'In Stock';
                }
            }
            $finalDate = $lastRestockDate ? $lastRestockDate : date('Y-m-d H:i:s');
            $sql = "UPDATE products SET stock = :stock, status = :status, last_restock_date = :last_restock_date WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':stock' => $newStock,
                ':status' => $finalStatus,
                ':last_restock_date' => $finalDate,
                ':product_id' => $productId
            ]);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Stock, status, and last restock date updated successfully',
                    'data' => [
                        'product_id' => $productId,
                        'new_stock' => $newStock,
                        'new_status' => $finalStatus,
                        'last_restock_date' => $finalDate
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update stock, status, and last restock date'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating stock, status, and last restock date: ' . $e->getMessage()
            ];
        }
    }

    public function getProductById($productId) {
        $sql = "SELECT * FROM products WHERE product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $product = $stmt->fetch();
        return $product;
    }

    public function getAllProducts() {
        $sql = "SELECT * FROM products ORDER BY product_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProductsByCategory($category) {
        $sql = "SELECT * FROM products WHERE category = :category ORDER BY product_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll();
    }

    public function deleteProduct($productId) {
        try {
            $this->pdo->beginTransaction();
            // Get product details for category and image
            $sql = "SELECT category, image_url FROM products WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
            $product = $stmt->fetch();
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }
            // Delete from specific product table based on category
            $tableMap = [
                'CPU' => 'cpu',
                'CPU Cooler' => 'cpu_cooler',
                'Motherboard' => 'motherboard',
                'Memory' => 'memory',
                'Storage' => 'storage',
                'Video Card' => 'video_card',
                'Power Supply' => 'power_supply',
                'Operating System' => 'operating_system',
                'Monitor' => 'monitor',
                'PC Case' => 'pc_case'
            ];
            $tableName = $tableMap[$product['category']] ?? null;
            if ($tableName) {
                $sql = "DELETE FROM $tableName WHERE product_id = :product_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':product_id' => $productId]);
            }
            // Delete from products table
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
            // Delete image file if it exists
            if ($product['image_url'] && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ];
        }
    }

    // Add more methods as needed (deleteProduct, etc.)
} 