<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['break_id'])) {
    $break_id = $_POST['break_id'];

    // Завершаем перерыв
    $stmt = $db->prepare("UPDATE breaks SET end_time = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([date('Y-m-d H:i:s'), $break_id, $user_id]);

    // Получаем информацию о типе перерыва
    $stmt_break = $db->prepare("SELECT break_type FROM breaks WHERE id = ?");
    $stmt_break->execute([$break_id]);
    $break_data = $stmt_break->fetch();

    if ($break_data) {
        $break_type = $break_data['break_type'];

        // Обновляем количество доступных слотов для выбранного типа перерыва
        $stmt_slots = $db->prepare("UPDATE break_slots SET available_slots = available_slots + 1 WHERE break_type = ?");
        $stmt_slots->execute([$break_type]);

        // Возвращаем успешный ответ
        echo json_encode(['success' => true, 'message' => 'Перерыв завершен, слот возвращен!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Не удалось найти перерыв.']);
    }
}
?>
