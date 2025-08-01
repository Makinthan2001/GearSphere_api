<?php
require_once 'corsConfig.php';
initializeEndpoint();

require_once __DIR__ . '/Main Classes/Customer.php';
require_once __DIR__ . '/Main Classes/technician.php';
require_once __DIR__ . '/Main Classes/Message.php';
require_once __DIR__ . '/Main Classes/Review.php';

try {
    $customerObj = new Customer();
    $technicianObj = new technician();
    $messageObj = new Message();
    $reviewObj = new Review();

    $latestCustomers = $customerObj->getLatestCustomers(5);
    $latestTechnicians = $technicianObj->getLatestTechnicians(5);
    $technicianCount = $technicianObj->getTechnicianCount();
    $latestMessages = $messageObj->getLatestMessages(5);
    $latestReviews = $reviewObj->getLatestReviews(5);

    echo json_encode([
        'latestCustomers' => $latestCustomers,
        'latestTechnicians' => $latestTechnicians,
        'technicianCount' => $technicianCount,
        'latestMessages' => $latestMessages,
        'latestReviews' => $latestReviews
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch dashboard stats', 'details' => $e->getMessage()]);
}
