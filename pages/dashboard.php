<?php
session_start();

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user']['id'];

// Получаем данные о пользователе (ФИО)
$stmt_user = $db->prepare("SELECT first_name, last_name, middle_name FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Получаем все перерывы по типам
$stmt_10min = $db->prepare("SELECT breaks.*, users.first_name, users.last_name, users.middle_name 
                            FROM breaks 
                            JOIN users ON breaks.user_id = users.id
                            WHERE breaks.break_type = '10 минут' AND breaks.end_time IS NULL");
$stmt_10min->execute();

$stmt_15min = $db->prepare("SELECT breaks.*, users.first_name, users.last_name, users.middle_name 
                            FROM breaks 
                            JOIN users ON breaks.user_id = users.id
                            WHERE breaks.break_type = '15 минут' AND breaks.end_time IS NULL");
$stmt_15min->execute();

$stmt_5min = $db->prepare("SELECT breaks.*, users.first_name, users.last_name, users.middle_name 
                           FROM breaks 
                           JOIN users ON breaks.user_id = users.id
                           WHERE breaks.break_type = '5 минут' AND breaks.end_time IS NULL");
$stmt_5min->execute();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Перерывы</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h3 {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .end-break-container {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<!-- Приветствие с ФИО -->
<h2>Добро пожаловать, <?php echo $user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']; ?>!</h2>

<!-- Кнопка выхода из аккаунта -->
<a href="logout.php"><button>Выйти из аккаунта</button></a>

<!-- Форма для выхода на перерыв -->
<h3>Выход на перерыв</h3>
<form action="start_break.php" method="POST">
    <label for="break_type">Выберите тип перерыва:</label>
    <select name="break_type" id="break_type" required>
        <option value="10 минут">10 минут</option>
        <option value="15 минут">15 минут</option>
        <option value="5 минут">5 минут</option>
    </select>
    <button type="submit">Выйти на перерыв</button>
</form>


<!-- Таблица для 10 минут -->
<h3>10 минут</h3>
<table>
    <thead>
        <tr>
            <th>Ф.И.О.</th>
            <th>Время выхода</th>
            <th>Таймер</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $stmt_10min->fetch()): ?>
            <tr id="break-<?php echo $row['id']; ?>">
                <td><?php echo $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name']; ?></td>
                <td><?php echo $row['start_time']; ?></td>
                <td id="duration-<?php echo $row['id']; ?>">00:00:00</td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Контейнер для кнопок завершения перерыва для 10 минут -->
<div class="end-break-container">
    <?php
    // Переносим вывод кнопок завершения перерыва в отдельный блок
    $stmt_10min->execute(); // Нужно снова выполнить запрос, чтобы использовать его в этом месте
    while ($row = $stmt_10min->fetch()):
    ?>
        <?php if ($row['user_id'] == $user_id): ?>
                        <button class="end-break" data-id="<?php echo $row['id']; ?>">Завершить</button>
                    <?php endif; ?>
    <?php endwhile; ?>
</div>

<!-- Таблица для 15 минут -->
<h3>15 минут</h3>
<table>
    <thead>
        <tr>
            <th>Ф.И.О.</th>
            <th>Время выхода</th>
            <th>Таймер</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $stmt_15min->fetch()): ?>
            <tr id="break-<?php echo $row['id']; ?>">
                <td><?php echo $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name']; ?></td>
                <td><?php echo $row['start_time']; ?></td>
                <td id="duration-<?php echo $row['id']; ?>">00:00:00</td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Контейнер для кнопок завершения перерыва для 15 минут -->
<div class="end-break-container">
    <?php
    // Переносим вывод кнопок завершения перерыва в отдельный блок
    $stmt_15min->execute(); // Нужно снова выполнить запрос, чтобы использовать его в этом месте
    while ($row = $stmt_15min->fetch()):
    ?>
        <?php if ($row['user_id'] == $user_id): ?>
                        <button class="end-break" data-id="<?php echo $row['id']; ?>">Завершить</button>
                    <?php endif; ?>
    <?php endwhile; ?>
</div>

<!-- Таблица для 5 минут -->
<h3>5 минут</h3>
<table>
    <thead>
        <tr>
            <th>Ф.И.О.</th>
            <th>Время выхода</th>
            <th>Таймер</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $stmt_5min->fetch()): ?>
            <tr id="break-<?php echo $row['id']; ?>">
                <td><?php echo $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name']; ?></td>
                <td><?php echo $row['start_time']; ?></td>
                <td id="duration-<?php echo $row['id']; ?>">00:00:00</td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Контейнер для кнопок завершения перерыва для 5 минут -->
<div class="end-break-container">
    <?php
    // Переносим вывод кнопок завершения перерыва в отдельный блок
    $stmt_5min->execute(); // Нужно снова выполнить запрос, чтобы использовать его в этом месте
    while ($row = $stmt_5min->fetch()):
    ?>
        <?php if ($row['user_id'] == $user_id): ?>
                        <button class="end-break" data-id="<?php echo $row['id']; ?>">Завершить</button>
                    <?php endif; ?>
    <?php endwhile; ?>
</div>

<script>
$(document).ready(function() {
    // Обработчик формы выхода на перерыв
    $('form').submit(function(e) {
        e.preventDefault(); // Отменяем стандартное поведение формы

        var break_type = $('#break_type').val(); // Получаем выбранный тип перерыва
        $.ajax({
            url: 'start_break.php',
            type: 'POST',
            data: { break_type: break_type },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    // Если перерыв успешно начат, обновляем таблицу
                    location.reload(); // Перезагружаем страницу, чтобы обновить данные
                } else {
                    // Если ошибка (например, активный перерыв), показываем сообщение
                    alert(data.message);
                }
            },
            error: function() {
                alert("Произошла ошибка при выходе на перерыв.");
            }
        });
    });

    // Обновление таймера
    setInterval(function() {
        $('tr[id^="break-"]').each(function() {
            var break_id = $(this).attr('id').split('-')[1]; // Извлекаем ID перерыва
            var duration_cell = $('#duration-' + break_id);
            $.ajax({
                url: 'update_break_time.php',
                type: 'POST',
                data: { break_id: break_id },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.duration) {
                        duration_cell.text(data.duration); // Обновляем таймер в таблице
                    }
                }
            });
        });
    }, 1000); // обновлять каждую секунду

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
                    // Удаляем строку с завершённым перерывом из таблицы
                    $('#break-' + break_id).remove();
                    
                    // Удаляем кнопку завершения перерыва
                    $('.end-break[data-id="' + break_id + '"]').remove();
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
