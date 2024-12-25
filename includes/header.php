<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle ?? 'Сырный магазин'; ?></title>
    <link rel="stylesheet" href="/cheese/style.css">
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="/cheese/<?php echo $script; ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="logo">
            <img src="/cheese/images/cheese-logo.png" alt="Логотип">
        </div>
        <nav>
            <ul>
                <li><a href="/cheese/">Главная</a></li>
                <li><a href="/cheese/about.php">О нас</a></li>
                <li><a href="/cheese/contact.php">Контакты</a></li>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="/cheese/admin/">Админ панель</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
