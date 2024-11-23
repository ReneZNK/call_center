<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user']['id'];

// Проверка, есть ли активный перерыв
$stmt_check = $db->prepare("SELECT * FROM breaks WHERE user_id = ? AND end_time IS NULL");
$stmt_check->execute([$user_id]);
$active_break = $stmt_check->fetch();

if ($active_break) {
    // Если активный перерыв уже есть, выводим ошибку
    echo json_encode(['success' => false, 'message' => 'Вы уже на перерыве! Завершите текущий перерыв перед началом нового.']);
    exit;
}

// Если нет активного перерыва, продолжаем
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['break_type'])) {
    $break_type = $_POST['break_type'];
    $start_time = date('Y-m-d H:i:s');
    
    // Вставка нового перерыва в базу данных
    $stmt = $db->prepare("INSERT INTO breaks (user_id, start_time, break_type) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $start_time, $break_type]);

    echo json_encode(['success' => true]);
}
?>
