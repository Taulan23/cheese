<?php
require_once 'config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php?redirect=contact.php');
    exit;
}

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $user_id = $_SESSION['user_id'];
    
    $query = "INSERT INTO messages (user_id, subject, message) VALUES ($user_id, '$subject', '$message')";
    if (mysqli_query($conn, $query)) {
        $success = "Сообщение успешно отправлено! Администратор ответит вам в ближайшее время.";
    } else {
        $error = "Ошибка при отправке сообщения: " . mysqli_error($conn);
    }
}

// Получение сообщений текущего пользователя
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM messages WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Определяем, показывать ли форму сообщения
$show_message_form = isset($_GET['action']) && $_GET['action'] === 'message';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Контакты</title>
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
        <?php if ($show_message_form): ?>
            <div class="message-form-container">
                <h2>Написать сообщение</h2>
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="message-form">
                    <div>
                        <label>Тема:</label>
                        <input type="text" name="subject" required>
                    </div>
                    <div>
                        <label>Сообщение:</label>
                        <textarea name="message" required></textarea>
                    </div>
                    <button type="submit">Отправить</button>
                </form>
            </div>
        <?php else: ?>
            <div class="contact-info">
                <h2>Наши контакты</h2>
                <p>Адрес: ул. Сырная, 123</p>
                <p>Телефон: +7 (123) 456-78-90</p>
                <p>Email: info@cheese.com</p>
                
                <div class="working-hours">
                    <h3>Часы работы:</h3>
                    <p>Пн-Пт: 9:00 - 20:00</p>
                    <p>Сб-Вс: 10:00 - 18:00</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($messages)): ?>
            <div class="my-messages">
                <h2>Мои сообщения</h2>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-item">
                        <h3><?php echo htmlspecialchars($msg['subject']); ?></h3>
                        <div class="message-meta">
                            Отправлено: <?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </div>
                        <?php if ($msg['reply']): ?>
                            <div class="message-reply">
                                <h4>Ответ администратора:</h4>
                                <div class="reply-content">
                                    <?php echo nl2br(htmlspecialchars($msg['reply'])); ?>
                                </div>
                                <div class="reply-meta">
                                    Получен: <?php echo date('d.m.Y H:i', strtotime($msg['replied_at'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div id="clock"></div>
        <p>&copy; 2024 Сырный магазин. Все права защищены.</p>
    </footer>
</body>
</html>
