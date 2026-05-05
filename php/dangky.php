<?php
session_start();
include("config.php"); 

$thongbao = '';

if (isset($_POST['dangky'])) {
    $hoten = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $matkhau = trim($_POST['matkhau']);
    $xacthucmatkhau = trim($_POST['xacthucmatkhau']);

    // ====== VALIDATION PHP ======
    if (empty($email)) {
        $thongbao = "Email không được để trống!";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $thongbao = "Email không hợp lệ!";
    }
    elseif (empty($matkhau)) {
        $thongbao = "Mật khẩu không được để trống!";
    }
    elseif ($matkhau !== $xacthucmatkhau) {
        $thongbao = "Mật khẩu không khớp!";
    } 
    else {
        // HASH PASSWORD
        $hashed_password = password_hash($matkhau, PASSWORD_BCRYPT);

        // Check email tồn tại
        $stmt = $conn->prepare("SELECT Idnguoidung FROM NguoiDungs WHERE Email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $thongbao = "Email đã tồn tại!";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO NguoiDungs (Email, MatKhau, HoTen, VaiTro, AnhDaiDien) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $vai_tro = 'Khách Hàng';
            $anh = 'default.png';

            $stmt->bind_param("sssss", $email, $hashed_password, $hoten, $vai_tro, $anh);

            if ($stmt->execute()) {
                $thongbao = "Đăng ký thành công!";
            } else {
                $thongbao = "Lỗi đăng ký!";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body class="login-page">

<div class="login-container">
    <h2>LUXURY REGISTRATION</h2>

    <form method="POST" action="">
        <input type="text" name="hoten" placeholder="Họ tên" required>
        <input type="text" name="email" placeholder="Email" required>
        <input type="password" name="matkhau" placeholder="Mật khẩu" required>
        <input type="password" name="xacthucmatkhau" placeholder="Xác nhận mật khẩu" required>

        <button type="submit" name="dangky">Đăng ký</button>
    </form>

    <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    <a href="index.php" class="back-home">← Về trang chủ</a>
</div>

<?php if (!empty($thongbao)) : ?>
    <script>
        // Hiển thị thông báo dạng alert của trình duyệt
        alert("<?php echo addslashes($thongbao); ?>");
    </script>
<?php endif; ?>

</body>
</html>