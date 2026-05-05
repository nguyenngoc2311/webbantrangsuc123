<?php
session_start();
include("config.php");

// Nếu người dùng vào từ email chứa email + token, lưu lại để mở trang reset
if (isset($_GET['email']) && isset($_GET['token'])) {
    $_SESSION['reset_email'] = $_GET['email'];
    $_SESSION['reset_token'] = $_GET['token'];
}

// 🔒 Check session
if (!isset($_SESSION['reset_email'])) {
    echo "<script>alert('Phiên hết hạn hoặc đường dẫn không hợp lệ! Vui lòng thử lại.'); window.location='quenmatkhau.php';</script>";
    exit();
}

if(isset($_POST['reset'])){
    $otp = $_POST['otp'];
    $pass = $_POST['matkhau'];
    $repass = $_POST['nhap_lai'];

    if (!isset($_SESSION['otp'])) {
        echo "<script>alert('Phiên OTP đã hết hạn. Vui lòng gửi lại mail.'); window.location='quenmatkhau.php';</script>";
        exit();
    }

    if($otp != $_SESSION['otp']){
        echo "<script>alert('Sai OTP!');</script>";
    } 
    elseif($pass != $repass){
        echo "<script>alert('Mật khẩu không khớp!');</script>";
    } 
    else {
        $email = $_SESSION['reset_email'];

        // DÙNG password_hash
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE nguoidungs SET MatKhau=? WHERE Email=?");
        $stmt->bind_param("ss", $hash, $email);

        if($stmt->execute()){
            unset($_SESSION['otp']);
            unset($_SESSION['reset_email']);

            echo "<script>alert('Đổi mật khẩu thành công!'); window.location='login.php';</script>";
            exit();
        } else {
            echo "<script>alert('Lỗi cập nhật!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Reset mật khẩu</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body class="login-page">
<div class="login-container">
    <h2>NHẬP OTP</h2>

    <form method="POST">
        <div class="input-group">
            <input type="text" name="otp" placeholder="Nhập OTP" required>
        </div>

        <div class="input-group">
            <input type="password" name="matkhau" placeholder="Mật khẩu mới" required>
        </div>

        <div class="input-group">
            <input type="password" name="nhap_lai" placeholder="Nhập lại mật khẩu" required>
        </div>

        <button type="submit" name="reset">Xác nhận</button>
    </form>
</div>
</body>
</html>