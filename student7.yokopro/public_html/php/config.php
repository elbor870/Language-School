<?php
// Параметры подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'student7_database');
define('DB_USER', 'student7_dbuser');
define('DB_PASS', 'P@ssw0rd_7');

// Функция подключения к БД через mysqli
function getDBConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Проверка подключения
    if ($mysqli->connect_error) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }
    
    // Установка кодировки
    $mysqli->set_charset("utf8mb4");
    
    return $mysqli;
}
?>