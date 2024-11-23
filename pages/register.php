<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Проверка на существующего пользователя
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo 'Пользователь с таким email уже существует.';
    } else {
        // Вставка нового пользователя в базу данных
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, middle_name, email, password) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $middle_name, $email, $password]);

        // Сохранение данных о пользователе в сессии
        $_SESSION['user'] = [
            'id' => $db->lastInsertId(), // ID нового пользователя
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name,
            'email' => $email
        ];

        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="/style.css"> <!-- Подключаем общий стиль -->
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2>Регистрация</h2>
        <form method="POST">
            <input type="text" name="last_name" class="form-input" placeholder="Фамилия" required>
            <input type="text" name="first_name" class="form-input" placeholder="Имя" required>
            <input type="text" name="middle_name" class="form-input" placeholder="Отчество" required>
            <input type="email" name="email" class="form-input" placeholder="Электронная почта" required>
            <input type="password" name="password" class="form-input" placeholder="Пароль" required>
            <button type="submit" class="form-submit">Зарегистрироваться</button>
        </form>

        <div class="form-link">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </div>
    </div>
</div>

</body>
</html>
