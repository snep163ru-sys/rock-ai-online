<?php
// Test via HF router proxy
$token = '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';
$url   = 'https://router.huggingface.co/hf-inference/models/facebook/musicgen-small';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        'Content-Type: application/json',
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'inputs' => 'test electric guitar riff industrial rock',
        'parameters' => ['duration' => 10],
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
]);

$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP: $code\n";
echo "Error: " . ($err ?: 'none') . "\n";
echo "Primary IP: " . ($info['primary_ip'] ?? '?') . "\n";
echo "Length: " . strlen($res) . "\n";
if (strlen($res) > 5) {
    $path = '/var/www/rock-ai.online/html/music/industrial/hf_router_' . time() . '.mp3';
    file_put_contents($path, $res);
    echo "Saved to: $path\n";
} else {
    echo "Response preview: " . substr($res, 0, 200) . "\n";
}