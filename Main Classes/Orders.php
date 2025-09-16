<?php
require_once __DIR__ . '/../DbConnector.php';

class Orders
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function createOrder($user_id, $total_amount, $assignment_id = null, $status = 'pending', $delivery_charge = 0.00, $delivery_address = null)
    {
        try {
            $sql = "INSERT INTO orders (user_id, total_amount, assignment_id, status, delivery_charge, delivery_address) 
                    VALUES (:user_id, :total_amount, :assignment_id, :status, :delivery_charge, :delivery_address)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':total_amount' => $total_amount,
                ':assignment_id' => $assignment_id,
                ':status' => $status,
                ':delivery_charge' => $delivery_charge,
                ':delivery_address' => $delivery_address
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getOrderById($order_id)
    {
        $sql = "SELECT * FROM orders WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    // Fetch all orders for a user
    public function getOrdersByUserId($user_id)
    {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch orders by assignment ID (for technician build requests)
    public function getOrdersByAssignmentId($assignment_id)
    {
        $sql = "SELECT * FROM orders WHERE assignment_id = :assignment_id ORDER BY order_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':assignment_id' => $assignment_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Analytics Methods ---
    public function getTotalRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status IN ('processing','shipped','delivered')";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch();
        return $row['total_revenue'] ?? 0;
    }

    public function getTotalOrders()
    {
        $sql = "SELECT COUNT(*) as total_orders FROM orders WHERE status IN ('processing','shipped','delivered')";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch();
        return $row['total_orders'] ?? 0;
    }

    public function getAverageOrderValue()
    {
        $sql = "SELECT AVG(total_amount) as avg_order_value FROM orders WHERE status IN ('processing','shipped','delivered')";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch();
        return $row['avg_order_value'] ?? 0;
    }

    public function getSalesTrend($period = 'month', $user_id = null)
    {
        // Use payment_date from payment table for sales trend, filtered by user if provided
        $sql = "SELECT DATE_FORMAT(p.payment_date, '%Y-%m') as period, SUM(p.amount) as revenue, COUNT(*) as orders
            FROM payment p
            JOIN orders o ON p.order_id = o.order_id
            WHERE p.payment_status = 'success'
              AND o.status IN ('processing','shipped','delivered')";
        $params = [];
        if ($user_id !== null) {
            $sql .= " AND o.user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        $sql .= " GROUP BY period ORDER BY period DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build a map of period => result
        $trendMap = [];
        // DEBUG: Log raw periods from SQL results
        error_log('getSalesTrend SQL periods: ' . implode(',', array_map(function ($r) {
            return $r['period'];
        }, $results)));
        foreach ($results as $row) {
            $trendMap[$row['period']] = $row;
        }
        // DEBUG: Log trendMap keys
        error_log('getSalesTrend trendMap keys: ' . implode(',', array_keys($trendMap)));

        // Determine the latest period (max of DB or current month)
        $now = new DateTime();
        $currentPeriod = $now->format('Y-m');
        $latestPeriod = $currentPeriod;
        if (!empty($trendMap)) {
            $dbMax = max(array_keys($trendMap));
            if ($dbMax > $currentPeriod) {
                $latestPeriod = $dbMax;
            }
        }
        // Generate 6 months ending at latestPeriod (changed from 5 to 6)
        $months = [];
        $latest = DateTime::createFromFormat('Y-m', $latestPeriod);
        for ($i = 5; $i >= 0; $i--) {  // Changed from 4 to 5
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
        $totalRevenue = array_sum(array_column($rows, 'revenue'));
        foreach ($rows as &$row) {
            $row['percentage'] = $totalRevenue > 0 ? round(($row['revenue'] / $totalRevenue) * 100) : 0;
        }
        return $rows;
    }
}
