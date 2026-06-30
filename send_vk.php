<?php
// send_vk.php
// Скрипт для отправки данных формы в личные сообщения ВКонтакте

header('Content-Type: application/json; charset=utf-8');

// ==========================================
// НАСТРОЙКИ (заполняет клиент)
// ==========================================

// Читаем ID клиента из конфигурационного файла
$config_file = __DIR__ . '/vk_config.json';
$vk_user_id = '';
if (file_exists($config_file)) {
    $config_data = json_decode(file_get_contents($config_file), true);
    if (isset($config_data['vk_user_id'])) {
        $vk_user_id = $config_data['vk_user_id'];
    }
}

// ==========================================
// НАСТРОЙКИ БОТА (не трогать)
// ==========================================
$vk_token = 'vk1.a.GZqjYnIiyHtMKq7UfWz3-SzU5KabyxA40z0cu-FHiQ7_wxHTl5rSXRwm0IcLR2gk0ebpDhmZNsoIcDTIvMAcHJL1EOAJB87HSIjUdqpmdO7_BK2UR5wNfVHI1D2EmcSJs-Q_tolKJI41OwPubAGcyUc5HGcRewdp8kq0fD67OvxsW4PC4ICijUiolvzRZPdluCT1jKsEMn0AbGI3VbPEXQ'; // Токен вашей единой группы ВК
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем имя сайта, с которого пришла заявка
    $site_name = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'вашего сайта';

    // Формируем текст сообщения
    $messageText = "🔔 Новая анкета с сайта {$site_name}!\n\n";

    // Собираем все поля из формы
    foreach ($_POST as $key => $value) {
        if (!empty($value)) {
            // Если передано несколько значений (например, несколько галочек)
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            
            // Пропускаем служебные поля, если они есть
            if (in_array($key, ['form-id', 'formname'])) {
                continue;
            }
            
            // Форматируем сообщение
            $messageText .= "▪ {$key}: {$value}\n";
        }
    }

    $params = [
        'access_token' => $vk_token,
        'user_id'      => $vk_user_id,
        'random_id'    => rand(1, 10000000),
        'message'      => $messageText,
        'v'            => '5.131'
    ];

    $url = 'https://api.vk.com/method/messages.send';
    
    // Используем cURL для отправки запроса к API ВКонтакте
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . $error]);
    } else {
        echo json_encode(['status' => 'success', 'vk_response' => json_decode($result, true)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);
}
