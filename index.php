<?php
session_start();

// Если пользователь уже авторизован, перенаправляем на панель управления
if (isset($_SESSION['user'])) {
    header('Location: pages/dashboard.php');
    exit;
}

// Если пользователь не авторизован, перенаправляем на страницу входа
header('Location: pages/login.php');
exit;
?>
