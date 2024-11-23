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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .registration-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        .form-input:focus {
            outline: none;
            border-color: #4CAF50;
            background-color: #fff;
        }

        .form-submit {
            width: 100%;
            padding: 14px;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-submit:hover {
            background-color: #45a049;
        }

        .form-link {
            display: block;
            margin-top: 15px;
            text-align: center;
        }

        .form-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .form-link a:hover {
            text-decoration: underline;
        }

        /* Адаптивность для мобильных устройств */
        @media screen and (max-width: 768px) {
            .registration-container {
                padding: 20px;
                width: 90%;
            }

            .form-input {
                font-size: 14px;
            }

            .form-submit {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="registration-container">
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

</body>
</html>
