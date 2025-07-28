<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config_mysqli.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    die("❌ Geen device ID opgegeven.");
}

$device_id = $_GET['id'];
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

// Check of device al bestaat
$stmt = $conn->prepare("SELECT id FROM device_ids WHERE device_id = ?");
$stmt->bind_param("s", $device_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close(); // Alleen sluiten als we verder gaan

    $insert = $conn->prepare("INSERT INTO device_ids (device_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $device_id, $ip_address, $user_agent);

    if ($insert->execute()) {
        echo "✅ Geregistreerd";
    } else {
        echo "❌ Fout bij invoegen: " . $conn->error;
    }
    $insert->close(); // Sluit $insert, niet $stmt
} else {
    echo "ℹ️ Bestaat al";
    $stmt->close(); // Alleen hier sluiten
}

$conn->close();
?>


