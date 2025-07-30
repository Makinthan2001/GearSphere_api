<?php
// Session cleanup script - clears all GearSphere sessions
// DEVELOPMENT ONLY - DO NOT USE IN PRODUCTION
if (
    !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) &&
    !str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost')
) {
    http_response_code(403);
    die('This script is for development only.');
}

require_once 'corsConfig.php';

// Clear session for each possible port
$ports = ['3000', '3001', '3002', '3003', '5173', '5174', '8080', '8081'];

echo "Clearing all GearSphere sessions...\n";

foreach ($ports as $port) {
    $sessionName = "GEARSPHERE_SESSION_" . $port;

    session_name($sessionName);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Destroy this session
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();

    echo "Cleared session for port $port (session name: $sessionName)\n";
}

echo "All sessions cleared successfully!\n";
echo "You can now test with fresh sessions on each port.\n";
