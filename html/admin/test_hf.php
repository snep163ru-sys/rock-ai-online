<?php
$token = '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';
$ch = curl_init('https://api-inference.huggingface.co/models/facebook/musicgen-small');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", 'Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['inputs' => 'test guitar riff', 'parameters' => ['duration' => 5]]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP: $code\n";
echo "Length: " . strlen($res) . "\n";
echo "First 100: " . substr($res, 0, 100) . "\n";