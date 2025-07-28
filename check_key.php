<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config_mysqli.php'; // Zorg dat dit bestand bestaat en correct is

if (!isset($_GET['key']) || !isset($_GET['id'])) {
    http_response_code(400);
    die("❌ Vereiste parameters ontbreken.");
}

$key_value = $_GET['key'];
$device_id = $_GET['id'];

// Check of de key geldig en actief is
$stmt = $conn->prepare("SELECT id, is_premium, is_active, expires_at FROM `keys` WHERE key_value = ?");
$stmt->bind_param("s", $key_value);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $now = date('Y-m-d H:i:s');

    if ($row['is_active'] == 0) {
        echo "INACTIVE";
    } elseif ($row['expires_at'] < $now) {
        echo "VERLOPEN";
    } else {
        // Optioneel: koppel device aan key
        $update = $conn->prepare("UPDATE `keys` SET user_id = ? WHERE id = ?");
        $update->bind_param("si", $device_id, $row['id']);
        $update->execute();
        $update->close();

        echo "VALID";
    }
} else {
    echo "INVALID";
}

$stmt->close();
$conn->close();
?>


