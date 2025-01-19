<?php
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'moderator')) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Получение текущих данных по слотам
$stmt = $db->prepare("SELECT * FROM break_slots");
$stmt->execute();
$slots = $stmt->fetchAll();

$responseMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['break_type']) && isset($_POST['available_slots'])) {
    $break_type = $_POST['break_type'];
    $available_slots = $_POST['available_slots'];

    // Проверка на правильность данных
    if ($available_slots >= 0) {
        // Обновляем количество доступных слотов
        $stmt_update = $db->prepare("UPDATE break_slots SET available_slots = ? WHERE break_type = ?");
        $stmt_update->execute([$available_slots, $break_type]);
        $responseMessage = 'Количество слотов обновлено.';
    } else {
        $responseMessage = 'Количество слотов не может быть отрицательным.';
    }

    // Перенаправление для предотвращения повторной отправки формы
    header('Location: ' . $_SERVER['PHP_SELF'] . '?message=' . urlencode($responseMessage));
    exit;
}

// Проверка наличия сообщения в параметрах URL
if (isset($_GET['message'])) {
    $responseMessage = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>Управление слотами перерывов</title>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const responseMessage = "<?= htmlspecialchars($responseMessage) ?>";
            if (responseMessage) {
                alert(responseMessage);
            }
        });
    </script>
</head>
<body>
    <h1>Управление слотами перерывов</h1>
    <form method="POST">
        <label for="break_type">Тип перерыва:</label>
        <select name="break_type" id="break_type">
        <option value="15 минут">15 минут</option>
            <option value="10 минут">10 минут</option>
            <option value="5 минут">5 минут</option>
        </select><br><br>

        <label for="available_slots">Доступные слоты:</label>
        <input type="number" name="available_slots" id="available_slots" min="0"><br><br>

        <button type="submit">Обновить слоты</button>
    </form>

    <h2>Текущие слоты:</h2>
    <table border="1">
        <tr>
            <th>Тип перерыва</th>
            <th>Доступные слоты</th>
        </tr>
        <?php foreach ($slots as $slot): ?>
        <tr>
            <td><?= htmlspecialchars($slot['break_type']) ?></td>
            <td><?= htmlspecialchars($slot['available_slots']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
