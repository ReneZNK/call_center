<?php
$host = 'localhost';
$dbname = 'call_center';
$username = 'root';
$password = ''; // Обычно пустой для XAMPP

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>
