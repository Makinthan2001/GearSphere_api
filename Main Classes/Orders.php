<?php

/**
 * Order Management Class
 * 
 * This class handles all order-related operations for the GearSphere system.
 * It manages order creation, retrieval, updates, and provides comprehensive
 * analytics including sales trends, revenue tracking, and product performance.
 * @package GearSphere-BackEnd
 */

require_once __DIR__ . '/../DbConnector.php';

class Orders
{
    private $pdo;  // Database connection object

    /**
     * Constructor - Initialize database connection
     * 
     * Establishes connection to the database for all order operations
     */
    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Create a new order in the system
     * 
     * Creates a new order record with user information, total amount,
     * and optional assignment for custom PC builds.
     * 
     * @param int $user_id ID of the customer placing the order
     * @param float $total_amount Total monetary value of the order
     * @param int|null $assignment_id Optional assignment ID for custom builds
     * @param string $status Initial order status (default: 'pending')
     * @return int|false Order ID if successful, false on failure
     */
    public function createOrder($user_id, $total_amount, $assignment_id = null, $status = 'pending')
    {
        try {
            $sql = "INSERT INTO orders (user_id, total_amount, assignment_id, status) VALUES (:user_id, :total_amount, :assignment_id, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':total_amount' => $total_amount,
                ':assignment_id' => $assignment_id,
                ':status' => $status
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retrieve order information by ID
     * 
     * Fetches complete order details for a specific order ID.
     * Used for order tracking and administrative purposes.
     * 
     * @param int $order_id ID of the order to retrieve
     * @return array|false Order data array or false if not found
     */
    public function getOrderById($order_id)
    {
        $sql = "SELECT * FROM orders WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update order assignment for custom builds
     * 
     * Links an order to a specific technician assignment for
     * custom PC build services.
     * 
     * @param int $order_id ID of the order to update
     * @param int $assignment_id ID of the technician assignment
     * @return bool True if updated successfully, false otherwise
     */
    public function updateAssignment($order_id, $assignment_id)
    {
        try {
            $sql = "UPDATE orders SET assignment_id = :assignment_id WHERE order_id = :order_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':assignment_id' => $assignment_id,
                ':order_id' => $order_id
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retrieve all orders for a specific user
     * 
     * Fetches order history for a customer, ordered by most recent first.
     * Used for customer order tracking and history display.
     * 
     * @param int $user_id ID of the user whose orders to retrieve
     * @return array Array of order data sorted by date (newest first)
     */
    public function getOrdersByUserId($user_id)
    {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =================================================================
    // ANALYTICS AND REPORTING METHODS
    // =================================================================

    /**
     * Calculate total revenue from completed orders
     * 
     * Sums up revenue from all orders that have reached completion
     * (processing, shipped, or delivered status). Used for financial
     * reporting and dashboard analytics.
     * 
     * @return float Total revenue amount
     */
    public function getTotalRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status IN ('processing','shipped','delivered')";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch();
        return $row['total_revenue'] ?? 0;
    }

    /**
     * Count total number of completed orders
     * 
     * Returns the count of all successfully processed orders.
     * Excludes pending, cancelled, or failed orders.
     * 
     * @return int Total number of completed orders
     */
    public function getTotalOrders()
    {
        $sql = "SELECT COUNT(*) as total_orders FROM orders WHERE status IN ('processing','shipped','delivered')";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch();
        return $row['total_orders'] ?? 0;
    }

    /**
     * Calculate average order value
     * 
     * Computes the mean value of all completed orders.
     * Useful for understanding customer spending patterns.
     * 
     * @return float Average order value
     */
    public function getAverageOrderValue()
    {
        $sql = "SELECT AVG(total_amount) as avg_order_value FROM orders WHERE status IN ('processing','shipped','delivered')";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch();
        return $row['avg_order_value'] ?? 0;
    }

    /**
     * Generate sales trend data over time
     * 
     * Provides monthly sales data for the last 6 months including
     * revenue and order count. Can be filtered by specific user.
     * Uses payment dates for accurate financial reporting.
     * 
     * @param string $period Time period for grouping (default: 'month')
     * @param int|null $user_id Optional user ID to filter results
     * @return array Array of monthly sales data with revenue and order counts
     */
    public function getSalesTrend($period = 'month', $user_id = null)
    {
        // Use payment_date from payment table for accurate sales trend analysis
        $sql = "SELECT DATE_FORMAT(p.payment_date, '%Y-%m') as period, SUM(p.amount) as revenue, COUNT(*) as orders
            FROM payment p
            JOIN orders o ON p.order_id = o.order_id
            WHERE p.payment_status = 'success'
              AND o.status IN ('processing','shipped','delivered')";
        $params = [];
        
        // Add user filter if specified
        if ($user_id !== null) {
            $sql .= " AND o.user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        $sql .= " GROUP BY period ORDER BY period DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build a map of period => result for efficient lookup
        $trendMap = [];
        // Debug logging for trend analysis
        error_log('getSalesTrend SQL periods: ' . implode(',', array_map(function ($r) {
            return $r['period'];
        }, $results)));
        
        foreach ($results as $row) {
            $trendMap[$row['period']] = $row;
        }
        error_log('getSalesTrend trendMap keys: ' . implode(',', array_keys($trendMap)));

        // Determine the latest period (max of DB data or current month)
        $now = new DateTime();
        $currentPeriod = $now->format('Y-m');
        $latestPeriod = $currentPeriod;
        if (!empty($trendMap)) {
            $dbMax = max(array_keys($trendMap));
            if ($dbMax > $currentPeriod) {
                $latestPeriod = $dbMax;
            }
        }
        
        // Generate 6 months of data ending at latestPeriod
        $months = [];
        $latest = DateTime::createFromFormat('Y-m', $latestPeriod);
        for ($i = 5; $i >= 0; $i--) {  // 6 months total (0-5)
            $m = clone $latest;
            $m->modify("-{$i} months");
            $period = $m->format('Y-m');
            $months[] = [
                'period' => $period,
                'revenue' => isset($trendMap[$period]) ? (float)$trendMap[$period]['revenue'] : 0,
                'orders' => isset($trendMap[$period]) ? (int)$trendMap[$period]['orders'] : 0
            ];
        }
        return $months;
    }

    /**
     * Get top-selling products by revenue
     * 
     * Identifies the best-performing products based on total revenue
     * generated. Includes sales quantity and revenue data.
     * 
     * @param int $limit Number of top products to return (default: 3)
     * @return array Array of top products with sales and revenue data
     */
    public function getTopProducts($limit = 3)
    {
        $sql = "SELECT p.product_id, p.name, SUM(oi.quantity) as sales, SUM(oi.price * oi.quantity) as revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.status IN ('processing','shipped','delivered')
                GROUP BY p.product_id, p.name
                ORDER BY revenue DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Analyze sales performance by product category
     * 
     * Provides comprehensive category-wise sales analysis including
     * total sales, revenue, and percentage contribution to overall sales.
     * Useful for inventory planning and marketing strategy.
     * 
     * @return array Array of category performance data with percentages
     */
    public function getCategoryPerformance()
    {
        $sql = "SELECT p.category, SUM(oi.quantity) as sales, SUM(oi.price * oi.quantity) as revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.status IN ('processing','shipped','delivered')
                GROUP BY p.category
                ORDER BY revenue DESC";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();
        
        // Calculate percentage contribution for each category
        $totalRevenue = array_sum(array_column($rows, 'revenue'));
        foreach ($rows as &$row) {
            $row['percentage'] = $totalRevenue > 0 ? round(($row['revenue'] / $totalRevenue) * 100) : 0;
        }
        return $rows;
    }
}
