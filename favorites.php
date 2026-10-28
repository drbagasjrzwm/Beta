<?php
require_once "config/database.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$sql = "SELECT s.* FROM favorites f JOIN songs s ON f.song_id = s.id WHERE f.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Favorit - Musify by Mukti</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">❤️ Lagu Favorit Saya</div>
        <a href="index.php" class="upload-btn">Kembali ke Beranda</a>
    </header>
    <main class="container">
        <div class="song-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($song = $result->fetch_assoc()): ?>
                    <div class="song-card">
                        <div class="song-left">
                            <div class="song-cover">🎵</div>
                            <div class="song-info">
                                <h3><?= htmlspecialchars($song['title']) ?></h3>
                                <p><?= htmlspecialchars($song['artist']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Anda Belum Menambahkan Favorit Lagu</h3>
                    <p>Tandai lagu favorit Anda dari daftar musik untuk melihatnya di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>