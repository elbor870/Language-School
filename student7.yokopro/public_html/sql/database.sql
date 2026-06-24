-- Использование указанной базы данных
USE student7_database;

-- Таблица курсов
CREATE TABLE IF NOT EXISTS courses (
    id INT(10) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    price FLOAT(7,2) NOT NULL,
    description VARCHAR(150) NOT NULL,
    amount_students INT(50) NOT NULL,
    teacher_fio VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица заявок
CREATE TABLE IF NOT EXISTS applications (
    id INT(10) PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(18) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course_id INT(10) NOT NULL,
    consent TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Вставка 7 записей о курсах (использую записи с номерами 1, 3, 7, 13, 20, 22, 26)
INSERT INTO courses (name, price, description, amount_students, teacher_fio) VALUES
('Excel для чайников', 5000.00, 'Обучение работе с таблицами с нуля до продвинутого уровня', 12, 'Иванова Мария Сергеевна'),
('Python-разработчик', 45000.00, 'Полный цикл обучения от основ до создания веб-приложений', 10, 'Петров Александр Владимирович'),
('Веб-дизайн в Figma', 18000.00, 'Создание макетов сайтов и мобильных приложений', 8, 'Сидорова Анна Игоревна'),
('Немецкий язык с нуля', 20000.00, 'Грамматика, лексика и разговорная речь', 15, 'Шмидт Елена Викторовна'),
('Фронтенд разработка (HTML/CSS/JS)', 35000.00, 'Создание сайтов с нуля до адаптивной верстки', 10, 'Козлов Дмитрий Андреевич'),
('Управление проектами', 27000.00, 'Методологии Agile и Scrum для IT-проектов', 8, 'Морозова Ольга Павловна'),
('Python для Data Science', 50000.00, 'Анализ данных, визуализация и машинное обучение', 6, 'Волков Сергей Николаевич');