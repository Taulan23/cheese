<?php
require_once 'config.php';

// Очистка сессии
session_destroy();

// Перенаправление на страницу входа
header('Location: auth.php');
exit; 