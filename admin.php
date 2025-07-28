<?php
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Databaseverbinding mislukt: " . $conn->connect_error);
}

// Automatisch premium aanmaken via knop in Control Panel
if (isset($_POST['set_premium']) && isset($_POST['device_id'])) {
    $device_id = $_POST['device_id'];
    $generated_key = bin2hex(random_bytes(8)); // 16-karakter key

    $stmt = $conn->prepare("INSERT INTO keys (key_value, user_id, is_premium, is_active, expires_at) VALUES (?, ?, 1, 1, DATE_ADD(NOW(), INTERVAL 30 DAY))");
    $stmt->bind_param("ss", $generated_key, $device_id);

    if ($stmt->execute()) {
        echo "✅ Premium geactiveerd voor apparaat-ID: $device_id<br>Sleutel: <b>$generated_key</b><br><br>";
    } else {
        echo "❌ Fout bij activeren van premium: " . $stmt->error;
    }
    $stmt->close();
}

// Handmatige invoer van key
if (isset($_POST['manual_add'])) {
    $manual_key = $_POST['manual_key'] ?? '';
    $manual_device_id = $_POST['manual_device_id'] ?? '';

    if (!empty($manual_key) && !empty($manual_device_id)) {
        $stmt = $conn->prepare("INSERT INTO keys (key_value, user_id, is_premium, is_active, expires_at) VALUES (?, ?, 1, 1, DATE_ADD(NOW(), INTERVAL 30 DAY))");
        $stmt->bind_param("ss", $manual_key, $manual_device_id);

        if ($stmt->execute()) {
            echo "✅ Handmatig premium key toegevoegd: <b>$manual_key</b> voor apparaat-ID: $manual_device_id<br><br>";
        } else {
            echo "❌ Fout bij invoeren van key: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "❌ Vul zowel een key als device_id in.<br><br>";
    }
}
?>

<!-- Terug link -->
<a href="Control Panel.php">⬅️ Terug naar Control Panel</a>

<!-- Formulier voor handmatige invoer -->
<h3>🛠️ Handmatig Premium Key Toevoegen</h3>
<form method="post" action="">
    <label for="manual_key">Premium Key:</label><br>
    <input type="text" name="manual_key" id="manual_key" required><br><br>

    <label for="manual_device_id">Device ID:</label><br>
    <input type="text" name="manual_device_id" id="manual_device_id" required><br><br>

    <input type="submit" name="manual_add" value="Voeg Handmatig Toe">
</form>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
