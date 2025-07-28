<?php
$host = 'sql310.infinityfree.com';
$user = 'if0_39575378';
$pass = 'Zvj7zTm1N3HGh';
$db   = 'if0_39575378_keymanager';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("❌ Databaseverbinding mislukt: " . mysqli_connect_error());
}
?>
