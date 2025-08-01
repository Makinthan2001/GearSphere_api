<?php

/**
 * Message Deletion API Endpoint
 * 
 * This endpoint handles deleting customer messages from the GearSphere system.
 * It supports multiple input formats, validates message IDs, and processes
 * message deletions with proper error handling and response formatting.
 * 
 * @method POST
 * @endpoint /deleteMessage.php
 */

// Initialize CORS configuration for cross-origin requests
require_once 'corsConfig.php';
initializeEndpoint();

// Import Message class for message operations
require_once __DIR__ . '/Main Classes/Message.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ====================================================================
    // EXTRACT MESSAGE ID FROM MULTIPLE INPUT FORMATS
    // ====================================================================
    
    // Support JSON input for API flexibility
    if (strpos($_SERVER["CONTENT_TYPE"] ?? '', "application/json") === 0) {
        $input = json_decode(file_get_contents("php://input"), true);
        $message_id = $input['message_id'] ?? null;
    } else {
        // Support form-encoded data for traditional forms
        $message_id = $_POST['message_id'] ?? null;
    }
    
    // ====================================================================
    // VALIDATE AND PROCESS MESSAGE DELETION
    // ====================================================================
    
    if ($message_id) {
        // Attempt to delete message
        $msgObj = new Message();
        $result = $msgObj->deleteMessage($message_id);
        
        // Return deletion result
        echo json_encode($result);
    } else {
        // Missing required message ID
        echo json_encode([
            'success' => false,
            'message' => 'message_id is required.'
        ]);
    }
} else {
    // ====================================================================
    // INVALID REQUEST METHOD - Return error
    // ====================================================================
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
