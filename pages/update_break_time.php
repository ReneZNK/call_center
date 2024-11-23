<?php
session_start();
require_once '../includes/db.php';

if (isset($_POST['break_id'])) {
    $break_id = $_POST['break_id'];

    // Получаем информацию о перерыве из базы данных
    $stmt = $db->prepare("SELECT start_time FROM breaks WHERE id = ? AND end_time IS NULL");
    $stmt->execute([$break_id]);
    $row = $stmt->fetch();

    if ($row) {
        $start_time = strtotime($row['start_time']);
        $current_time = time();
        $duration = $current_time - $start_time; // Разница в секундах

        // Переводим длительность в формат ЧЧ:ММ:СС
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;
        $formatted_time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

        // Возвращаем результат в формате JSON
        echo json_encode(['duration' => $formatted_time]);
    } else {
        echo json_encode(['duration' => '00:00:00']);
    }
}
?>
