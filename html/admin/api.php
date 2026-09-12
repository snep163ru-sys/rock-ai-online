<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$musicDir = __DIR__ . '/../music';
$dbFile   = __DIR__ . '/tracks.json';

function loadDB() {
    global $dbFile;
    if (!file_exists($dbFile)) return [];
    $data = json_decode(file_get_contents($dbFile), true);
    return is_array($data) ? $data : [];
}

function saveDB($data) {
    global $dbFile;
    file_put_contents($dbFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function scanGenreDir($genre) {
    global $musicDir;
    $path = "$musicDir/$genre";
    if (!is_dir($path)) return [];
    $files = glob("$path/*.mp3");
    $tracks = [];
    foreach ($files as $f) {
        $tracks[] = [
            'url'    => "/music/$genre/" . basename($f),
            'title'  => pathinfo(basename($f), PATHINFO_FILENAME),
            'artist' => $genre,
        ];
    }
    return $tracks;
}

$action = $_GET['action'] ?? 'list';
$genre  = $_GET['genre'] ?? '';

if ($action === 'list' || $action === '') {
    $db = loadDB();
    if ($genre && $genre !== 'all') {
        $filtered = array_values(array_filter($db, fn($t) => $t['artist'] === $genre));
        if (empty($filtered)) {
            $filtered = scanGenreDir($genre);
        }
        echo json_encode($filtered, JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (empty($db)) {
        $genres = ['cyberpunk','industrial','grunge','psychedelic','progressive','punk','alternative','heavy','power','numetal','folk','glam'];
        foreach ($genres as $g) {
            $db = array_merge($db, scanGenreDir($g));
        }
    }
    echo json_encode($db, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $url    = $_POST['url'] ?? '';
    $title  = $_POST['title'] ?? 'Untitled';
    $artist = $_POST['artist'] ?? 'unknown';
    if (!$url) {
        http_response_code(400);
        echo json_encode(['error' => 'url is required']);
        exit;
    }
    $db = loadDB();
    foreach ($db as $t) {
        if ($t['url'] === $url) {
            echo json_encode(['ok' => true, 'message' => 'already exists']);
            exit;
        }
    }
    $db[] = ['url' => $url, 'title' => $title, 'artist' => $artist];
    saveDB($db);
    echo json_encode(['ok' => true, 'total' => count($db)]);
    exit;
}

if ($action === 'upload-file' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'file is required']);
        exit;
    }
    $genre = $_POST['genre'] ?? 'grunge';
    $file  = $_FILES['file'];
    $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'mp3') {
        http_response_code(400);
        echo json_encode(['error' => 'only mp3 allowed']);
        exit;
    }
    $targetDir = $musicDir . '/' . $genre;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.mp3';
    $dest = $targetDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500);
        echo json_encode(['error' => 'upload failed']);
        exit;
    }
    $title = $_POST['title'] ?? pathinfo($file['name'], PATHINFO_FILENAME);
    $db = loadDB();
    $entry = ['url' => "/music/$genre/$filename", 'title' => $title, 'artist' => $genre];
    $db[] = $entry;
    saveDB($db);
    echo json_encode(['ok' => true, 'track' => $entry]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'unknown action']);