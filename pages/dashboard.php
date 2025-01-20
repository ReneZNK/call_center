<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user']['id'];

// Получаем данные о пользователе (ФИО и тип смены)
$stmt_user = $db->prepare("SELECT first_name, last_name, middle_name, shift_type FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();
// Проверка на наличие активной смены
$stmt_check_shift = $db->prepare("SELECT * FROM shifts WHERE user_id = ? AND end_time IS NULL");
$stmt_check_shift->execute([$user_id]);
$active_shift = $stmt_check_shift->fetch();

$stmt_active_shifts = $db->prepare("SELECT users.first_name, users.last_name, users.middle_name, users.shift_type, shifts.start_time 
    FROM shifts 
    JOIN users ON shifts.user_id = users.id
    WHERE shifts.end_time IS NULL
");
$stmt_active_shifts->execute();
$active_users = $stmt_active_shifts->fetchAll();

// Определяем доступное время на перерывы в зависимости от типа смены
$break_time_allowed = ($user['shift_type'] == '8 часов') ? 30 : 45; // для 8 часов - 30 минут, для 12 часов - 45 минут

// Получаем данные о перерывах пользователя и считаем общее время использованных перерывов
$stmt_breaks = $db->prepare("SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) AS total_break_time 
                             FROM breaks 
                             WHERE user_id = ? AND end_time IS NOT NULL");
$stmt_breaks->execute([$user_id]);
$breaks_data = $stmt_breaks->fetch();
$total_break_time = $breaks_data['total_break_time'] ?: 0; // Если перерывы не завершены, то 0

// Рассчитываем оставшееся время для перерывов
$remaining_break_time = $break_time_allowed - $total_break_time;

// Функция для получения перерывов по типам
function getBreaksByType($db, $break_type) {
    $stmt = $db->prepare("SELECT breaks.*, users.first_name, users.last_name, users.middle_name 
                          FROM breaks 
                          JOIN users ON breaks.user_id = users.id
                          WHERE breaks.break_type = ? AND breaks.end_time IS NULL");
    $stmt->execute([$break_type]);
    return $stmt->fetchAll();
}

// Получаем информацию о доступных слотах для каждого типа перерыва
$stmt_slots = $db->prepare("SELECT * FROM break_slots");
$stmt_slots->execute();
$slots = $stmt_slots->fetchAll();

// Получаем перерывы по типам
$breaks_10min = getBreaksByType($db, '10 минут');
$breaks_15min = getBreaksByType($db, '15 минут');
$breaks_5min = getBreaksByType($db, '5 минут');

// Если смена завершена, сбрасываем оставшееся время
if (!$active_shift) {
    $remaining_break_time = 0; // Сбросить время перерывов, если смена завершена
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Перерывы</title>
    <link rel="stylesheet" href="/style.css"> <!-- Подключаем общий стиль -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container">
    <!-- Приветствие с ФИО -->
    <div class="navbar">
        <span>Добро пожаловать, <?= $user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']; ?>!</span>
        <?php if ($_SESSION['user']['role'] == 'admin'): ?>
        <a href="manage_break_slots.php">Слоты </a> 
        <a href="register.php">Регистрация нового пользователя </a> 
        <a href="edit.php">Изменение пользователя</a>
        <a href="generate_report.php">Отчётность</a>
        <a href="logout.php">Выйти</a>
        <?php else: ?>
        <a href="logout.php">Выйти</a>
        <?php endif; ?>
    </div>

    <div class="shift-container">
        <h3>Смена, <?=$user['shift_type'];?> </h3>
        <?php if ($active_shift): ?>
            <button id="start-shift" class="btn btn-primary" disabled>Начало смены</button>
            <button id="end-shift" class="btn btn-secondary">Конец смены</button>
        <?php else: ?>
            <button id="start-shift" class="btn btn-primary">Начало смены</button>
            <button id="end-shift" class="btn btn-secondary" disabled>Конец смены</button>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('start-shift').addEventListener('click', function () {
            fetch('start_shift.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('start-shift').disabled = true;
                        document.getElementById('end-shift').disabled = false;
                        alert('Смена начата!');
                        location.reload();
                    } else {
                        alert(data.message || 'Ошибка сервера!');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Ошибка подключения!');
                });
        });

        document.getElementById('end-shift').addEventListener('click', function () {
    fetch('end_shift.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                document.getElementById('start-shift').disabled = false;
                document.getElementById('end-shift').disabled = true;
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Ошибка подключения!');
        });
});
    </script>

    <h1>Перерывы сотрудников</h1>

    <!-- Форма для выхода на перерыв -->
    <h3>Выход на перерыв</h3>
    <form action="start_break.php" method="POST">
    <h3>Оставшееся время на перерывы: <?= $remaining_break_time ?> минут</h3>
        <label for="break_type">Выберите тип перерыва:</label>
        <select name="break_type" id="break_type" required>
            <option value="15 минут">15 минут</option>
            <option value="10 минут">10 минут</option>
            <option value="5 минут">5 минут</option>
        </select>
        <button type="submit" class="form-submit">Выйти на перерыв</button>
    </form>

    <h1>Сотрудники на смене</h1>
    <table>
        <thead>
            <tr>
                <th>Ф.И.О.</th>
                <th>Тип смены</th>
                <th>Время начала</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($active_users as $active_user): ?>
        <tr>
            <td><?= $active_user['last_name'] . ' ' . $active_user['first_name'] . ' ' . $active_user['middle_name']; ?></td>
            <td><?= $active_user['shift_type']; ?></td>
            <td><?= date('H:i:s', strtotime($active_user['start_time'])); ?></td> <!-- Форматирование времени -->
        </tr>
    <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Таблицы перерывов для различных типов -->
    <?php 
    $break_types = ['15 минут' => $breaks_15min, '10 минут' => $breaks_10min, '5 минут' => $breaks_5min];
    foreach ($break_types as $break_type => $breaks): 

        // Получаем количество доступных слотов для этого типа перерыва
        $available_slots = 0;
        foreach ($slots as $slot) {
            if ($slot['break_type'] == $break_type) {
                $available_slots = $slot['available_slots'];
                break;
            }
        }
?>
        <h3><?= $break_type ?> (<?= $available_slots ?> слотов)</h3>
        <table>
            <thead>
                <tr>
                    <th>Ф.И.О.</th>
                    <th>Время выхода</th>
                    <th>Таймер</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($breaks as $row): ?>
                    <tr id="break-<?= $row['id']; ?>">
                        <td><?= $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name']; ?></td>
                        <td><?= $row['start_time']; ?></td>
                        <td id="duration-<?= $row['id']; ?>">00:00:00</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="end-break-container">
            <?php foreach ($breaks as $row): ?>
                <?php if ($row['user_id'] == $user_id): ?>
                    <button class="end-break" data-id="<?= $row['id']; ?>">Завершить</button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <footer>
        <p>&copy; 2024 Call Center. Все права защищены.</p>
    </footer>
</div>

<script>
$(document).ready(function() {
    // Обработчик формы выхода на перерыв
    $('form').submit(function(e) {
        e.preventDefault();
        var break_type = $('#break_type').val();
        var remainingTime = <?= $remaining_break_time ?>;
        var breakDuration = break_type === '10 минут' ? 10 : (break_type === '15 минут' ? 15 : 5);

        if (remainingTime >= breakDuration) {
            $.ajax({
                url: 'start_break.php',
                type: 'POST',
                data: { break_type: break_type },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                },
                error: function() {
                    alert("Произошла ошибка при выходе на перерыв.");
                }
            });
        } else {
            alert('У вас недостаточно времени для этого перерыва!');
        }
    });

    // Обновление таймера
    setInterval(function() {
        $('tr[id^="break-"]').each(function() {
            var break_id = $(this).attr('id').split('-')[1];
            var duration_cell = $('#duration-' + break_id);
            $.ajax({
                url: 'update_break_time.php',
                type: 'POST',
                data: { break_id: break_id },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.duration) {
                        duration_cell.text(data.duration);
                    }
                }
            });
        });
    }, 1000); 

    // Завершение перерыва
    $('.end-break').click(function() {
        var break_id = $(this).data('id');
        $.ajax({
            url: 'end_break.php',
            type: 'POST',
            data: { break_id: break_id },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    $('#break-' + break_id).remove();
                    $('.end-break[data-id="' + break_id + '"]').remove();
                    location.reload();
                } else {
                    alert(data.message);
                }
            },
            error: function() {
                alert("Произошла ошибка при завершении перерыва.");
            }
        });
    });
});
</script>

</body>
</html>
