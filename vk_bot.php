<?php

// Данные вашего центрального бота (настройте их после создания группы)
$confirmation_token = 'af39953e'; // Строка, которую нужно вернуть серверу ВК
$vk_token = 'vk1.a.GZqjYnIiyHtMKq7UfWz3-SzU5KabyxA40z0cu-FHiQ7_wxHTl5rSXRwm0IcLR2gk0ebpDhmZNsoIcDTIvMAcHJL1EOAJB87HSIjUdqpmdO7_BK2UR5wNfVHI1D2EmcSJs-Q_tolKJI41OwPubAGcyUc5HGcRewdp8kq0fD67OvxsW4PC4ICijUiolvzRZPdluCT1jKsEMn0AbGI3VbPEXQ';         // Токен группы для отправки сообщений

// Получаем и декодируем JSON-данные от ВКонтакте
$data = json_decode(file_get_contents('php://input'));

// Проверяем, что данные получены
if (!$data) {
    echo "This is a VK Bot Webhook endpoint.";
    exit;
}

// Проверяем тип события
switch ($data->type) {
    
    // Подтверждение адреса сервера
    case 'confirmation':
        echo $confirmation_token;
        break;
        
    // Получение нового сообщения
    case 'message_new':
        // Извлекаем ID и текст сообщения
        $user_id = $data->object->message->from_id;
        $text = $data->object->message->text;
        
        $secret_link_key = 'super_secret_wedding_key_2024';
        
        // Проверяем, содержит ли сообщение ссылку
        if (preg_match('/(https?:\/\/[^\s]+)/i', $text, $matches)) {
            $url = rtrim($matches[1], '/'); // Удаляем слеш на конце если есть
            $target_endpoint = $url . '/link_vk.php';
            
            // Отправляем POST запрос на сайт клиента
            $post_data = json_encode([
                'secret_key' => $secret_link_key,
                'user_id' => $user_id
            ]);
            
            $ch = curl_init($target_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Ждем максимум 5 секунд
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Анализируем ответ от сайта
            if ($http_code == 200) {
                $response_data = json_decode($response, true);
                if ($response_data && isset($response_data['status']) && $response_data['status'] === 'success') {
                    $message_text = "✅ Отлично! Ваш сайт {$url} успешно привязан.\nТеперь все заполненные анкеты будут приходить вам сюда!";
                } else {
                    $message_text = "❌ Сайт ответил, но возникла ошибка при сохранении (Неверный ключ или нет прав на запись).";
                }
            } else {
                $message_text = "❌ Не удалось связаться с сайтом {$url} (Ошибка {$http_code}). Убедитесь, что ссылка верная и сайт работает.";
            }
        } else {
            // Если ссылки нет, отправляем инструкцию
            $message_text = "Привет! 👋\n\nЧтобы получать анкеты гостей сюда, просто отправьте мне ссылку на ваш готовый сайт!\n\nНапример:\nhttps://ivan-i-maria.ru";
        }
        
        // Отправляем сообщение обратно пользователю
        $request_params = array(
            'message' => $message_text,
            'peer_id' => $user_id,
            'access_token' => $vk_token,
            'v' => '5.131',
            'random_id' => mt_rand() // Уникальный идентификатор для защиты от дублей
        );
        
        $get_params = http_build_query($request_params);
        $result = @file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);
        
        // Логируем ответ для отладки
        file_put_contents('bot_log.txt', date('Y-m-d H:i:s') . " | User: $user_id | Response: $result\n", FILE_APPEND);
        
        // Возвращаем "ok", чтобы сервер ВКонтакте понял, что сообщение обработано
        echo 'ok';
        break;
        
    default:
        // Для всех остальных событий просто возвращаем "ok"
        file_put_contents('bot_log.txt', date('Y-m-d H:i:s') . " | Other event: " . $data->type . "\n", FILE_APPEND);
        echo 'ok';
        break;
}

// Запишем весь входящий JSON если это сообщение (на случай, если структура другая)
if (isset($data->type) && $data->type === 'message_new') {
    file_put_contents('bot_log.txt', date('Y-m-d H:i:s') . " | INCOMING: " . file_get_contents('php://input') . "\n", FILE_APPEND);
}

?>
