<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'DbConnector.php';

class StockManager {
    private $db;
    
    public function __construct() {
        $connector = new DBConnector();
        $this->db = $connector->connect();
    }
    
    public function updateStock($productId, $newStock, $newStatus = null, $lastRestockDate = null) {
        try {
            // Validate input
            if (!$productId || !is_numeric($productId)) {
                return [
                    'success' => false,
                    'message' => 'Invalid product ID'
                ];
            }
            
            if (!is_numeric($newStock) || $newStock < 0) {
                return [
                    'success' => false,
                    'message' => 'Stock must be a non-negative number'
                ];
            }
            
            // Validate status if provided
            $validStatuses = ['In Stock', 'Low Stock', 'Out of Stock', 'Discontinued'];
            if ($newStatus && !in_array($newStatus, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)
                ];
            }
            
            // Validate date if provided
            if ($lastRestockDate && !strtotime($lastRestockDate)) {
                return [
                    'success' => false,
                    'message' => 'Invalid date format for last restock date'
                ];
            }
            
            // Check if product exists
            $checkSql = "SELECT product_id, name, stock, status FROM products WHERE product_id = :product_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':product_id' => $productId]);
            $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }
            
            $oldStock = $product['stock'];
            $oldStatus = $product['status'];
            
            // Use provided status or calculate based on stock
            // Only allow manual override to "Discontinued", otherwise auto-calculate
            if ($newStatus === 'Discontinued') {
                $finalStatus = 'Discontinued';
            } else {
                $finalStatus = $this->determineStatus($newStock);
            }
            
            // Use provided date or current timestamp
            $finalDate = $lastRestockDate ? $lastRestockDate : date('Y-m-d H:i:s');
            
            // Update stock, status, and last_restock_date
            $sql = "UPDATE products SET stock = :stock, status = :status, last_restock_date = :last_restock_date WHERE product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':stock' => $newStock,
                ':status' => $finalStatus,
                ':last_restock_date' => $finalDate,
                ':product_id' => $productId
            ]);
            
            if ($result) {
                // Log stock change
                $this->logStockChange($productId, $oldStock, $newStock, $oldStatus, $finalStatus);
                
                // One-time forced fix: set all products with stock = 0 to Out of Stock
                $fixSql = "UPDATE products SET status = 'Out of Stock' WHERE stock = 0";
                $fixResult = $this->db->prepare($fixSql)->execute();
                error_log('Forced status fix for stock=0, result: ' . var_export($fixResult, true));
                
                return [
                    'success' => true,
                    'message' => 'Stock, status, and last restock date updated successfully',
                    'data' => [
                        'product_id' => $productId,
                        'product_name' => $product['name'],
                        'old_stock' => $oldStock,
                        'new_stock' => $newStock,
                        'old_status' => $oldStatus,
                        'new_status' => $finalStatus,
                        'stock_change' => $newStock - $oldStock,
                        'status_manually_set' => $newStatus !== null,
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
    
    private function determineStatus($stock) {
        if ($stock === 0) {
            return 'Out of Stock';
        } elseif ($stock <= 5) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }
    
    private function logStockChange($productId, $oldStock, $newStock, $oldStatus, $newStatus) {
        try {
            // You can create a stock_history table to track all stock changes
            // For now, we'll just log to console/error log
            $stockChange = $newStock - $oldStock;
            $stockChangeType = $stockChange > 0 ? 'INCREASE' : ($stockChange < 0 ? 'DECREASE' : 'NO_CHANGE');
            $statusChange = $oldStatus !== $newStatus ? "Status: {$oldStatus} → {$newStatus}" : "Status unchanged: {$newStatus}";
            
            error_log("Stock/Status change for product ID {$productId}: Stock {$oldStock} → {$newStock} ({$stockChangeType}), {$statusChange}");
            
            // Example SQL for stock_history table (uncomment if you create the table):
            /*
            $sql = "INSERT INTO stock_history (product_id, old_stock, new_stock, change_amount, change_type, old_status, new_status, timestamp) 
                    VALUES (:product_id, :old_stock, :new_stock, :change_amount, :change_type, :old_status, :new_status, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':product_id' => $productId,
                ':old_stock' => $oldStock,
                ':new_stock' => $newStock,
                ':change_amount' => $stockChange,
                ':change_type' => $stockChangeType,
                ':old_status' => $oldStatus,
                ':new_status' => $newStatus
            ]);
            */
            
        } catch (Exception $e) {
            // Don't fail the main operation if logging fails
            error_log("Failed to log stock/status change: " . $e->getMessage());
        }
    }
    
    public function getStockHistory($productId) {
        try {
            // This would query the stock_history table
            // For now, return mock data
            return [
                'success' => true,
                'history' => [
                    [
                        'date' => date('Y-m-d H:i:s'),
                        'type' => 'MANUAL_UPDATE',
                        'quantity' => 0,
                        'previous_stock' => 0,
                        'new_stock' => 0,
                        'notes' => 'Stock history feature coming soon'
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching stock history: ' . $e->getMessage()
            ];
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockManager = new StockManager();
    
    // Debug: Log all POST data
    error_log("POST data received: " . print_r($_POST, true));
    error_log("FILES data received: " . print_r($_FILES, true));
    
    // Get form data
    $productId = $_POST['product_id'] ?? null;
    $newStock = $_POST['stock'] ?? null;
    $newStatus = (isset($_POST['status']) && $_POST['status'] === 'Discontinued') ? 'Discontinued' : null;
    // Force status recalculation except for Discontinued
    if ($newStatus === null) {
        $newStatus = '';
    }
    $lastRestockDate = $_POST['last_restock_date'] ?? null;
    
    // Debug: Log the extracted values
    error_log("Extracted product_id: " . var_export($productId, true));
    error_log("Extracted stock: " . var_export($newStock, true));
    error_log("Extracted status: " . var_export($newStatus, true));
    error_log("Extracted last_restock_date: " . var_export($lastRestockDate, true));
    
    if ($productId === null || $productId === '' || $newStock === null || $newStock === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID and stock are required',
            'debug' => [
                'received_post' => $_POST,
                'product_id' => $productId,
                'stock' => $newStock,
                'status' => $newStatus,
                'last_restock_date' => $lastRestockDate
            ]
        ]);
        exit;
    }
    
    $result = $stockManager->updateStock($productId, $newStock, $newStatus, $lastRestockDate);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 