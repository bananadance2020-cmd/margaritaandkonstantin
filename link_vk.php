<?php
header('Content-Type: application/json; charset=utf-8');

// Секретный ключ для защиты от чужих запросов (ДОЛЖЕН СОВПАДАТЬ С КЛЮЧОМ В vk_bot.php)
$secret_link_key = 'super_secret_wedding_key_2024';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Only POST requests allowed']);
    exit;
}

// Читаем JSON из POST-запроса
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['secret_key']) || !isset($data['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
    exit;
}

if ($data['secret_key'] !== $secret_link_key) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid secret key']);
    exit;
}

$vk_user_id = trim($data['user_id']);

// Сохраняем ID в конфигурационный файл
$config_file = __DIR__ . '/vk_config.json';
$config_data = [];
if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true) ?: [];
}

$config_data['vk_user_id'] = $vk_user_id;

if (file_put_contents($config_file, json_encode($config_data, JSON_PRETTY_PRINT))) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied writing to config']);
}
?>
