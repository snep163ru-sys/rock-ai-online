<?php
// Fix: если не резолвится DNS, используем IP напрямую
$token = getenv('HF_TOKEN') ?: '';
$host  = 'api-inference.huggingface.co';
$ip    = '18.244.164.92';

// Используем IP напрямую, но с правильным Host header
$url = "https://$ip/models/facebook/musicgen-small";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        'Content-Type: application/json',
        "Host: $host",
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'inputs' => 'test electric guitar riff industrial rock',
        'parameters' => ['duration' => 10],
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

echo "HTTP: $code\n";
if ($err) echo "Error: $err\n";
echo "Length: " . strlen($res) . "\n";
if (strlen($res) > 0) {
    $path = '/var/www/rock-ai.online/html/music/industrial/hf_test_' . time() . '.mp3';
    file_put_contents($path, $res);
    echo "Saved to: $path\n";
} else {
    echo "No data received\n";
    echo "First 200: " . substr($res, 0, 200) . "\n";
}