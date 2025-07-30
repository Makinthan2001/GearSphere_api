<?php
require_once 'corsConfig.php';
initializeEndpoint();

// Include required classes
require_once "Main Classes/Compare_product.php";
require_once "DbConnector.php";

// Get budget and usage from query parameters
$budget = isset($_GET['budget']) ? (float)$_GET['budget'] : 0;
$usage = isset($_GET['usage']) ? strtolower(trim($_GET['usage'])) : '';

if ($budget <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid budget']);
    exit;
}

// Define usage-based weight distribution
$usageWeights = [
    'gaming' => [
        'cpu' => 0.15,
        'gpu' => 0.30,
        'ram' => 0.10,
        'storage' => 0.10,
        'motherboard' => 0.12,
        'psu' => 0.06,
        'case' => 0.05,
        'cooler' => 0.03,
        'os' => 0.04,
        'monitor' => 0.05
    ],
    'workstation' => [
        'cpu' => 0.25,
        'gpu' => 0.20,
        'ram' => 0.12,
        'storage' => 0.12,
        'motherboard' => 0.12,
        'psu' => 0.06,
        'case' => 0.04,
        'cooler' => 0.03,
        'os' => 0.03,
        'monitor' => 0.03
    ],
    'multimedia' => [
        'cpu' => 0.18,
        'gpu' => 0.22,
        'ram' => 0.10,
        'storage' => 0.10,
        'motherboard' => 0.12,
        'psu' => 0.05,
        'case' => 0.05,
        'cooler' => 0.03,
        'os' => 0.05,
        'monitor' => 0.10
    ]
];

// Fallback to gaming if usage type is unknown
$weights = $usageWeights[$usage] ?? $usageWeights['gaming'];

// Connect to database
$db = new DBConnector();
$pdo = $db->connect();

// Create compare instance (future use)
$compare = new Compare_product($pdo);

// Function to get most expensive product under max price
function getBestAffordable($pdo, $table, $maxPrice)
{
    $sql = "
        SELECT p.* FROM products p
        JOIN $table t ON t.product_id = p.product_id
        WHERE p.price <= :maxPrice AND p.status IN ('In Stock', 'Low Stock')
        ORDER BY p.price DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':maxPrice' => $maxPrice]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Table mapping
$tableMap = [
    'cpu' => 'cpu',
    'motherboard' => 'motherboard',
    'ram' => 'memory',
    'storage' => 'storage',
    'gpu' => 'video_card',
    'psu' => 'power_supply',
    'case' => 'pc_case',
    'cooler' => 'cpu_cooler',
    'os' => 'operating_system',
    'monitor' => 'monitor'
];

// Generate build
$build = [];
$debug = [];
foreach ($weights as $part => $weight) {
    $max = $budget * $weight * 1.1; // 10% flexibility
    $table = $tableMap[$part];
    $item = getBestAffordable($pdo, $table, $max);
    if ($item) {
        $build[$part] = $item;
    } else {
        $build[$part] = null;
        $debug[$part] = "No product found in $table under LKR " . number_format($max, 2);
    }
}

// Calculate total
$total = array_sum(array_map(fn($item) => $item['price'] ?? 0, $build));

// Budget label
$budgetLabel = '';
switch (true) {
    case $budget >= 100000 && $budget <= 200000:
        $budgetLabel = 'Entry Level';
        break;
    case $budget > 200000 && $budget <= 300000:
        $budgetLabel = 'Budget';
        break;
    case $budget > 300000 && $budget <= 400000:
        $budgetLabel = 'Mid-Range';
        break;
    case $budget > 400000 && $budget <= 500000:
        $budgetLabel = 'High-End';
        break;
    case $budget > 500000 && $budget <= 750000:
        $budgetLabel = 'Premium';
        break;
    case $budget > 750000:
        $budgetLabel = 'Ultimate';
        break;
    default:
        $budgetLabel = 'Below Minimum';
}

// Final response
$response = [
    'success' => true,
    'build' => $build,
    'total' => $total,
    'label' => $budgetLabel,
    'usage' => ucfirst($usage)
];

if (!empty($debug)) {
    $response['debug'] = $debug;
}

// Output JSON
echo json_encode($response);
