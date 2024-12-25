CREATE DATABASE IF NOT EXISTS cheese;
USE cheese;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    slug VARCHAR(100) UNIQUE,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    subject VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (username, password, email, is_admin) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 1);

INSERT INTO pages (title, content, slug) VALUES 
('Главная', 'Добро пожаловать в мир сыра!', 'home'),
('О нас', 'Мы любим сыр и знаем о нем все!', 'about'),
('Контакты', 'Свяжитесь с нами для заказа сыра', 'contacts');

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255) NOT NULL,
    last_access TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_pages_slug ON pages(slug);
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_logs_user_id ON logs(user_id); 

UPDATE pages SET content = '
<div class="intro">
    <h1>Добро пожаловать в мир изысканных сыров!</h1>
    <p>Мы предлагаем широкий выбор элитных сыров со всего мира.</p>
    
    <!-- Таймер обратного отсчета -->
    <div id="countdown">
        <h2>До следующей акции осталось:</h2>
        <p id="timer"></p>
    </div>

    <!-- Смена изображения -->
    <div id="image-container">
        <h2>Популярные сыры:</h2>
        <img id="cheese-image" src="images/cheese1.jpg" alt="Популярный сыр" style="width: 300px; height: auto;">
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
</div>' 
WHERE slug = 'home';

UPDATE pages SET content = '
<div class="about-us">
    <h2>О нашем магазине</h2>
    <p>Мы специализируемся на продаже элитных сортов сыра с 2010 года.</p>
    <p>Наши преимущества:</p>
    <ul>
        <li>Прямые поставки из Европы</li>
        <li>Строгий контроль качества</li>
        <li>Профессиональное хранение</li>
        <li>Консультации сырных сомелье</li>
    </ul>
</div>' 
WHERE slug = 'about';

UPDATE pages SET content = '
<div class="contacts">
    <h2>Наши контакты</h2>
    <p>Адрес: ул. Сырная, 123</p>
    <p>Телефон: +7 (123) 456-78-90</p>
    <p>Email: info@cheese.com</p>
    
    <div class="working-hours">
        <h3>Часы работы:</h3>
        <p>Пн-Пт: 9:00 - 20:00</p>
        <p>Сб-Вс: 10:00 - 18:00</p>
    </div>
</div>' 
WHERE slug = 'contacts'; 

ALTER TABLE messages ADD COLUMN reply TEXT DEFAULT NULL;
ALTER TABLE messages ADD COLUMN replied_at TIMESTAMP NULL DEFAULT NULL; 