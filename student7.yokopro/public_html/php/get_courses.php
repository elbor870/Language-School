<?php
// Отключаем вывод ошибок в браузер
error_reporting(0);
ini_set('display_errors', 0);

// Устанавливаем заголовок JSON
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// Получаем курсы из БД
$mysqli = getDBConnection();

$query = "SELECT * FROM courses ORDER BY id ASC";
$result = $mysqli->query($query);

if ($result) {
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    echo json_encode($courses, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'error' => 'Ошибка получения курсов: ' . $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
}

$mysqli->close();
?>