<?php
session_start();

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user']['id'];
$break_id = $_POST['break_id'];

// Проверяем, что этот перерыв принадлежит текущему пользователю
$stmt = $db->prepare("SELECT * FROM breaks WHERE id = ? AND user_id = ?");
$stmt->execute([$break_id, $user_id]);
$break = $stmt->fetch();

if ($break) {
    // Завершаем перерыв, обновляем время окончания
    $end_time = date('Y-m-d H:i:s');
    $stmt_end = $db->prepare("UPDATE breaks SET end_time = ? WHERE id = ?");
    $stmt_end->execute([$end_time, $break_id]);

    echo json_encode(['success' => true]);
} else {
    // Если перерыв не найден или не принадлежит пользователю
    echo json_encode(['success' => false, 'message' => 'Вы не можете завершить этот перерыв.']);
}
?>
