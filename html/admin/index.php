<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'db.php';

requireAdmin();

$db = new RadioDB();

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

$uploadResult = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $genre = $_POST['genre'] ?? '';
    $title = $_POST['title'] ?? '';
    $artist = $_POST['artist'] ?? '';
    
    if (empty($genre)) {
        $uploadResult = 'Select genre!';
    } elseif (empty($title)) {
        $uploadResult = 'Enter track title!';
    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $uploadResult = 'Upload error! Code: ' . $_FILES['file']['error'];
    } else {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'mp3') {
            $uploadResult = 'Only MP3 files!';
        } else {
            $genrePath = MUSIC_PATH . $genre;
            if (!file_exists($genrePath)) {
                mkdir($genrePath, 0777, true);
            }
            
            $fileName = time() . '.mp3';
            $targetPath = $genrePath . '/' . $fileName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $db->addTrack($genre, $fileName, $title, $artist);
                $uploadResult = 'Upload successful!';
            } else {
                $uploadResult = 'Save error! Check folder permissions.';
            }
        }
    }
}

if (isset($_GET['sync'])) {
    $genre = $_GET['sync'];
    $path = MUSIC_PATH . $genre . '/';
    
    if (file_exists($path)) {
        $files = glob($path . '*.mp3');
        $count = 0;
        foreach ($files as $file) {
            $fileName = basename($file);
            $title = str_replace(['_', '-', '.mp3'], ' ', $fileName);
            $db->addTrack($genre, $fileName, $title, $genre);
            $count++;
        }
        header('Location: index.php?synced=' . $genre . '&count=' . $count);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NEURA ADMIN</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #030305; color: #fff; font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #0a0a10;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .header h1 { color: #00ffcc; font-size: 1.8rem; }
        .header a { color: #8c8ca3; text-decoration: none; padding: 8px 16px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; }
        .header a:hover { color: #00ffcc; border-color: #00ffcc; }
        .upload-form {
            background: #0a0a10;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .upload-form h3 { color: #00ffcc; margin-bottom: 15px; }
        .upload-form form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .upload-form select, .upload-form input[type="text"], .upload-form input[type="file"] {
            padding: 10px;
            background: #151520;
            border: 1px solid #2a2a3e;
            border-radius: 10px;
            color: #fff;
            font-size: 0.9rem;
        }
        .upload-form select { min-width: 120px; }
        .upload-form input[type="text"] { min-width: 150px; flex: 1; }
        .upload-form input[type="file"] { background: transparent; border: none; padding: 10px 0; }
        .upload-form button {
            padding: 10px 25px;
            background: #00ffcc;
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }
        .upload-form button:hover { transform: scale(1.02); }
        .upload-result { padding: 10px 15px; border-radius: 10px; margin-top: 10px; }
        .upload-result.success { background: rgba(0,255,204,0.1); color: #00ffcc; border: 1px solid #00ffcc; }
        .upload-result.error { background: rgba(255,0,0,0.1); color: #ff3333; border: 1px solid #ff3333; }
        .genre-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .genre-card {
            background: #0a0a10;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .genre-card h3 { color: #00ffcc; margin-bottom: 10px; font-size: 1.2rem; }
        .genre-card .count { color: #8c8ca3; font-size: 0.9rem; margin-bottom: 15px; }
        .genre-card .actions { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .genre-card .actions a {
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            background: rgba(255,255,255,0.05);
            color: #8c8ca3;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .genre-card .actions a:hover { background: #00ffcc; color: #000; }
        .genre-card .tracks { max-height: 200px; overflow-y: auto; }
        .genre-card .track-item {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }
        .genre-card .track-item .title { color: #fff; }
        .genre-card .track-item .delete { color: #ff3333; cursor: pointer; background: none; border: none; font-size: 1.1rem; }
        .genre-card .track-item .delete:hover { color: #ff6666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NEURA ADMIN</h1>
            <a href="logout.php">Logout</a>
        </div>
        
        <?php if (isset($_GET['synced'])): ?>
            <div class="upload-result success">Synced! Added: <?php echo $_GET['count'] ?? 0; ?> tracks</div>
        <?php endif; ?>
        
        <?php if ($uploadResult): ?>
            <div class="upload-result <?php echo strpos($uploadResult, 'successful') !== false ? 'success' : 'error'; ?>">
                <?php echo $uploadResult; ?>
            </div>
        <?php endif; ?>
        
        <div class="upload-form">
            <h3>Upload New Track</h3>
            <form method="POST" enctype="multipart/form-data">
                <select name="genre" required>
                    <option value="">Select genre</option>
                    <?php foreach ($allGenres as $key => $name): ?>
                        <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="title" placeholder="Track title" required>
                <input type="text" name="artist" placeholder="Artist">
                <input type="file" name="file" accept=".mp3" required>
                <button type="submit">Upload</button>
            </form>
        </div>
        
        <div class="genre-grid">
            <?php foreach ($allGenres as $key => $name): 
                $tracks = $db->getTracksByGenre($key);
            ?>
                <div class="genre-card">
                    <h3><?php echo $name; ?></h3>
                    <div class="count"><?php echo count($tracks); ?> tracks</div>
                    <div class="actions">
                        <a href="?sync=<?php echo $key; ?>">Scan folder</a>
                        <a href="edit.php?genre=<?php echo $key; ?>">Edit</a>
                    </div>
                    <div class="tracks">
                        <?php if (count($tracks) > 0): ?>
                            <?php foreach (array_slice($tracks, 0, 5) as $track): ?>
                                <div class="track-item">
                                    <span class="title"><?php echo htmlspecialchars($track['title']); ?></span>
                                    <form method="POST" action="delete.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $track['id']; ?>">
                                        <button type="submit" class="delete" onclick="return confirm('Delete track?')">✕</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($tracks) > 5): ?>
                                <div style="color:#8c8ca3; font-size:0.8rem; text-align:center; padding:5px;">
                                    + <?php echo count($tracks) - 5; ?> more
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="color:#8c8ca3; font-size:0.8rem; padding:10px 0; text-align:center;">No tracks</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
