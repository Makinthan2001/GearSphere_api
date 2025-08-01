<?php

/**
 * Messages Retrieval API Endpoint
 * 
 * This endpoint retrieves all customer messages from the GearSphere system.
 * It fetches contact form submissions and customer inquiries for administrative
 * review and response management.
 * 
 * @method GET/POST
 * @endpoint /getMessages.php
 * @returns JSON array of all customer messages with details
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Set JSON response header
header('Content-Type: application/json');

// Import Message class for message operations
require_once __DIR__ . '/Main Classes/Message.php';

// ====================================================================
// RETRIEVE AND RETURN ALL MESSAGES
// ====================================================================

// Create message instance and fetch all customer messages
$msgObj = new Message();
$messages = $msgObj->getAllMessages();

// Return messages as JSON response
echo json_encode($messages);
