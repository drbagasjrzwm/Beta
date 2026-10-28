<?php

require_once __DIR__ . "/config/database.php";

header("Content-Type: application/json; charset=UTF-8");


// ==============================
// Pastikan request POST
// ==============================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Request tidak valid."
    ]);

    exit;
}


// ==============================
// Ambil ID
// ==============================

$id = isset($_POST["id"])
    ? (int) $_POST["id"]
    : 0;


if ($id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "ID lagu tidak valid."
    ]);

    exit;
}


// ==============================
// Cari lagu berdasarkan ID
// ==============================

$stmt = $conn->prepare(
    "SELECT filepath FROM songs WHERE id = ?"
);

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat query database."
    ]);

    exit;
}


$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->store_result();


if ($stmt->num_rows === 0) {

    $stmt->close();

    echo json_encode([
        "success" => false,
        "message" => "Lagu tidak ditemukan."
    ]);

    exit;
}


$stmt->bind_result($filepath);

$stmt->fetch();

$stmt->close();


// ==============================
// Hapus file MP3
// ==============================

$filePath = __DIR__ . "/" . $filepath;


if (file_exists($filePath)) {

    if (!unlink($filePath)) {

        echo json_encode([
            "success" => false,
            "message" =>
                "File MP3 tidak dapat dihapus dari folder."
        ]);

        exit;
    }
}


// ==============================
// Hapus data dari database
// ==============================

$stmt = $conn->prepare(
    "DELETE FROM songs WHERE id = ?"
);

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat query DELETE."
    ]);

    exit;
}


$stmt->bind_param("i", $id);


if ($stmt->execute()) {

    $stmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Lagu berhasil dihapus."
    ]);

} else {

    $stmt->close();

    echo json_encode([
        "success" => false,
        "message" =>
            "Data lagu gagal dihapus dari database."
    ]);
}