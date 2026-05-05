<?php
session_start();
include("config.php");

if(isset($_POST['dangnhap'])){

    $email = trim($_POST['email']);
    $matkhau = trim($_POST['matkhau']);

    $sql = "SELECT * FROM nguoidungs WHERE Email=?";
    $stmt = $conn->prepare($sql);

    if($stmt){
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $user = $result->fetch_assoc();

            // 1. KIỂM TRA MẬT KHẨU
            if(password_verify($matkhau, $user['MatKhau'])){

                // 2. KIỂM TRA TRẠNG THÁI TÀI KHOẢN (THÊM MỚI Ở ĐÂY)
                if($user['TrangThai'] == 0){
                    echo "<script>alert('Tài khoản của bạn đã bị khóa! Vui lòng liên hệ Admin.'); window.location.href='login.php';</script>";
                    exit();
                }

                // Nếu không bị khóa thì mới tạo Session
                $_SESSION['id_khachhang'] = $user['Idnguoidung'];
                $_SESSION['id'] = $user['Idnguoidung'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['hoten'] = $user['HoTen'];
                $_SESSION['vaitro'] = $user['VaiTro'];
                $_SESSION['anh_dai_dien'] = $user['AnhDaiDien'];

                if($user['VaiTro'] == "Admin"){
                    header("Location: ../admin/admin.php");
                } else {
                    header("Location: index.php");
                }
                exit();

            } else {
                echo "<script>alert('Sai mật khẩu!');</script>";
            }

        } else {
            echo "<script>alert('Email không tồn tại!');</script>";
        }

        $stmt->close();
    } else {
        die("Lỗi SQL: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Luxury</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body class="login-page">

<div class="login-container">
    <h2>LUXURY LOGIN</h2>

    <form method="POST" action="">
        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
            <input type="password" name="matkhau" placeholder="Mật khẩu" required>
        </div>
        <button type="submit" name="dangnhap">Đăng nhập</button>
    </form>

    <p class="register-link">
        Chưa có tài khoản? <a href="dangky.php">Đăng ký ngay</a>
    </p>

    <p>
        <a href="quenmatkhau.php">Quên mật khẩu?</a>
    </p>
</div>

</body>
</html>