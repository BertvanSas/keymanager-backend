<?php
session_start(); // Start de sessie bovenaan
require 'config.php'; // Zorg dat de databaseconnectie beschikbaar is

// Wachtwoord definitie
$correct_password = "Appelboom123";
$login_error = "";

// Verwerk login verzoek
if (isset($_POST['login'])) {
    $entered_password = $_POST['password'] ?? '';
    if ($entered_password === $correct_password) {
        $_SESSION['loggedin'] = true;
    } else {
        $login_error = "Ongeldig wachtwoord.";
    }
}

// Loguit functionaliteit
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: Control Panel.php");
    exit;
}

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Toon login formulier als niet ingelogd
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>🔐 Key Controle Paneel - Login</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #e0f2f7; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
            .login-container { background: #ffffff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; width: 350px; }
            .login-container h1 { color: #2c3e50; margin-bottom: 30px; }
            .login-container label { display: block; text-align: left; margin-bottom: 8px; font-weight: bold; color: #555; }
            .login-container input[type="password"] { width: calc(100% - 22px); padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
            .login-container button { background-color: #3498db; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 18px; width: 100%; transition: background-color 0.3s ease; }
            .login-container button:hover { background-color: #2980b9; }
            .error-message { color: #dc3545; margin-top: 15px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>Login bij Controle Paneel</h1>
            <form method="POST">
                <label for="password">Wachtwoord:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" name="login">Login</button>
                <?php if ($login_error): ?>
                    <p class="error-message"><?= $login_error ?></p>
                <?php endif; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit; // Stop de uitvoering als niet ingelogd
}

// De rest van de code blijft hetzelfde als in je oorspronkelijke Control Panel.php
// Verwerk POST verzoeken (alleen als ingelogd)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Actie voor het aanmaken van een nieuwe key
    if (isset($_POST['create_key'])) {
        $key = bin2hex(random_bytes(16)); // Genereer een langere, uniekere sleutel
        $premium = isset($_POST['is_premium']) ? 1 : 0;
        $active = 1; // Nieuwe sleutels zijn standaard actief
        $expires = $_POST['expires_at'];
        $user_id = $_POST['user_id_assign'] ?? null; // Nieuw: optioneel user_id toewijzen bij aanmaak

        $stmt = $pdo->prepare("INSERT INTO `keys` (key_value, user_id, is_premium, is_active, expires_at) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$key, $user_id, $premium, $active, $expires])) {
            // Optioneel: succesbericht tonen
        } else {
            // Optioneel: foutbericht tonen
        }
    }

    // Acties voor het beheren van bestaande keys
    if (isset($_POST['action'])) {
        $id = $_POST['id'];
        $action = $_POST['action'];

        switch ($action) {
            case 'toggle_premium':
                $pdo->prepare("UPDATE `keys` SET is_premium = NOT is_premium WHERE id = ?")->execute([$id]);
                break;
            case 'toggle_active':
                $pdo->prepare("UPDATE `keys` SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
                break;
            case 'delete':
                $pdo->prepare("DELETE FROM `keys` WHERE id = ?")->execute([$id]);
                break;
            case 'extend_30_days': // Nieuwe actie: verleng met 30 dagen
                $pdo->prepare("UPDATE `keys` SET expires_at = DATE_ADD(expires_at, INTERVAL 30 DAY) WHERE id = ?")->execute([$id]);
                break;
            case 'assign_user_id_web': // Nieuwe actie voor toewijzing user_id vanuit webpaneel
                $new_user_id = $_POST['new_user_id'] ?? null;
                if ($new_user_id) {
                    $pdo->prepare("UPDATE `keys` SET user_id = ? WHERE id = ?")->execute([$new_user_id, $id]);
                }
                break;
        }
    }
    // Herlaad de pagina om de wijzigingen te zien en POST-data te verwijderen
    header("Location: Control Panel.php");
    exit;
}

// Haal alle keys op voor weergave - DEZE HOORT BOVEN DE HTML
$stmt = $pdo->query("SELECT * FROM `keys` ORDER BY created_at DESC"); // Gebruik backticks voor 'keys'
$keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>🔐 Key Controle Paneel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #e0f2f7; padding: 20px; color: #333; }
        h1, h2, h3 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .create-form { background: #ffffff; padding: 25px; border-radius: 10px; width: 450px; margin: 20px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #ddd; }
        .create-form label { display: block; margin-bottom: 8px; font-weight: bold; }
        .create-form input[type="datetime-local"], .create-form input[type="text"] { width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .create-form button { background-color: #4CAF50; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%; }
        .create-form button:hover { background-color: #45a049; }

        table { width: 100%; border-collapse: collapse; margin-top: 30px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px 15px; border: 1px solid #e0e0e0; text-align: left; }
        th { background-color: #3498db; color: white; font-weight: bold; text-transform: uppercase; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        form { display: inline-block; margin: 0; } /* Zorg dat formulieren op één lijn blijven */
        button { padding: 8px 12px; margin: 3px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background-color 0.3s ease; }
        .premium { color: #28a745; font-weight: bold; }
        .not-premium { color: #dc3545; font-weight: bold; }
        .inactive { color: #6c757d; font-weight: bold; }
        .active { color: #28a745; font-weight: bold; }

        button[name="action"][value="toggle_premium"] { background-color: #ffc107; color: #333; } /* Geel voor Premium Toggle */
        button[name="action"][value="toggle_premium"]:hover { background-color: #e0a800; }
        button[name="action"][value="toggle_active"] { background-color: #17a2b8; color: white; } /* Cyan voor Active Toggle (Pauzeren) */
        button[name="action"][value="toggle_active"]:hover { background-color: #138496; }
        button[name="action"][value="delete"] { background-color: #dc3545; color: white; } /* Rood voor Verwijderen */
        button[name="action"][value="delete"]:hover { background-color: #c82333; }
        button[name="action"][value="extend_30_days"] { background-color: #6f42c1; color: white; } /* Paars voor Verlengen */
        button[name="action"][value="extend_30_days"]:hover { background-color: #563d7c; }
        button[name="action"][value="assign_user_id_web"] { background-color: #20c997; color: white; } /* Groen voor user ID toewijzen */
        button[name="action"][value="assign_user_id_web"]:hover { background-color: #17a2b8; }

        .key-value { font-family: 'Consolas', 'Monaco', monospace; background-color: #e9ecef; padding: 5px 8px; border-radius: 3px; }
        .logout-button {
            background-color: #f44336; /* Rood */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            float: right; /* Plaats de knop aan de rechterkant */
            margin-top: 10px;
        }
        .logout-button:hover {
            background-color: #da190b;
        }
    </style>
</head>
<body>

<h1>🔐 Key Controle Paneel <a href="?action=logout" class="logout-button">Uitloggen</a></h1>


<div class="create-form">
    <form method="POST">
        <h3>🆕 Nieuwe Key Aanmaken</h3>
        <label>
            <input type="checkbox" name="is_premium"> Premium key?
        </label><br>
        <label for="expires_at">Verloopdatum:</label>
        <input type="datetime-local" id="expires_at" name="expires_at" required><br>
        <label for="user_id_assign">Optioneel: Koppel aan Gebruiker ID (bij aanmaak):</label>
        <input type="text" id="user_id_assign" name="user_id_assign" placeholder="Voer apparaat ID in (optioneel)"><br>
        <button type="submit" name="create_key">➕ Genereer Key</button>
    </form>
</div>

<h2>📋 Alle Keys</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Key</th>
            <th>Premium</th>
            <th>Actief</th>
            <th>Gebruiker ID</th> <th>Verloopt op</th>
            <th>Aangemaakt op</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($keys as $key): ?>
        <tr>
            <td><?= $key['id'] ?></td>
            <td><code class="key-value"><?= htmlspecialchars($key['key_value']) ?></code></td>
            <td class="<?= $key['is_premium'] ? 'premium' : 'not-premium' ?>">
                <?= $key['is_premium'] ? 'Ja' : 'Nee' ?>
            </td>
            <td class="<?= $key['is_active'] ? 'active' : 'inactive' ?>">
                <?= $key['is_active'] ? 'Ja' : 'Nee' ?>
            </td>
            <td><?= htmlspecialchars($key['user_id'] ?? 'N.V.T.') ?></td> <td><?= $key['expires_at'] ?></td>
            <td><?= $key['created_at'] ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $key['id'] ?>">
                    <button name="action" value="toggle_premium" title="Toggle Premium Status">🌟 Toggle Premium</button>
                    <button name="action" value="toggle_active" title="Activeer/Deactiveer Key (Pauzeer)">⏸️ Toggle Actief</button>
                    <button name="action" value="extend_30_days" title="Verleng met 30 dagen">➕ 30 Dagen</button>
                    <br>
                    <input type="text" name="new_user_id" placeholder="Nieuw Gebruiker ID" style="width: 120px; margin-right: 5px;">
                    <button name="action" value="assign_user_id_web" title="Wijs gebruiker ID toe">🔗 Wijs ID toe</button>
                    <button name="action" value="delete" onclick="return confirm('Weet je het zeker dat je deze key wilt verwijderen? Dit kan niet ongedaan gemaakt worden!');" title="Verwijder Key">🗑️ Verwijder</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h2>📱 Geregistreerde Apparaten</h2>
<?php
$devices = $pdo->query("SELECT * FROM `device_ids` ORDER BY last_seen DESC")->fetchAll();
echo "<p><strong>Debug: Aantal geregistreerde apparaten: " . count($devices) . "</strong></p>";
if (count($devices) == 0) {
    echo "<p><strong>Debug: Geen apparaten gevonden in de database. Controleer of de API-aanroepen werken.</strong></p>";
}
?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Apparaat ID</th>
            <th>IP Adres</th>
            <th>User Agent</th>
            <th>Eerste registratie</th>
            <th>Laatst gezien</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($devices as $device): ?>
        <tr>
            <td><?= $device['id'] ?></td>
            <td><code><?= htmlspecialchars($device['device_id']) ?></code></td>
            <td><?= htmlspecialchars($device['ip_address'] ?? 'Onbekend') ?></td>
            <td><?= htmlspecialchars(substr($device['user_agent'] ?? 'Onbekend', 0, 50)) ?></td>
            <td><?= $device['created_at'] ?></td>
            <td><?= $device['last_seen'] ?? 'Nog niet' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Databaseverbinding mislukt: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM device_ids ORDER BY created_at DESC");
?>

<h2>Geregistreerde Apparaten</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Device ID</th>
        <th>IP Adres</th>
        <th>Laatste Bezoek</th>
        <th>Premium</th>
        <th>Acties</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['device_id']) ?></td>
            <td><?= htmlspecialchars($row['ip_address']) ?></td>
            <td><?= $row['last_seen'] ?></td>
            <td>
                <?php
                // Controleer of deze device_id een premium key heeft
                $stmt = $conn->prepare("SELECT is_premium FROM keys WHERE user_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1");
                $stmt->bind_param("s", $row['device_id']);
                $stmt->execute();
                $stmt->bind_result($is_premium);
                $stmt->fetch();
                echo ($is_premium == 1) ? '✅ Ja' : '❌ Nee';
                $stmt->close();
                ?>
            </td>
            <td>
                <form method="post" action="admin.php">
                    <input type="hidden" name="device_id" value="<?= $row['device_id'] ?>">
                    <input type="submit" name="set_premium" value="Zet Premium Aan">
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<?php $conn->close(); ?>


</body>
</html>


