<?php
require_once 'config.php';

// Проверка прав администратора
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    switch ($_POST['action']) {
        case 'edit':
            $id = (int)$_POST['page_id'];
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $content = mysqli_real_escape_string($conn, $_POST['content']);
            $slug = mysqli_real_escape_string($conn, $_POST['slug']);
            
            $query = "UPDATE pages SET title = '$title', content = '$content', slug = '$slug' WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $response['success'] = true;
                $response['message'] = 'Страница успешно обновлена';
            } else {
                $response['message'] = 'Ошибка при обновлении: ' . mysqli_error($conn);
            }
            break;
            
        case 'delete':
            $id = (int)$_POST['page_id'];
            $query = "DELETE FROM pages WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $response['success'] = true;
                $response['message'] = 'Страница удалена';
            } else {
                $response['message'] = 'Ошибка при удалении: ' . mysqli_error($conn);
            }
            break;
            
        case 'add':
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $content = mysqli_real_escape_string($conn, $_POST['content']);
            $slug = mysqli_real_escape_string($conn, $_POST['slug']);
            
            $query = "INSERT INTO pages (title, content, slug) VALUES ('$title', '$content', '$slug')";
            if (mysqli_query($conn, $query)) {
                $response['success'] = true;
                $response['message'] = 'Страница добавлена';
                $response['page_id'] = mysqli_insert_id($conn);
            } else {
                $response['message'] = 'Ошибка при добавлении: ' . mysqli_error($conn);
            }
            break;
            
        case 'toggleUserRole':
            $id = (int)$_POST['user_id'];
            $is_admin = (int)$_POST['is_admin'];
            $new_role = $is_admin ? 0 : 1;
            
            $query = "UPDATE users SET is_admin = $new_role WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $response['success'] = true;
                $response['message'] = 'Роль пользователя обновлена';
                $response['new_role'] = $new_role;
            } else {
                $response['message'] = 'Ошибка при обновлении роли: ' . mysqli_error($conn);
            }
            break;
            
        case 'deleteUser':
            $id = (int)$_POST['user_id'];
            // Проверяем, не удаляет ли админ сам себя
            if ($id == $_SESSION['user_id']) {
                $response['message'] = 'Нельзя удалить свой аккаунт';
                break;
            }
            
            $query = "DELETE FROM users WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $response['success'] = true;
                $response['message'] = 'Пользователь удален';
            } else {
                $response['message'] = 'Ошибка при удалении: ' . mysqli_error($conn);
            }
            break;
            
        case 'deleteMessage':
            $id = (int)$_POST['message_id'];
            $query = "DELETE FROM messages WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $response['success'] = true;
                $response['message'] = 'Сообщение удалено';
            } else {
                $response['message'] = 'Ошибка при удалении сообщения: ' . mysqli_error($conn);
            }
            break;
            
        case 'reply':
            $id = (int)$_POST['message_id'];
            $reply = mysqli_real_escape_string($conn, $_POST['reply']);
            
            $query = "UPDATE messages SET 
                      reply = '$reply',
                      replied_at = CURRENT_TIMESTAMP 
                      WHERE id = $id";
                      
            if (mysqli_query($conn, $query)) {
                $response['success'] = true;
                $response['message'] = 'Ответ отправлен';
            } else {
                $response['message'] = 'Ошибка при отправке ответа: ' . mysqli_error($conn);
            }
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Получение списка страниц
$query = "SELECT * FROM pages ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$pages = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-panel {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .admin-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .admin-form input[type="text"],
        .admin-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .admin-form textarea {
            min-height: 200px;
        }
        .page-item {
            background: #fff;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
            margin-right: 5px;
        }
        .btn-edit {
            background: #2196F3;
        }
        .btn-delete {
            background: #f44336;
        }
        .btn-submit {
            background: #4CAF50;
            padding: 10px 20px;
        }
        .header-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-back {
            background: #666;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover {
            background: #555;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            display: none;
            z-index: 1000;
        }
        .notification.success {
            background: #4CAF50;
        }
        .notification.error {
            background: #f44336;
        }
        .loading {
            opacity: 0.5;
            pointer-events: none;
        }
        .users-list {
            margin-top: 20px;
        }
        .user-item {
            background: #fff;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .user-item h3 {
            margin: 0 0 10px 0;
        }
        .user-actions {
            margin-top: 10px;
        }
        .admin-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .message-item {
            background: #fff;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .message-meta {
            color: #666;
            font-size: 0.9em;
        }
        .message-content {
            margin: 15px 0;
            white-space: pre-line;
        }
        .message-actions {
            text-align: right;
        }
        .message-item {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .sender-info {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .message-content {
            margin: 15px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .message-reply {
            margin-top: 15px;
            padding: 15px;
            background: #f0f7ff;
            border-radius: 4px;
        }
        .reply-meta {
            color: #666;
            font-size: 0.8em;
            margin-top: 5px;
            text-align: right;
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
        }
        .modal-content textarea {
            width: 100%;
            min-height: 150px;
            margin: 15px 0;
            padding: 10px;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="header-panel">
            <h1>Управление страницами</h1>
            <a href="index.php" class="btn btn-back">← Вернуться на сайт</a>
        </div>

        <div class="admin-form">
            <h2>Добавить/Редактировать страницу</h2>
            <form id="pageForm">
                <input type="hidden" name="page_id" id="pageId">
                <input type="hidden" name="action" id="formAction" value="add">
                
                <div>
                    <label>Заголовок:</label>
                    <input type="text" name="title" id="pageTitle" required>
                </div>
                
                <div>
                    <label>URL (slug):</label>
                    <input type="text" name="slug" id="pageSlug" required>
                </div>
                
                <div>
                    <label>Содержание:</label>
                    <textarea name="content" id="pageContent" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-submit">Сохранить</button>
                <button type="button" class="btn" onclick="resetForm()" style="background: #666;">Отмена</button>
            </form>
        </div>

        <div id="pagesList">
            <h2>Существующие страницы</h2>
            <?php foreach ($pages as $page): ?>
                <div class="page-item" data-id="<?php echo $page['id']; ?>">
                    <h3><?php echo htmlspecialchars($page['title']); ?></h3>
                    <p>URL: <?php echo htmlspecialchars($page['slug']); ?></p>
                    <div class="page-actions">
                        <button class="btn btn-edit" onclick="editPage(<?php echo htmlspecialchars(json_encode($page)); ?>)">
                            Редактировать
                        </button>
                        <button class="btn btn-delete" onclick="deletePage(<?php echo $page['id']; ?>)">
                            Удалить
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-section">
            <h2>Управление пользователями</h2>
            <div class="users-list">
                <?php
                // Получение списка пользователей
                $query = "SELECT * FROM users ORDER BY id DESC";
                $result = mysqli_query($conn, $query);
                $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
                
                foreach ($users as $user):
                ?>
                    <div class="user-item" data-id="<?php echo $user['id']; ?>">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                        <p>Роль: <?php echo $user['is_admin'] ? 'Администратор' : 'Пользователь'; ?></p>
                        <div class="user-actions">
                            <button class="btn btn-edit" onclick="toggleUserRole(<?php echo $user['id']; ?>, <?php echo $user['is_admin']; ?>)">
                                <?php echo $user['is_admin'] ? 'Снять админа' : 'Сделать админом'; ?>
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                    Удалить
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="admin-section">
            <h2>Сообщения пользователей</h2>
            <div class="messages-list">
                <?php
                // Получение списка сообщений с информацией об отправителях
                $query = "SELECT m.*, u.username, u.email 
                          FROM messages m 
                          JOIN users u ON m.user_id = u.id 
                          ORDER BY m.created_at DESC";
                $result = mysqli_query($conn, $query);
                $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
                
                if (empty($messages)): ?>
                    <p>Сообщений пока нет</p>
                <?php else:
                    foreach ($messages as $message):
                ?>
                    <div class="message-item" data-id="<?php echo $message['id']; ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                                <div class="sender-info">
                                    <span class="message-meta">
                                        От: <?php echo htmlspecialchars($message['username']); ?> 
                                        (<?php echo htmlspecialchars($message['email']); ?>) | 
                                        Дата: <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                        
                        <?php if ($message['reply']): ?>
                        <div class="message-reply">
                            <h4>Ваш ответ:</h4>
                            <div class="reply-content">
                                <?php echo nl2br(htmlspecialchars($message['reply'])); ?>
                            </div>
                            <div class="reply-meta">
                                Отправлено: <?php echo date('d.m.Y H:i', strtotime($message['replied_at'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="message-actions">
                            <?php if (!$message['reply']): ?>
                                <button class="btn btn-edit" onclick="replyToMessage(<?php echo $message['id']; ?>)">
                                    Ответить
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-delete" onclick="deleteMessage(<?php echo $message['id']; ?>)">
                                Удалить
                            </button>
                        </div>
                    </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
        </div>

        <!-- Добавьте модальное окно для ответа -->
        <div id="replyModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>Ответить на сообщение</h3>
                <form id="replyForm">
                    <input type="hidden" name="message_id" id="replyMessageId">
                    <textarea name="reply" id="replyText" required></textarea>
                    <div class="modal-actions">
                        <button type="submit" class="btn btn-submit">Отправить ответ</button>
                        <button type="button" class="btn" onclick="closeReplyModal()">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="notification" class="notification"></div>

    <script>
        function showNotification(message, isSuccess = true) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification ' + (isSuccess ? 'success' : 'error');
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        function resetForm() {
            document.getElementById('pageForm').reset();
            document.getElementById('pageId').value = '';
            document.getElementById('formAction').value = 'add';
            document.querySelector('.btn-submit').textContent = 'Сохранить';
        }

        function editPage(page) {
            document.getElementById('pageId').value = page.id;
            document.getElementById('pageTitle').value = page.title;
            document.getElementById('pageSlug').value = page.slug;
            document.getElementById('pageContent').value = page.content;
            document.getElementById('formAction').value = 'edit';
            document.querySelector('.btn-submit').textContent = 'Сохранить изменения';
        }

        function deletePage(id) {
            if (!confirm('Вы уверены, что хотите удалить эту страницу?')) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('page_id', id);

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`.page-item[data-id="${id}"]`).remove();
                    showNotification(data.message);
                } else {
                    showNotification(data.message, false);
                }
            });
        }

        document.getElementById('pageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const action = document.getElementById('formAction').value;
            formData.append('action', action);

            this.classList.add('loading');

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.classList.remove('loading');
                if (data.success) {
                    showNotification(data.message);
                    if (action === 'add') {
                        location.reload(); // Перезагрузка для отображения новой страницы
                    }
                    resetForm();
                } else {
                    showNotification(data.message, false);
                }
            });
        });

        function toggleUserRole(id, currentRole) {
            if (!confirm('Вы уверены, что хотите изменить роль этого пользователя?')) return;

            const formData = new FormData();
            formData.append('action', 'toggleUserRole');
            formData.append('user_id', id);
            formData.append('is_admin', currentRole);

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message);
                    location.reload(); // Перезагружаем страницу для обновления списка
                } else {
                    showNotification(data.message, false);
                }
            });
        }

        function deleteUser(id) {
            if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) return;

            const formData = new FormData();
            formData.append('action', 'deleteUser');
            formData.append('user_id', id);

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`.user-item[data-id="${id}"]`).remove();
                    showNotification(data.message);
                } else {
                    showNotification(data.message, false);
                }
            });
        }

        function deleteMessage(id) {
            if (!confirm('Вы уверены, что хотите удалить это сообщение?')) return;

            const formData = new FormData();
            formData.append('action', 'deleteMessage');
            formData.append('message_id', id);

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`.message-item[data-id="${id}"]`).remove();
                    showNotification(data.message);
                } else {
                    showNotification(data.message, false);
                }
            });
        }

        function replyToMessage(id) {
            document.getElementById('replyMessageId').value = id;
            document.getElementById('replyModal').style.display = 'flex';
        }

        function closeReplyModal() {
            document.getElementById('replyModal').style.display = 'none';
            document.getElementById('replyForm').reset();
        }

        document.getElementById('replyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'reply');

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message);
                    closeReplyModal();
                    location.reload();
                } else {
                    showNotification(data.message, false);
                }
            });
        });

        // Закрытие модального окна при клике вне его
        document.getElementById('replyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReplyModal();
            }
        });
    </script>
</body>
</html> 