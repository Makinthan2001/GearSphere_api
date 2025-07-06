<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
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
    
    public function deleteProduct($productId) {
        try {
            $this->db->beginTransaction();
            
            // First, get the product details to know the category and image
            $sql = "SELECT category, image_url FROM products WHERE product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }
            
            // Delete from specific product table based on category
            $this->deleteProductSpecific($productId, $product['category']);
            
            // Delete from products table
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
            
            // Delete image file if it exists
            if ($product['image_url'] && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ];
        }
    }
    
    private function deleteProductSpecific($productId, $category) {
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
        
        if ($tableName) {
            $sql = "DELETE FROM $tableName WHERE product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
        }
    }
    
    public function deleteMultipleProducts($productIds) {
        try {
            $this->db->beginTransaction();
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($productIds as $productId) {
                $result = $this->deleteProduct($productId);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Deleted $successCount products successfully" . ($errorCount > 0 ? ", $errorCount failed" : ""),
                'deleted_count' => $successCount,
                'error_count' => $errorCount
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error deleting products: ' . $e->getMessage()
            ];
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productManager = new ProductManager();
    
    // Get product ID from different sources
    $productId = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // For DELETE requests, get ID from URL parameters
        $productId = $_GET['id'] ?? null;
    } else {
        // For POST requests, get ID from POST data or JSON body
        $productId = $_POST['product_id'] ?? null;
        
        // If not in POST, try to get from JSON body
        if (!$productId) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $productId = $data['product_id'] ?? null;
        }
    }
    
    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    
    $result = $productManager->deleteProduct($productId);
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 