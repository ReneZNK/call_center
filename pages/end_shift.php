<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Проверяем, есть ли активная смена
$stmt_check = $db->prepare("SELECT * FROM shifts WHERE user_id = ? AND end_time IS NULL");
$stmt_check->execute([$user_id]);
$active_shift = $stmt_check->fetch();

if ($active_shift) {
    // Проверка, нужно ли завершить смену по времени
    $shift_type = $active_shift['shift_type'];
    $start_time = strtotime($active_shift['start_time']);
    $current_time = time();
    
    // Расчет длительности смены
    if ($shift_type === '8 часов') {
        $shift_duration = 8 * 3600; // 8 часов в секундах
    } elseif ($shift_type === '12 часов') {
        $shift_duration = 12 * 3600; // 12 часов в секундах
    }
    
    // Проверка, прошло ли время смены
    if (($current_time - $start_time) >= $shift_duration) {
        // Если время истекло, автоматически завершаем смену
        $end_time = date('Y-m-d H:i:s');
        $stmt_end_shift = $db->prepare("UPDATE shifts SET end_time = ? WHERE id = ?");
        $stmt_end_shift->execute([$end_time, $active_shift['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Смена завершена автоматически.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Смена ещё не закончена.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Нет активной смены.']);
}

?>
