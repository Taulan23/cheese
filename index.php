<?php
require_once 'config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

// Получение страницы из БД
$slug = isset($_GET['page']) ? $_GET['page'] : 'home';
$query = "SELECT * FROM pages WHERE slug = '" . mysqli_real_escape_string($conn, $slug) . "'";
$result = mysqli_query($conn, $query);
$page = mysqli_fetch_assoc($result);

// Получение всех страниц для меню
$query = "SELECT title, slug FROM pages";
$result = mysqli_query($conn, $query);
$pages = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($page['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="scripts.js" defer></script>
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
        <?php if ($slug === 'home'): ?>
            <div class="intro">
                <h1>Добро пожаловать в мир изысканных сыров!</h1>
                <p>Мы предлагаем широкий выбор элитных сыров со всего мира.</p>
                
                <div id="countdown">
                    <h2>До следующей акции осталось:</h2>
                    <p id="timer"></p>
                </div>

                <div id="image-container">
                    <h2>Популярные сыры:</h2>
                    <img id="cheese-image" src="images/cheese1.jpg" alt="Популярный сыр">
                </div>

                <div class="cheese-types">
                    <h2>Наши лучшие сорта:</h2>
                    <ul>
                        <li>Пармезан выдержанный</li>
                        <li>Горгонзола</li>
                        <li>Камамбер</li>
                        <li>Грюйер</li>
                        <li>Рокфор</li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <h1><?php echo htmlspecialchars($page['title']); ?></h1>
            <div class="content">
                <?php echo $page['content']; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div id="clock"></div>
        <p>&copy; 2024 Сырный магазин. Все права защищены.</p>
    </footer>
</body>
</html>
