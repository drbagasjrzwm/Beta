<?php
require_once "config/database.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"];
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM songs WHERE title LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $search . "%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musify by ZWM</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .user-welcome { font-size: 16px; font-weight: bold; color: #fff; }
        .profile-dropdown { position: relative; display: inline-block; cursor: pointer; color: #fff; }
        .dropdown-content { display: none; position: absolute; right: 0; background-color: #fff; min-width: 130px; box-shadow: 0px 8px 16px rgba(0,0,0,0.2); z-index: 1001; border-radius: 8px; overflow: hidden; }
        .dropdown-content a { color: #18181b !important; padding: 10px 14px; text-decoration: none; display: block; font-size: 14px; }
        .dropdown-content a:hover { background-color: #f1f1f1; }
        .profile-dropdown:hover .dropdown-content { display: block; }
        .search-bar { padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; width: 250px; }
        /* Modal Upload Custom */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: #fff; padding: 25px; border-radius: 12px; width: 400px; }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <header class="navbar">
        <div class="user-welcome">
            Musify by ZWM
        </div>

        <form method="GET" action="">
            <input type="text" name="search" class="search-bar" placeholder="Cari judul lagu..." value="<?= htmlspecialchars($search) ?>">
        </form>

        <div style="display: flex; align-items: center; gap: 15px;">
            <button type="button" class="upload-btn" onclick="openUploadModal()">+ Upload Lagu</button>
            <div class="profile-dropdown">
                ⚙️ Profil ▾
                <div class="dropdown-content">
                    <a href="#">Profile</a>
                    <a href="#">Setting</a>
                    <a href="#">About Us</a>
                    <a href="logout.php" style="color: red !important;">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN -->
    <main class="container">
        <section class="hero">
            <h1>Selamat Datang, <?= htmlspecialchars($username) ?></h1>
            <p>Dengarkan lagu yang kamu simpan di Musify.</p>
        </section>

        <section class="songs-section">
            <h2>Daftar Lagu</h2>
            <div class="song-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($song = $result->fetch_assoc()): ?>
                        <div class="song-card" data-id="<?= $song['id'] ?>" data-src="<?= htmlspecialchars($song['filepath']) ?>" data-title="<?= htmlspecialchars($song['title']) ?>" data-artist="<?= htmlspecialchars($song['artist']) ?>">
                            <div class="song-left">
                                <div class="song-cover">
                                    <?php if (!empty($song['cover'])): ?>
                                        <img src="<?= htmlspecialchars($song['cover']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:10px;">
                                    <?php else: ?>
                                        🎵
                                    <?php endif; ?>
                                </div>
                                <div class="song-info">
                                    <h3><?= htmlspecialchars($song['title']) ?></h3>
                                    <p><?= htmlspecialchars($song['artist']) ?></p>
                                </div>
                            </div>
                            <div class="song-actions">
                                <button type="button" class="play-btn">▶</button>
                                <button type="button" class="delete-btn" data-id="<?= $song['id'] ?>" data-title="<?= htmlspecialchars($song['title']) ?>">🗑️</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🎵</div>
                        <h3>Belum ada lagu</h3>
                        <p>Upload lagu MP3 untuk mulai mendengarkan musik.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- MUSIC PLAYER -->
    <div class="music-player">
        <div class="player-info">
            <div class="player-cover">🎵</div>
            <div>
                <h4 id="player-title">Belum ada lagu</h4>
                <p id="player-artist">-</p>
            </div>
        </div>
        <div class="player-controls">
            <button id="prev-btn" type="button">⏮</button>
            <button id="main-play-btn" type="button">▶</button>
            <button id="next-btn" type="button">⏭</button>
        </div>
        <div class="audio-container">
            <audio id="audio-player" controls>Browser kamu tidak mendukung audio player.</audio>
        </div>
        <!-- Tombol Tambah (+) Bagian Kanan Player -->
        <div>
            <button id="player-options-btn" style="background:#18181b; color:#fff; border:none; padding:10px 14px; border-radius:50%; cursor:pointer; font-size:16px;">+</button>
        </div>
    </div>

    <!-- MODAL UPLOAD KUSTOM -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <h3>Upload Lagu Kustom</h3>
            <form id="customUploadForm" enctype="multipart/form-data" style="margin-top:15px;">
                <div class="form-group"><label>File Musik (.mp3)</label><input type="file" name="music" accept=".mp3" required></div>
                <div class="form-group"><label>Judul Lagu</label><input type="text" name="title" placeholder="Nama lagu..."></div>
                <div class="form-group"><label>Nama Artist</label><input type="text" name="artist" placeholder="Nama artist..."></div>
                <div class="form-group"><label>Foto Cover Lagu</label><input type="file" name="cover" accept="image/*"></div>
                <button type="submit" class="submit-btn">Upload Sekarang</button>
                <button type="button" onclick="closeUploadModal()" style="width:100%; margin-top:8px; background:#ccc; border:none; padding:10px; border-radius:8px; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>

    <!-- MODAL OPSI PLAYER (+) -->
    <div id="optionsModal" class="modal">
        <div class="modal-content" style="text-align:center;">
            <h3>Menu Tambahan</h3>
            <div style="margin-top:20px; display:flex; flex-direction:column; gap:10px;">
                <button onclick="window.location.href='favorites.php'" class="submit-btn">❤️ Favorit Lagu</button>
                <button onclick="alert('Fitur Buat Playlist Baru');" class="submit-btn">📋 Buat Playlist Baru</button>
                <button onclick="alert('Fitur Daftar Playlist');" class="submit-btn">📂 Daftar Playlist</button>
                <button onclick="closeOptionsModal()" style="background:#ccc; border:none; padding:10px; border-radius:8px; cursor:pointer;">Tutup</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        function openUploadModal() { document.getElementById('uploadModal').style.display = 'flex'; }
        function closeUploadModal() { document.getElementById('uploadModal').style.display = 'none'; }
        document.getElementById('player-options-btn').onclick = () => { document.getElementById('optionsModal').style.display = 'flex'; };
        function closeOptionsModal() { document.getElementById('optionsModal').style.display = 'none'; }

        // AJAX Upload Kustom
        document.getElementById('customUploadForm').onsubmit = async function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let res = await fetch('upload.php', { method: 'POST', body: formData });
            let json = await res.json();
            if(json.success) {
                alert("🎵 Lagu berhasil diupload!");
                location.reload();
            } else {
                alert("Gagal: " + json.message);
            }
        };
    </script>
</body>
</html>