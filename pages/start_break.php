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

// Получаем тип смены пользователя
$stmt_shift = $db->prepare("SELECT shift_type FROM users WHERE id = ?");
$stmt_shift->execute([$user_id]);
$user_shift = $stmt_shift->fetch();

// Определяем доступное время на перерывы в зависимости от типа смены
$break_time_allowed = ($user_shift['shift_type'] == '8 часов') ? 30 : 45; // для 8 часов - 30 минут, для 12 часов - 45 минут

// Получаем общее время использованных перерывов
$stmt_breaks = $db->prepare("SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS total_break_time 
                             FROM breaks 
                             WHERE user_id = ? AND end_time IS NOT NULL");
$stmt_breaks->execute([$user_id]);
$breaks_data = $stmt_breaks->fetch();
$total_break_time = $breaks_data['total_break_time'] ?: 0;

// Проверяем, есть ли достаточно времени для нового перерыва
$remaining_break_time = $break_time_allowed - $total_break_time;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['break_type'])) {
    $break_type = $_POST['break_type'];
    $break_duration = ($break_type === '10 минут') ? 10 : ($break_type === '15 минут' ? 15 : 5);

    // Проверка доступных слотов
    $stmt_slots = $db->prepare("SELECT available_slots FROM break_slots WHERE break_type = ?");
    $stmt_slots->execute([$break_type]);
    $slots_data = $stmt_slots->fetch();

    if ($slots_data && $slots_data['available_slots'] > 0) {
        if ($remaining_break_time >= $break_duration) {
            $start_time = date('Y-m-d H:i:s');
            
            // Вставка нового перерыва в базу данных
            $stmt = $db->prepare("INSERT INTO breaks (user_id, start_time, break_type) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $start_time, $break_type]);

            // Уменьшаем количество доступных слотов
            $stmt_update_slots = $db->prepare("UPDATE break_slots SET available_slots = available_slots - 1 WHERE break_type = ?");
            $stmt_update_slots->execute([$break_type]);

            // Возвращаем успешный ответ
            echo json_encode(['success' => true, 'start_time' => $start_time, 'break_type' => $break_type]);
        } else {
            echo json_encode(['success' => false, 'message' => 'У вас недостаточно времени для этого перерыва!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Нет доступных слотов для этого перерыва!']);
    }
}
?>
