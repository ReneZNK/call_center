<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name']];
        header('Location: dashboard.php');
        exit;
    } else {
        echo 'Неверный логин или пароль.';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
</head>
<body>
    <h2>Вход в систему</h2>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        <br>

        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>
        <br>

        <button type="submit">Войти</button>
    </form>
</body>
</html>
