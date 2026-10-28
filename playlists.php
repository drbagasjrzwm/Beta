<?php
require_once "config/database.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$sql = "SELECT * FROM playlists WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Playlist - Musify by Mukti</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">📂 Daftar Playlist</div>
        <a href="index.php" class="upload-btn">Kembali ke Beranda</a>
    </header>
    <main class="container">
        <div class="song-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($pl = $result->fetch_assoc()): ?>
                    <div class="song-card">
                        <div class="song-info">
                            <h3><?= htmlspecialchars($pl['name']) ?></h3>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Belum ada Playlist yang Dibuat</h3>
                    <p>Buat playlist baru melalui menu tambahan di music player.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>