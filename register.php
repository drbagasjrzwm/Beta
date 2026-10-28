<?php
require_once "config/database.php";
session_start();

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $username, $password);

    if ($stmt->execute()) {
        $success = true;
        $message = "Akun berhasil terdaftar!";
    } else {
        $message = "Gagal mendaftar: Email atau Username sudah digunakan.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - Musify by Mukti</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="upload-page">
    <div class="upload-box">
        <h1>Daftar Akun Musify</h1>
        <p>Buat akun baru untuk mulai menyimpan dan mendengarkan musik.</p>

        <?php if (!empty($message)): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
            <?php if ($success): ?>
                <script>
                    alert("Akun berhasil terdaftar!");
                    window.location.href = "login.php";
                </script>
            <?php endif; ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Masukkan email...">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Buat username...">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Buat password...">
            </div>
            <button type="submit" class="submit-btn">SUBMIT</button>
        </form>
        <p style="margin-top: 15px; text-align: center;">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </div>
</body>
</html>