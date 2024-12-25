<?php
require_once 'config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

// Получение контента страницы из БД
$query = "SELECT * FROM pages WHERE slug = 'about'";
$result = mysqli_query($conn, $query);
$page = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>О нас</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/logo.png" alt="Логотип">
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="about.php">О нас</a></li>
                <li><a href="contact.php">Контакты</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="contact.php?action=message" class="btn-message">Написать сообщение</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="user-info">
            Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            <a href="logout.php">Выход</a>
            <?php if ($_SESSION['is_admin']): ?>
                <a href="admin.php" class="admin-link">Панель администратора</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="content">
            <h1><?php echo htmlspecialchars($page['title']); ?></h1>
            <?php echo $page['content']; ?>
        </div>
    </main>

    <footer>
        <div id="clock"></div>
        <p>&copy; 2024 Сырный магазин. Все права защищены.</p>
    </footer>
</body>
</html>