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

        $_SESSION['user'] = ['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email];
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
</head>
<body>
    <h2>Регистрация</h2>
    <form method="POST">
        <label for="last_name">Фамилия:</label>
        <input type="text" name="last_name" id="last_name" required>
        <br>

        <label for="first_name">Имя:</label>
        <input type="text" name="first_name" id="first_name" required>
        <br>

        <label for="middle_name">Отчество:</label>
        <input type="text" name="middle_name" id="middle_name" required>
        <br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        <br>

        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>
        <br>

        <button type="submit">Зарегистрироваться</button>
    </form>
</body>
</html>
