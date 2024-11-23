<?php
session_start();
session_destroy(); // Уничтожаем все сессионные данные
header('Location: login.php'); // Перенаправляем на страницу входа
exit;
?>
