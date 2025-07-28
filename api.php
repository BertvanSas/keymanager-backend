<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Juiste config gebruiken
require_once 'config_mysqli.php';

header('Content-Type: application/json');

$sql = "SELECT device_id, ip_address, user_agent, created_at, last_seen FROM device_ids ORDER BY created_at DESC";
$result = $conn->query($sql);

$devices = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $devices[] = $row;
    }
}

echo json_encode([
    "count" => count($devices),
    "devices" => $devices
]);

$conn->close();
?>




