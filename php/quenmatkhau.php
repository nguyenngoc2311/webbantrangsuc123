<?php
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body class="login-page">
<div class="login-container">
    <h2>QUÊN MẬT KHẨU</h2>

    <form method="POST" action="send_otp.php">
        <div class="input-group">
            <input type="email" name="email" placeholder="Nhập email" required>
        </div>

        <button type="submit" name="guimail">Gửi OTP</button>
    </form>

    <a href="login.php" class="back-home">← Quay lại đăng nhập</a>
</div>
</body>
</html>