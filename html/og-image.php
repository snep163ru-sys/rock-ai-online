<?php
// Создаём картинку 1200x630 через GD с поддержкой кириллицы
header('Content-Type: image/png');

// Создаём изображение
$width = 1200;
$height = 630;
$img = imagecreatetruecolor($width, $height);

// Цвета
$bg = imagecolorallocate($img, 10, 10, 16);        // #0a0a10
$neon = imagecolorallocate($img, 0, 255, 204);      // #00ffcc
$gray = imagecolorallocate($img, 95, 95, 122);      // #5f5f7a
$dark = imagecolorallocate($img, 20, 20, 30);       // #14141e

// Заливка фона
imagefilledrectangle($img, 0, 0, $width, $height, $bg);

// Градиент
for ($i = 0; $i < $height; $i += 2) {
    $color = imagecolorallocate($img, 10 + ($i * 0.005), 10 + ($i * 0.005), 16 + ($i * 0.01));
    imageline($img, 0, $i, $width, $i, $color);
}

// Рамка
imagerectangle($img, 20, 20, $width-20, $height-20, $neon);
imagerectangle($img, 30, 30, $width-30, $height-30, $neon);

// Неоновый круг
imageellipse($img, 200, 315, 180, 180, $neon);
imageellipse($img, 200, 315, 140, 140, $neon);
imageellipse($img, 200, 315, 100, 100, $neon);
imagefilledellipse($img, 200, 315, 25, 25, $neon);

// Рисуем текст (латиницей - она точно работает)
// "NEURA ROCK"
$text = "NEURA ROCK";
$font_size = 5; // 5 = большой шрифт
$x = 380;
$y = 280;
imagestring($img, $font_size, $x, $y, $text, $neon);

// "AI RADIO"
imagestring($img, 4, 460, 320, "AI RADIO", $gray);

// "12 genres • Infinite stream"
imagestring($img, 3, 400, 360, "12 genres • Infinite stream", $gray);

// Декоративные линии
$deco_color = imagecolorallocate($img, 0, 200, 160);
for ($i = 0; $i < 3; $i++) {
    $y = 560 + $i * 10;
    imageline($img, 200, $y, 1000, $y, $deco_color);
}

// Сохраняем
imagepng($img, 'og-image.png', 9);
imagedestroy($img);

echo "✅ Картинка создана: og-image.png\n";
?>
