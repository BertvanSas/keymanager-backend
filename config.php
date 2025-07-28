<?php
$host = 'sql310.infinityfree.com';
$db   = 'if0_39575378_keymanager';
$user = 'if0_39575378';
$pass = 'Zvj7zTm1N3HGh';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // Test de databaseverbinding
     $stmt = $pdo->query("SELECT 1");
     if ($stmt) {
         error_log("Databaseverbinding succesvol getest.");
     }
} catch (\PDOException $e) {
     error_log("Database connectie fout: " . $e->getMessage());
     die("Er is een probleem met de databaseverbinding opgetreden: " . $e->getMessage());
}
?>