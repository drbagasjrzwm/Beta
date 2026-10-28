<?php
require_once __DIR__ . "/config/database.php";
session_start();

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Request tidak valid."]);
    exit;
}

if (!isset($_FILES["music"])) {
    echo json_encode(["success" => false, "message" => "File musik tidak ditemukan."]);
    exit;
}

$userId = $_SESSION["user_id"];
$title = trim($_POST["title"] ?? pathinfo($_FILES["music"]["name"], PATHINFO_FILENAME));
$artist = trim($_POST["artist"] ?? "Unknown Artist");
$file = $_FILES["music"];

// Upload Musik
$uploadDirMusic = __DIR__ . "/uploads/music/";
if (!is_dir($uploadDirMusic)) mkdir($uploadDirMusic, 0777, true);

$newFilenameMusic = uniqid("music_", true) . ".mp3";
$targetFileMusic = $uploadDirMusic . $newFilenameMusic;
$filepath = "uploads/music/" . $newFilenameMusic;

if (!move_uploaded_file($file["tmp_name"], $targetFileMusic)) {
    echo json_encode(["success" => false, "message" => "Gagal menyimpan file musik."]);
    exit;
}

// Upload Cover (Opsional)
$coverpath = null;
if (isset($_FILES["cover"]) && $_FILES["cover"]["error"] === UPLOAD_ERR_OK) {
    $uploadDirCover = __DIR__ . "/uploads/covers/";
    if (!is_dir($uploadDirCover)) mkdir($uploadDirCover, 0777, true);
    
    $coverExt = pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION);
    $newFilenameCover = uniqid("cover_", true) . "." . $coverExt;
    $targetFileCover = $uploadDirCover . $newFilenameCover;
    
    if (move_uploaded_file($_FILES["cover"]["tmp_name"], $targetFileCover)) {
        $coverpath = "uploads/covers/" . $newFilenameCover;
    }
}

// Simpan ke Database
$stmt = $conn->prepare("INSERT INTO songs (user_id, title, artist, filename, filepath, cover) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $userId, $title, $artist, $newFilenameMusic, $filepath, $coverpath);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Lagu berhasil diupload.", "title" => $title]);
} else {
    echo json_encode(["success" => false, "message" => "Gagal menyimpan ke database."]);
}
$stmt->close();