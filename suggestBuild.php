<?php

/**
 * PC Build Suggestion API Endpoint
 * 
 * This endpoint generates personalized PC build recommendations for the GearSphere system.
 * It analyzes budget constraints and usage patterns to suggest optimal component
 * combinations with intelligent weight distribution and compatibility considerations.
 * 
 * @method GET
 * @endpoint /suggestBuild.php
 * @param float $budget Total budget for the PC build
 * @param string $usage Usage type: 'gaming', 'workstation', or 'multimedia'
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import required classes for product comparison and database operations
require_once "Main Classes/Compare_product.php";
require_once "DbConnector.php";

// ====================================================================
// EXTRACT AND VALIDATE REQUEST PARAMETERS
// ====================================================================

// Get budget and usage parameters from query string
$budget = isset($_GET['budget']) ? (float)$_GET['budget'] : 0;
$usage = isset($_GET['usage']) ? strtolower(trim($_GET['usage'])) : '';

// Validate budget parameter
if ($budget <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid budget']);
    exit;
}

// ====================================================================
// USAGE-BASED WEIGHT DISTRIBUTION SYSTEM
// ====================================================================

// Define intelligent budget allocation weights for different usage scenarios
$usageWeights = [
    'gaming' => [
        'cpu' => 0.15,          // Moderate CPU for gaming
        'gpu' => 0.30,          // High GPU allocation for graphics performance
        'ram' => 0.10,          // Standard RAM allocation
        'storage' => 0.10,      // Storage for game libraries
        'motherboard' => 0.12,  // Quality motherboard for stability
        'psu' => 0.06,          // Adequate power supply
        'case' => 0.05,         // Basic case requirements
        'cooler' => 0.03,       // Cooling for gaming sessions
        'os' => 0.04,           // Operating system
        'monitor' => 0.05       // Display for gaming experience
    ],
    'workstation' => [
        'cpu' => 0.25,          // High CPU allocation for computational tasks
        'gpu' => 0.20,          // Professional GPU for rendering/CAD
        'ram' => 0.12,          // Increased RAM for multitasking
        'storage' => 0.12,      // More storage for work files
        'motherboard' => 0.12,  // Reliable motherboard for stability
        'psu' => 0.06,          // Adequate power for workstation components
        'case' => 0.04,         // Professional case
        'cooler' => 0.03,       // Cooling for sustained workloads
        'os' => 0.03,           // Operating system
        'monitor' => 0.03       // Basic monitor allocation
    ],
    'multimedia' => [
        'cpu' => 0.18,          // Balanced CPU for media processing
        'gpu' => 0.22,          // GPU for video editing and streaming
        'ram' => 0.10,          // RAM for media applications
        'storage' => 0.10,      // Storage for media files
        'motherboard' => 0.12,  // Quality motherboard
        'psu' => 0.05,          // Power supply
        'case' => 0.05,         // Case for multimedia setup
        'cooler' => 0.03,       // Cooling system
        'os' => 0.05,           // Operating system
        'monitor' => 0.10       // Higher monitor allocation for content creation
    ]
];

// Apply weight distribution based on usage type (fallback to gaming)
$weights = $usageWeights[$usage] ?? $usageWeights['gaming'];

// ====================================================================
// DATABASE CONNECTION AND HELPER FUNCTIONS
// ====================================================================

// Connect to database for product queries
$db = new DBConnector();
$pdo = $db->connect();

// Create comparison instance for future feature enhancements
$compare = new Compare_product($pdo);

/**
 * Get the best affordable product within price range
 * 
 * This function finds the most expensive (highest quality) product
 * that fits within the allocated budget for each component category.
 * 
 * @param PDO $pdo Database connection
 * @param string $table Product category table name
 * @param float $maxPrice Maximum price for the component
 * @return array|false Product data or false if none found
 */
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

// ====================================================================
// COMPONENT CATEGORY MAPPING
// ====================================================================

// Map component types to their corresponding database tables
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

// ====================================================================
// GENERATE PERSONALIZED PC BUILD
// ====================================================================

$build = [];
$debug = [];

// Process each component category with allocated budget
foreach ($weights as $part => $weight) {
    // Calculate maximum price for this component (with 10% flexibility)
    $max = $budget * $weight * 1.1;
    $table = $tableMap[$part];
    
    // Find best product within budget
    $item = getBestAffordable($pdo, $table, $max);
    
    if ($item) {
        $build[$part] = $item;
    } else {
        $build[$part] = null;
        $debug[$part] = "No product found in $table under LKR " . number_format($max, 2);
    }
}

// ====================================================================
// CALCULATE BUILD TOTAL AND CATEGORIZATION
// ====================================================================

// Calculate total cost of selected components
$total = array_sum(array_map(fn($item) => $item['price'] ?? 0, $build));

// Categorize build based on budget range
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

// ====================================================================
// RETURN BUILD RECOMMENDATION RESPONSE
// ====================================================================

// Prepare comprehensive response with build details
$response = [
    'success' => true,
    'build' => $build,
    'total' => $total,
    'label' => $budgetLabel,
    'usage' => ucfirst($usage)
];

// Include debug information if components couldn't be found
if (!empty($debug)) {
    $response['debug'] = $debug;
}

// Return JSON response with build recommendation
echo json_encode($response);
