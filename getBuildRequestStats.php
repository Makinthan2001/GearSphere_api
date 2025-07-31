<?php
require_once 'corsConfig.php';
initializeEndpoint();

header('Content-Type: application/json');
require_once __DIR__ . '/DbConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$technician_id = isset($_GET['technician_id']) ? intval($_GET['technician_id']) : null;
if (!$technician_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid technician_id']);
    exit;
}

try {
    $db = new DBConnector();
    $pdo = $db->connect();
    
    // Get total build requests count
    $totalQuery = "SELECT COUNT(*) as total FROM technician_assignments WHERE technician_id = :technician_id";
    $stmt = $pdo->prepare($totalQuery);
    $stmt->execute([':technician_id' => $technician_id]);
    $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRequests = $totalResult['total'];
    
    // Get pending requests count
    $pendingQuery = "SELECT COUNT(*) as pending FROM technician_assignments WHERE technician_id = :technician_id AND status = 'pending'";
    $stmt = $pdo->prepare($pendingQuery);
    $stmt->execute([':technician_id' => $technician_id]);
    $pendingResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $pendingRequests = $pendingResult['pending'];
    
    // Get accepted requests count
    $acceptedQuery = "SELECT COUNT(*) as accepted FROM technician_assignments WHERE technician_id = :technician_id AND status = 'accepted'";
    $stmt = $pdo->prepare($acceptedQuery);
    $stmt->execute([':technician_id' => $technician_id]);
    $acceptedResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $acceptedRequests = $acceptedResult['accepted'];
    
    // Get last 3 pending requests with customer details
    $pendingDetailsQuery = "SELECT 
        ta.assignment_id,
        ta.customer_id,
        ta.assigned_at,
        ta.instructions,
        u.name as customer_name,
        u.email as customer_email,
        u.contact_number as customer_phone,
        u.profile_image as customer_profile_image
        FROM technician_assignments ta
        JOIN users u ON ta.customer_id = u.user_id
        WHERE ta.technician_id = :technician_id AND ta.status = 'pending'
        ORDER BY ta.assigned_at DESC
        LIMIT 3";
    $stmt = $pdo->prepare($pendingDetailsQuery);
    $stmt->execute([':technician_id' => $technician_id]);
    $pendingDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_requests' => (int)$totalRequests,
            'pending_requests' => (int)$pendingRequests,
            'accepted_requests' => (int)$acceptedRequests
        ],
        'recent_pending' => $pendingDetails
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}