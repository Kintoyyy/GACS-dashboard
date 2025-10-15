<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
requireLogin();

$conn = getDBConnection();

// Get MikroTik credentials
$result = $conn->query("SELECT * FROM mikrotik_credentials WHERE is_connected = 1 ORDER BY id DESC LIMIT 1");
$credentials = $result->fetch_assoc();

if (!$credentials) {
    jsonResponse(['success' => false, 'message' => 'MikroTik not connected', 'netwatch' => []]);
}

use App\MikroTikAPI;

try {
    $mikrotik = new MikroTikAPI(
        $credentials['host'],
        $credentials['username'],
        $credentials['password'],
        $credentials['port']
    );

    $connected = $mikrotik->connect();

    if (!$connected) {
        jsonResponse(['success' => false, 'message' => 'Failed to connect to MikroTik', 'netwatch' => []]);
    }

    // Get netwatch entries
    $netwatch = $mikrotik->getNetwatch();

    jsonResponse([
        'success' => true,
        'netwatch' => $netwatch,
        'count' => count($netwatch)
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage(), 'netwatch' => []]);
}
