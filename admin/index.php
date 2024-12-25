<?php
session_start();
require_once '../includes/db.php';

// Проверка прав администратора
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../auth.php');
    exit;
}

// Получение списка страниц
$stmt = $pdo->query("SELECT * FROM pages ORDER BY id DESC");
$pages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-panel">
        <h1>Управление страницами</h1>
        
        <a href="add_page.php" class="button">Добавить страницу</a>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Slug</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($pages as $page): ?>
            <tr>
                <td><?php echo $page['id']; ?></td>
                <td><?php echo htmlspecialchars($page['title']); ?></td>
                <td><?php echo htmlspecialchars($page['slug']); ?></td>
                <td>
                    <a href="edit_page.php?id=<?php echo $page['id']; ?>">Редактировать</a>
                    <a href="delete_page.php?id=<?php echo $page['id']; ?>" 
                       onclick="return confirm('Вы уверены?')">Удалить</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html> 