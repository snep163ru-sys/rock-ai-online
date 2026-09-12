<?php
/**
 * generate.php — авто-генератор музыки для NEURA ROCK
 * Использует Hugging Face Inference API (модель MusicGen)
 * 
 * Вызов:   php generate.php [genre]
 * Пример:  php generate.php industrial
 * По crontab: 0 каждые 6 часов
 * 
 * Требует: PHP 8+, curl, allow_url_fopen
 */

// ======================== НАСТРОЙКИ ========================

// Hugging Face токен — получи на https://huggingface.co/settings/tokens
define('HF_TOKEN', getenv('HF_TOKEN') ?: '');

// Модель: facebook/musicgen-small | facebook/musicgen-medium | facebook/musicgen-large
define('HF_MODEL', 'facebook/musicgen-small');

// URL твоего радио (для upload)
define('SITE_URL', 'https://rock-ai.online');

// Путь к корню сайта на сервере (где лежат /music, /admin)
define('SITE_ROOT', __DIR__ . '/..');

// Максимум треков в одной папке жанра (больше — не генерим)
define('MAX_TRACKS_PER_GENRE', 100);

// ======================== ЛОГИКА ========================

$allGenres = ['cyberpunk','industrial','grunge','psychedelic','progressive',
              'punk','alternative','heavy','power','numetal','folk','glam'];

// Какой жанр генерировать
$genre = $argv[1] ?? '';
if (!$genre || !in_array($genre, $allGenres)) {
    echo "Использование: php generate.php <жанр>\n";
    echo "Жанры: " . implode(', ', $allGenres) . "\n";
    exit(1);
}

// Загружаем промты
$prompts = json_decode(file_get_contents(__DIR__ . '/prompts.json'), true);
if (!$prompts || !isset($prompts[$genre])) {
    echo "Ошибка: нет промтов для жанра '$genre'\n";
    exit(1);
}

// Выбираем случайный промт
$prompt = $prompts[$genre][array_rand($prompts[$genre])];
echo "[$genre] Промт: $prompt\n";

// Проверяем количество треков
$musicDir = SITE_ROOT . "/music/$genre";
if (!is_dir($musicDir)) mkdir($musicDir, 0755, true);

$existing = glob("$musicDir/*.mp3");
if (count($existing) >= MAX_TRACKS_PER_GENRE) {
    echo "[$genre] Достигнут лимит ({MAX_TRACKS_PER_GENRE}), пропускаем\n";
    exit(0);
}

// Генерируем через Hugging Face
echo "[$genre] Отправляем запрос в Hugging Face...\n";

$promptData = json_encode([
    'inputs' => $prompt,
    'parameters' => ['duration' => 15],
]);

// Пробуем разные endpoints (router.huggingface.co работает, api-inference нет из-за DNS)
$endpoints = [
    'https://router.huggingface.co/hf-inference/models/' . HF_MODEL,
    'https://api-inference.huggingface.co/models/' . HF_MODEL,
];

$response = null;
$httpCode = 0;

foreach ($endpoints as $url) {
    echo "[$genre] Пробуем: $url\n";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . HF_TOKEN,
            'Content-Type: application/json',
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $promptData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "[$genre] Ответ: HTTP $httpCode\n";
    if ($httpCode === 200) break;
    if ($httpCode === 503 || $httpCode === 500) {
        echo "[$genre] Модель загружается (503), пробуем следующий...\n";
        sleep(3);
        continue;
    }
}

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "[$genre] Ошибка HF API (HTTP $httpCode): " . mb_substr($response, 0, 200) . "\n";
    exit(1);
}

// Сохраняем mp3
$filename = time() . '_' . bin2hex(random_bytes(4)) . '.mp3';
$filepath = "$musicDir/$filename";

$saved = file_put_contents($filepath, $response);
if (!$saved) {
    echo "[$genre] Ошибка сохранения файла\n";
    exit(1);
}

// Генерируем название трека из промта (первые слова)
$words = explode(' ', $prompt);
$titleWords = array_slice($words, 0, rand(2, 4));
$title = implode(' ', $titleWords);
$title = ucwords(mb_strtolower($title));

echo "[$genre] Трек сохранён: $filename\n";
echo "[$genre] Название: $title\n";

// Регистрируем в БД через API
$registerUrl = SITE_URL . '/admin/api.php?action=upload';
$postData = [
    'url'  => "/music/$genre/$filename",
    'title' => $title,
    'artist' => $genre,
];

$ch2 = curl_init($registerUrl);
curl_setopt_array($ch2, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
$regResult = curl_exec($ch2);
$regHttp = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

if ($regHttp === 200) {
    echo "[$genre] Трек зарегистрирован в базе\n";
} else {
    echo "[$genre] Предупреждение: не удалось зарегистрировать ($regHttp): $regResult\n";
    // Файл уже сохранён, радио подхватит
}

echo "[$genre] Готово!\n";