<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'DbConnector.php';

class ProductManager {
    private $db;
    
    public function __construct() {
        $connector = new DBConnector();
        $this->db = $connector->connect();
    }
    
    public function getAllProducts() {
        try {
            $sql = "SELECT * FROM products ORDER BY product_id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            // Get specific details for each product
            foreach ($products as &$product) {
                $product['specific_details'] = $this->getProductSpecificDetails($product['product_id'], $product['category']);
            }
            
            return [
                'success' => true,
                'products' => $products
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching products: ' . $e->getMessage()
            ];
        }
    }
    
    public function getProductById($productId) {
        try {
            $sql = "SELECT * FROM products WHERE product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
            $product = $stmt->fetch();
            
            if ($product) {
                $product['specific_details'] = $this->getProductSpecificDetails($product['product_id'], $product['category']);
                return [
                    'success' => true,
                    'product' => $product
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching product: ' . $e->getMessage()
            ];
        }
    }
    
    public function getProductsByCategory($category) {
        try {
            $sql = "SELECT * FROM products WHERE category = :category ORDER BY product_id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':category' => $category]);
            $products = $stmt->fetchAll();
            
            // Get specific details for each product
            foreach ($products as &$product) {
                $product['specific_details'] = $this->getProductSpecificDetails($product['product_id'], $product['category']);
            }
            
            return [
                'success' => true,
                'products' => $products
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching products: ' . $e->getMessage()
            ];
        }
    }
    
    private function getProductSpecificDetails($productId, $category) {
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
        
        $tableName = $tableMap[$category] ?? null;
        
        if (!$tableName) {
            return null; // For general products
        }
        
        $sql = "SELECT * FROM $tableName WHERE product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        
        return $stmt->fetch();
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $productManager = new ProductManager();
    
    $productId = $_GET['id'] ?? null;
    $category = $_GET['category'] ?? null;
    
    if ($productId) {
        $result = $productManager->getProductById($productId);
    } elseif ($category) {
        $result = $productManager->getProductsByCategory($category);
    } else {
        $result = $productManager->getAllProducts();
    }
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 