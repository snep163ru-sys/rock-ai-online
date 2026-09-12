<?php
$img = imagecreatetruecolor(1200, 630);
$bg = imagecolorallocate($img, 10, 10, 16);
$neon = imagecolorallocate($img, 0, 255, 204);
$gray = imagecolorallocate($img, 95, 95, 122);

imagefilledrectangle($img, 0, 0, 1200, 630, $bg);
imagerectangle($img, 20, 20, 1180, 610, $neon);
imageellipse($img, 200, 315, 180, 180, $neon);
imagefilledellipse($img, 200, 315, 25, 25, $neon);
imagestring($img, 5, 380, 260, 'NEURA ROCK', $neon);
imagestring($img, 4, 440, 310, 'AI ROCK RADIO', $gray);

imagepng($img, 'og-image.png');
imagedestroy($img);
echo "✅ PNG created!\n";
?>
