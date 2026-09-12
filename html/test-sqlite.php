<?php
// Проверка SQLite
if (class_exists('PDO')) {
    echo "PDO установлен<br>";
    
    if (in_array('sqlite', PDO::getAvailableDrivers())) {
        echo "✅ SQLite драйвер установлен!<br>";
    } else {
        echo "❌ SQLite драйвер НЕ установлен<br>";
        echo "Доступные драйверы: " . implode(', ', PDO::getAvailableDrivers());
    }
} else {
    echo "❌ PDO не установлен";
}

// Проверяем создание базы
try {
    $db = new PDO('sqlite:/var/www/rock-ai.online/html/data/test.db');
    echo "✅ База данных создана!";
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>
