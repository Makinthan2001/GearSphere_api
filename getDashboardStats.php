<?php

/**
 * Dashboard Statistics API Endpoint
 * 
 * This endpoint provides comprehensive dashboard analytics for the GearSphere admin panel.
 * It aggregates data from multiple sources including customer registrations, technician
 * applications, messages, and reviews for real-time administrative monitoring.
 * 
 * @method GET
 * @endpoint /getDashboardStats.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import required classes for dashboard data aggregation
require_once __DIR__ . '/Main Classes/Customer.php';
require_once __DIR__ . '/Main Classes/technician.php';
require_once __DIR__ . '/Main Classes/Message.php';
require_once __DIR__ . '/Main Classes/Review.php';

try {
    // ====================================================================
    // INITIALIZE DATA COLLECTION OBJECTS
    // ====================================================================
    
    $customerObj = new Customer();
    $technicianObj = new technician();
    $messageObj = new Message();
    $reviewObj = new Review();

    // ====================================================================
    // COLLECT DASHBOARD ANALYTICS DATA
    // ====================================================================
    
    // Get latest customer registrations for activity monitoring
    $latestCustomers = $customerObj->getLatestCustomers(5);
    
    // Get latest technician applications for approval queue
    $latestTechnicians = $technicianObj->getLatestTechnicians(5);
    
    // Get total technician count for system metrics
    $technicianCount = $technicianObj->getTechnicianCount();
    
    // Get recent customer messages for support monitoring
    $latestMessages = $messageObj->getLatestMessages(5);
    
    // Get latest product reviews for quality monitoring
    $latestReviews = $reviewObj->getLatestReviews(5);

    // ====================================================================
    // RETURN COMPREHENSIVE DASHBOARD DATA
    // ====================================================================
    
    echo json_encode([
        'latestCustomers' => $latestCustomers,
        'latestTechnicians' => $latestTechnicians,
        'technicianCount' => $technicianCount,
        'latestMessages' => $latestMessages,
        'latestReviews' => $latestReviews
    ]);
} catch (Exception $e) {
    // Handle any errors in data collection
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch dashboard stats', 'details' => $e->getMessage()]);
}
