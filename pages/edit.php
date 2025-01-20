<?php
session_start();
require_once '../includes/db.php';

// Проверка, что пользователь является администратором
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Обработка редактирования пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Если пароль не пустой, то хэшируем новый пароль
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, middle_name = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $middle_name, $email, $role, $password, $user_id]);
    } else {
        // Если пароль не был изменен, обновляем только другие данные
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, middle_name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $middle_name, $email, $role, $user_id]);
    }

    header('Location: edit.php'); // Перенаправление после успешного редактирования
    exit;
}

// Обработка удаления пользователя
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Убедимся, что мы не пытаемся удалить самого себя (администратора)
    if ($delete_id != $_SESSION['user']['id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
    }
    
    header('Location: edit.php'); // Перенаправление после удаления
    exit;
}

// Получение списка всех пользователей
$stmt = $db->prepare("SELECT id, first_name, last_name, middle_name, email, role FROM users");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователей</title>
    <link rel="stylesheet" href="/style.css">
    <script>
        function redirectToDashboard() {
            window.location.href = "dashboard.php"; // Перенаправление на dashboard.php
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Список пользователей</h2>

    <!-- Список пользователей с кнопками редактирования и удаления -->
    <table>
        <thead>
            <tr>
                <th>Имя</th>
                <th>Фамилия</th>
                <th>Отчество</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['first_name']) ?></td>
                <td><?= htmlspecialchars($user['last_name']) ?></td>
                <td><?= htmlspecialchars($user['middle_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <a href="edit.php?edit_id=<?= $user['id'] ?>">Редактировать</a> | 
                    <a href="edit.php?delete_id=<?= $user['id'] ?>" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Форма редактирования данных пользователя -->
    <?php if (isset($_GET['edit_id'])):
        $edit_id = $_GET['edit_id'];
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$edit_id]);
        $user = $stmt->fetch();
        ?>
        <h2>Редактировать пользователя</h2>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($user['first_name']) ?>" required>
            <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            <input type="text" name="middle_name" class="form-input" value="<?= htmlspecialchars($user['middle_name']) ?>" required>
            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" required>
            
            <label for="role">Роль:</label>
            <select name="role" id="role" required>
                <option value="worker" <?= $user['role'] === 'worker' ? 'selected' : '' ?>>Работник</option>
                <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>Модератор</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
            </select>

            <label for="password">Новый пароль (оставьте пустым, если не хотите менять):</label>
            <input type="password" name="password" class="form-input" placeholder="Введите новый пароль">

            <button type="submit" class="form-submit">Сохранить изменения</button>
        </form>
            
    <?php endif; ?>
    <div class="back-to-dashboard">
    <button type="button" onclick="redirectToDashboard()" class="button">Перейти на главую страницу</button>
    </div>
</div>

</body>
</html>
