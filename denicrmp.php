<?php
// mysql данные (SLIV DENI TG @DENICRMP)
$servername = "127.0.0.1";
$username = "user43823";
$password = "Neznayudopystim";
$dbname = "user43823";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Нет подключения: " . $conn->connect_error);
}
?>
