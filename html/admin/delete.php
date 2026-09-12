<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'db.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $db = new RadioDB();
    $track = $db->getTrack($id);
    
    if ($track) {
        $filePath = MUSIC_PATH . $track['genre'] . '/' . $track['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $db->deleteTrack($id);
    }
}

header('Location: index.php');
exit;
?>
