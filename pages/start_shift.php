<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user']['id'];

// Проверка, есть ли уже активная смена
$stmt_check = $db->prepare("SELECT * FROM shifts WHERE user_id = ? AND end_time IS NULL");
$stmt_check->execute([$user_id]);
$active_shift = $stmt_check->fetch();

if ($active_shift) {
    // Если активная смена существует, выводим ошибку
    echo json_encode(['success' => false, 'message' => 'Смена уже начата!']);
    exit;
}

// Начало новой смены
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time = date('Y-m-d H:i:s');
    
    // Вставка новой смены в базу данных
    $stmt = $db->prepare("INSERT INTO shifts (user_id, start_time) VALUES (?, ?)");
    $stmt->execute([$user_id, $start_time]);

    // Возвращаем успешный ответ
    echo json_encode(['success' => true, 'start_time' => $start_time]);
}
?>
