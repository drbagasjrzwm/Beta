<?php
require_once "config/database.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ? AND username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_username, $db_password);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $db_username;
            echo "<script>alert('Selamat Datang, " . $db_username . "'); window.location.href = 'index.php';</script>";
            exit;
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Email atau Username tidak ditemukan.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Musify by Mukti</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="upload-page">
    <div class="upload-box">
        <h1>Masuk ke Musify</h1>
        <p>Silakan login dengan akun yang telah terdaftar.</p>

        <?php if (!empty($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Email terdaftar...">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Username Anda...">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Password...">
            </div>
            <button type="submit" class="submit-btn">Masuk</button>
        </form>
        <p style="margin-top: 15px; text-align: center;">Belum punya akun? <a href="register.php">Daftar</a></p>
    </div>
</body>
</html>