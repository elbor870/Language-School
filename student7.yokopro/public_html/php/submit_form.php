<?php
// Отключаем вывод ошибок в браузер
error_reporting(0);
ini_set('display_errors', 0);

// Устанавливаем заголовок JSON
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный метод запроса'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Получаем данные из формы
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$course_id = intval($_POST['course_id'] ?? 0);
$consent = isset($_POST['consent']) ? 1 : 0;

// Валидация данных
$errors = [];

if (empty($full_name)) {
    $errors[] = 'Введите ФИО';
} elseif (strlen($full_name) < 5) {
    $errors[] = 'ФИО должно содержать минимум 5 символов';
}

if (empty($phone)) {
    $errors[] = 'Введите телефон';
} elseif (!preg_match('/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/', $phone)) {
    $errors[] = 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX';
}

if (empty($email)) {
    $errors[] = 'Введите email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Введите корректный email';
}

if ($course_id <= 0) {
    $errors[] = 'Выберите курс';
}

if (!$consent) {
    $errors[] = 'Необходимо дать согласие на обработку персональных данных';
}

// Если есть ошибки, возвращаем их
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Подключаемся к БД
$mysqli = getDBConnection();

// Проверяем существование таблицы applications
$mysqli->query("CREATE TABLE IF NOT EXISTS applications (
    id INT(10) PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(18) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course_id INT(10) NOT NULL,
    consent TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Проверяем существование курса
$check_course = $mysqli->prepare("SELECT id FROM courses WHERE id = ?");
$check_course->bind_param("i", $course_id);
$check_course->execute();
$check_result = $check_course->get_result();

if ($check_result->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'errors' => ['Выбранный курс не найден']
    ], JSON_UNESCAPED_UNICODE);
    $check_course->close();
    $mysqli->close();
    exit;
}
$check_course->close();

// Подготавливаем и выполняем вставку
$stmt = $mysqli->prepare("INSERT INTO applications (full_name, phone, email, course_id, consent) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssii", $full_name, $phone, $email, $course_id, $consent);

if ($stmt->execute()) {
    $insert_id = $mysqli->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.',
        'id' => $insert_id
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'errors' => ['Ошибка при сохранении заявки: ' . $stmt->error]
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$mysqli->close();
?>