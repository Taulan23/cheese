<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'login') {
            // Обработка входа
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $password = $_POST['password'];
            
            $query = "SELECT * FROM users WHERE username = '$username'";
            $result = mysqli_query($conn, $query);
            
            if ($user = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Неверный пароль';
                }
            } else {
                $error = 'Пользователь не найден';
            }
        } elseif ($_POST['action'] == 'register') {
            // Обработка регистрации
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Проверка паролей
            if ($password !== $confirm_password) {
                $error = 'Пароли не совпадают';
            } else {
                // Проверка существования пользователя
                $query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    $error = 'Пользователь с таким именем или email уже существует';
                } else {
                    // Создание нового пользователя
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
                    
                    if (mysqli_query($conn, $query)) {
                        $success = 'Регистрация успешна! Теперь вы можете войти.';
                    } else {
                        $error = 'Ошибка при регистрации: ' . mysqli_error($conn);
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход/Регистрация</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-toggle {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-toggle button {
            background: none;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            color: #666;
        }
        .form-toggle button.active {
            color: #333;
            border-bottom: 2px solid #ffcc00;
        }
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="form-toggle">
            <button onclick="showForm('login')" id="loginBtn" class="active">Вход</button>
            <button onclick="showForm('register')" id="registerBtn">Регистрация</button>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Форма входа -->
        <form method="POST" class="auth-form active" id="loginForm">
            <input type="hidden" name="action" value="login">
            <div>
                <label>Имя пользователя:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Войти</button>
        </form>

        <!-- Форма регистрации -->
        <form method="POST" class="auth-form" id="registerForm">
            <input type="hidden" name="action" value="register">
            <div>
                <label>Имя пользователя:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label>Подтвердите пароль:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>

    <script>
        function showForm(formType) {
            // Переключение активной кнопки
            document.getElementById('loginBtn').classList.toggle('active', formType === 'login');
            document.getElementById('registerBtn').classList.toggle('active', formType === 'register');
            
            // Переключение активной формы
            document.getElementById('loginForm').classList.toggle('active', formType === 'login');
            document.getElementById('registerForm').classList.toggle('active', formType === 'register');
        }

        // Если есть сообщение об успешной регистрации, показываем форму входа
        <?php if (isset($success)): ?>
            showForm('login');
        <?php endif; ?>
    </script>
</body>
</html> 