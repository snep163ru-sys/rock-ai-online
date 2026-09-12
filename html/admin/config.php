<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

define('DB_PATH', __DIR__ . '/../data/radio.db');
define('MUSIC_PATH', __DIR__ . '/../music/');
define('ADMIN_USER', 'Snep163ru');
define('ADMIN_PASS_HASH', '$2y$10$yTkhvFyLamY1veZtzMECouqDuM7tl1bsJJJaGBKIguJCRY67czhN.');

$genres = ['cyberpunk', 'industrial', 'grunge', 'psychedelic', 'progressive', 'punk', 'alternative', 'heavy', 'power', 'numetal', 'folk', 'glam'];
foreach ($genres as $genre) {
    $path = MUSIC_PATH . $genre;
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}
?>
