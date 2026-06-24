<?php
// Включаем показ ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Проверка базы данных</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px; }
    </style>
</head>
<body>
<h1>Проверка базы данных</h1>";

require_once 'config.php';

try {
    $mysqli = getDBConnection();
    echo "<p class='success'>✓ Подключение к базе данных успешно!</p>";
    
    // Информация о подключении
    echo "<div class='section'>";
    echo "<h2>Информация о подключении:</h2>";
    echo "<p>Хост: " . DB_HOST . "</p>";
    echo "<p>База данных: " . DB_NAME . "</p>";
    echo "<p>Пользователь: " . DB_USER . "</p>";
    echo "<p>Версия MySQL: " . $mysqli->server_info . "</p>";
    echo "</div>";
    
    // Проверка таблиц
    echo "<div class='section'>";
    echo "<h2>Таблицы в базе данных:</h2>";
    $result = $mysqli->query("SHOW TABLES");
    
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠ Таблиц нет. Создаем...</p>";
        
        // Создание таблицы courses
        $mysqli->query("CREATE TABLE IF NOT EXISTS courses (
            id INT(10) PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            price FLOAT(7,2) NOT NULL,
            description VARCHAR(150) NOT NULL,
            amount_students INT(50) NOT NULL,
            teacher_fio VARCHAR(150) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Создание таблицы applications
        $mysqli->query("CREATE TABLE IF NOT EXISTS applications (
            id INT(10) PRIMARY KEY AUTO_INCREMENT,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(18) NOT NULL,
            email VARCHAR(100) NOT NULL,
            course_id INT(10) NOT NULL,
            consent TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        echo "<p class='success'>✓ Таблицы созданы!</p>";
    }
    echo "</div>";
    
    // Проверка курсов
    echo "<div class='section'>";
    echo "<h2>Курсы:</h2>";
    $result = $mysqli->query("SELECT * FROM courses");
    
    if ($result->num_rows > 0) {
        echo "<p>Найдено курсов: " . $result->num_rows . "</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Название</th><th>Цена</th><th>Описание</th><th>Мест в группе</th><th>Преподаватель</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . number_format($row['price'], 0, '.', ' ') . " ₽</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td>" . $row['amount_students'] . "</td>";
            echo "<td>" . htmlspecialchars($row['teacher_fio']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠ Курсов нет. Добавляем демо-данные...</p>";
        
        $mysqli->query("INSERT INTO courses (name, price, description, amount_students, teacher_fio) VALUES
        ('Excel для чайников', 5000.00, 'Обучение работе с таблицами с нуля до продвинутого уровня', 12, 'Иванова Мария Сергеевна'),
        ('Python-разработчик', 45000.00, 'Полный цикл обучения от основ до создания веб-приложений', 10, 'Петров Александр Владимирович'),
        ('Веб-дизайн в Figma', 18000.00, 'Создание макетов сайтов и мобильных приложений', 8, 'Сидорова Анна Игоревна'),
        ('Немецкий язык с нуля', 20000.00, 'Грамматика, лексика и разговорная речь', 15, 'Шмидт Елена Викторовна'),
        ('Фронтенд разработка (HTML/CSS/JS)', 35000.00, 'Создание сайтов с нуля до адаптивной верстки', 10, 'Козлов Дмитрий Андреевич'),
        ('Управление проектами', 27000.00, 'Методологии Agile и Scrum для IT-проектов', 8, 'Морозова Ольга Павловна'),
        ('Python для Data Science', 50000.00, 'Анализ данных, визуализация и машинное обучение', 6, 'Волков Сергей Николаевич')");
        
        echo "<p class='success'>✓ Добавлено 7 курсов!</p>";
    }
    echo "</div>";
    
    // Проверка заявок
    echo "<div class='section'>";
    echo "<h2>Заявки:</h2>";
    $result = $mysqli->query("SELECT * FROM applications ORDER BY created_at DESC");
    
    if ($result->num_rows > 0) {
        echo "<p>Найдено заявок: " . $result->num_rows . "</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>Email</th><th>Курс ID</th><th>Дата создания</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . $row['course_id'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠ Заявок пока нет.</p>";
    }
    echo "</div>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Ошибка: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>