<?php
// Включаем показ ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 0); // Не показывать ошибки в браузер

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// Логирование для отладки
$log_file = __DIR__ . '/debug.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Начало обработки\n", FILE_APPEND);
file_put_contents($log_file, "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Только POST запросы']);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$course_id = intval($_POST['course_id'] ?? 0);
$consent = ($_POST['consent'] ?? '') === '1' ? 1 : 0;

// Простая валидация
if (empty($full_name) || empty($phone) || empty($email) || $course_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Заполните все поля'
    ]);
    exit;
}

try {
    $mysqli = getDBConnection();
    
    // Создаем таблицу если нет
    $mysqli->query("CREATE TABLE IF NOT EXISTS applications (
        id INT(10) PRIMARY KEY AUTO_INCREMENT,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(18) NOT NULL,
        email VARCHAR(100) NOT NULL,
        course_id INT(10) NOT NULL,
        consent TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Вставляем данные
    $stmt = $mysqli->prepare("INSERT INTO applications (full_name, phone, email, course_id, consent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $full_name, $phone, $email, $course_id, $consent);
    
    if ($stmt->execute()) {
        $insert_id = $mysqli->insert_id;
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Успех! ID: $insert_id\n", FILE_APPEND);
        
        echo json_encode([
            'success' => true,
            'message' => 'Заявка сохранена',
            'id' => $insert_id
        ]);
    } else {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Ошибка: " . $stmt->error . "\n", FILE_APPEND);
        
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка сохранения: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
    $mysqli->close();
    
} catch (Exception $e) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Исключение: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка: ' . $e->getMessage()
    ]);
}

file_put_contents($log_file, date('Y-m-d H:i:s') . " - Конец обработки\n\n", FILE_APPEND);
?>