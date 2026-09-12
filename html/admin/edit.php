<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'db.php';

requireAdmin();

$db = new RadioDB();
$genre = $_GET['genre'] ?? '';

$allGenres = [
    'cyberpunk' => 'Cyberpunk',
    'industrial' => 'Industrial',
    'grunge' => 'Grunge',
    'psychedelic' => 'Psychedelic',
    'progressive' => 'Progressive',
    'punk' => 'Punk Rock',
    'alternative' => 'Alternative',
    'heavy' => 'Heavy Metal',
    'power' => 'Power Metal',
    'numetal' => 'Nu Metal',
    'folk' => 'Folk Rock',
    'glam' => 'Glam Rock'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $title = $_POST['title'] ?? '';
    $artist = $_POST['artist'] ?? '';
    $db->updateTrack($id, $title, $artist);
    header('Location: edit.php?genre=' . $genre);
    exit;
}

$tracks = $db->getTracksByGenre($genre);
$genreName = $allGenres[$genre] ?? $genre;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit - <?php echo $genreName; ?></title>
    <style>
        body { background: #030305; color: #fff; font-family: Arial; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #0a0a10;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .header h1 { color: #00ffcc; }
        .header a { color: #8c8ca3; text-decoration: none; padding: 8px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; }
        .header a:hover { color: #00ffcc; border-color: #00ffcc; }
        .track-item {
            background: #0a0a10;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .track-item form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .track-item input[type="text"] {
            padding: 8px 12px;
            background: #151520;
            border: 1px solid #2a2a3e;
            border-radius: 8px;
            color: #fff;
            flex: 1;
            min-width: 150px;
        }
        .track-item button {
            padding: 8px 16px;
            background: #00ffcc;
            color: #000;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .track-item .file-name { color: #8c8ca3; font-size: 0.8rem; min-width: 100px; }
        .back-btn { background: rgba(255,255,255,0.05); color: #8c8ca3; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
        .back-btn:hover { color: #fff; }
        .empty { color: #8c8ca3; text-align: center; padding: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit <?php echo $genreName; ?></h1>
            <a href="index.php" class="back-btn">Back</a>
        </div>
        
        <?php if (count($tracks) > 0): ?>
            <?php foreach ($tracks as $track): ?>
                <div class="track-item">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $track['id']; ?>">
                        <span class="file-name"><?php echo $track['file_name']; ?></span>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($track['title']); ?>" placeholder="Title">
                        <input type="text" name="artist" value="<?php echo htmlspecialchars($track['artist'] ?? ''); ?>" placeholder="Artist">
                        <button type="submit">Save</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty">No tracks in this genre</div>
        <?php endif; ?>
    </div>
</body>
</html>
