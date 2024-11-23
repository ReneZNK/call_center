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

        .login-container {
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
            .login-container {
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

<div class="login-container">
    <h2>Вход в систему</h2>
    <form method="POST">
        <input type="email" name="email" class="form-input" placeholder="Электронная почта" required>
        <input type="password" name="password" class="form-input" placeholder="Пароль" required>
        
        <button type="submit" class="form-submit">Войти</button>
    </form>
</div>

</body>
</html>
