<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Получаем активную смену пользователя
$stmt_check = $db->prepare("SELECT * FROM shifts WHERE user_id = ? AND end_time IS NULL");
$stmt_check->execute([$user_id]);
$active_shift = $stmt_check->fetch();

if ($active_shift) {
    // Получаем тип смены из таблицы users
    $stmt_user = $db->prepare("SELECT shift_type FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch();
    
    if (!$user || empty($user['shift_type'])) {
        echo json_encode(['success' => false, 'message' => 'Не удалось определить тип смены пользователя.']);
        exit;
    }

    $shift_type = $user['shift_type']; // Тип смены из таблицы users
    $start_time = strtotime($active_shift['start_time']);
    $current_time = time();

    // Расчет длительности смены
    if ($shift_type === '8 часов') {
        $shift_duration = 8 * 3600; // 8 часов в секундах
    } elseif ($shift_type === '12 часов') {
        $shift_duration = 12 * 3600; // 12 часов в секундах
    } else {
        echo json_encode(['success' => false, 'message' => 'Неизвестный тип смены: ' . $shift_type]);
        exit;
    }

    // Проверка, прошло ли время смены
    if (($current_time - $start_time) >= $shift_duration) {
        // Если время истекло, завершаем смену
        $end_time = date('Y-m-d H:i:s');
        $stmt_end_shift = $db->prepare("UPDATE shifts SET end_time = ? WHERE id = ?");
        $stmt_end_shift->execute([$end_time, $active_shift['id']]);

        // Удаляем все незавершенные перерывы для этого пользователя
        $stmt_delete_breaks = $db->prepare("DELETE FROM breaks WHERE user_id = ?");
        $stmt_delete_breaks->execute([$user_id]);

        echo json_encode(['success' => true, 'message' => 'Смена успешно завершена.']);
    } else {
        // Если время смены ещё не истекло, выводим предупреждение
        $remaining_time = $shift_duration - ($current_time - $start_time);
        $hours = floor($remaining_time / 3600);
        $minutes = floor(($remaining_time % 3600) / 60);

        echo json_encode([
            'success' => false,
            'message' => "Смена ещё не закончена. Осталось времени: {$hours} часов и {$minutes} минут."
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Нет активной смены.']);
}
?>
