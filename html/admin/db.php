<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

class RadioDB {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new PDO('sqlite:' . DB_PATH);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->exec("PRAGMA encoding = 'UTF-8'");
            $this->createTables();
        } catch (PDOException $e) {
            die('DB Error: ' . $e->getMessage());
        }
    }
    
    private function createTables() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tracks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                genre TEXT NOT NULL,
                file_name TEXT NOT NULL,
                title TEXT NOT NULL,
                artist TEXT,
                upload_date DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    public function addTrack($genre, $fileName, $title, $artist = '') {
        $stmt = $this->db->prepare("
            INSERT INTO tracks (genre, file_name, title, artist)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$genre, $fileName, $title, $artist]);
    }
    
    public function getTracksByGenre($genre) {
        $stmt = $this->db->prepare("
            SELECT * FROM tracks 
            WHERE genre = ? 
            ORDER BY upload_date DESC
        ");
        $stmt->execute([$genre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getGenres() {
        $stmt = $this->db->query("
            SELECT DISTINCT genre FROM tracks
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function deleteTrack($id) {
        $stmt = $this->db->prepare("DELETE FROM tracks WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateTrack($id, $title, $artist) {
        $stmt = $this->db->prepare("
            UPDATE tracks 
            SET title = ?, artist = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$title, $artist, $id]);
    }
    
    public function getTrack($id) {
        $stmt = $this->db->prepare("SELECT * FROM tracks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
