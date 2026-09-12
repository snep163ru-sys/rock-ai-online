<?php
// Создаём красивый логотип 1200x630
$width = 1200;
$height = 630;

// Создаём изображение
$img = imagecreatetruecolor($width, $height);

// Цвета
$bg = imagecolorallocate($img, 10, 10, 16);        // #0a0a10
$neon = imagecolorallocate($img, 0, 255, 204);      // #00ffcc
$neon_dim = imagecolorallocate($img, 0, 200, 160);  // #00c8a0
$gray = imagecolorallocate($img, 95, 95, 122);      // #5f5f7a
$dark = imagecolorallocate($img, 20, 20, 30);       // #14141e

// Заливка фона
imagefilledrectangle($img, 0, 0, $width, $height, $bg);

// Создаём градиентный фон
for ($i = 0; $i < $height; $i += 2) {
    $r = 10 + ($i * 0.01);
    $g = 10 + ($i * 0.01);
    $b = 16 + ($i * 0.015);
    $color = imagecolorallocate($img, $r, $g, $b);
    imageline($img, 0, $i, $width, $i, $color);
}

// Рисуем неоновые круги (логотип)
imageellipse($img, 200, 315, 200, 200, $neon);
imageellipse($img, 200, 315, 160, 160, $neon);
imageellipse($img, 200, 315, 120, 120, $neon);
imagefilledellipse($img, 200, 315, 25, 25, $neon);

// Рисуем букву "N" в круге (простой способ)
imagestring($img, 5, 185, 300, 'N', $neon);

// Рисуем текст с помощью imagettftext (поддержка кириллицы)
// Скачиваем шрифт если нет
$font_url = 'https://github.com/google/fonts/raw/main/ofl/orbitron/Orbitron%5Bwght%5D.ttf';
$font_file = 'orbitron.ttf';

if (!file_exists($font_file)) {
    echo "Скачиваем шрифт...\n";
    file_put_contents($font_file, file_get_contents($font_url));
}

// Используем встроенный шрифт если TTF не работает
if (function_exists('imagettftext') && file_exists($font_file)) {
    // TTF шрифт - красиво
    $font_size = 64;
    $text = "NEURA ROCK";
    $bbox = imagettfbbox($font_size, 0, $font_file, $text);
    $x = 380;
    $y = 280;
    imagettftext($img, $font_size, 0, $x, $y, $neon, $font_file, $text);
    
    $font_size = 28;
    $text = "AI РАДИОСТАНЦИЯ";
    $bbox = imagettfbbox($font_size, 0, $font_file, $text);
    $x = 440;
    $y = 330;
    imagettftext($img, $font_size, 0, $x, $y, $gray, $font_file, $text);
    
    $font_size = 20;
    $text = "12 ЖАНРОВ • БЕСКОНЕЧНЫЙ ЭФИР";
    $bbox = imagettfbbox($font_size, 0, $font_file, $text);
    $x = 400;
    $y = 370;
    imagettftext($img, $font_size, 0, $x, $y, $gray, $font_file, $text);
} else {
    // Запасной вариант - простой текст
    imagestring($img, 5, 380, 270, "NEURA ROCK", $neon);
    imagestring($img, 4, 430, 320, "AI RADIO", $gray);
    imagestring($img, 3, 400, 360, "12 GENRES • INFINITE STREAM", $gray);
}

// Декоративные линии внизу
for ($i = 0; $i < 3; $i++) {
    $y = 560 + $i * 12;
    imageline($img, 200, $y, 1000, $y, $neon_dim);
}

// Сохраняем
imagepng($img, 'og-image.png', 9);
imagedestroy($img);

echo "✅ Логотип создан: og-image.png\n";
echo "Проверьте: https://rock-ai.online/og-image.png\n";
?>
