<?php
include('../php/config.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$u_id = $_SESSION['id'];

// Fetch the user's data
$query = "SELECT * FROM nguoidungs WHERE Idnguoidung = '$u_id'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    echo "User not found.";
    exit();
}

// Use 'anh_dai_dien' as the column for profile picture
$avatar = !empty($user_data['anh_dai_dien']) ? $user_data['anh_dai_dien'] : 'default-avatar.png';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ của tôi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Hồ sơ của tôi</h1>
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thông tin cá nhân</h5>
                    
                    <!-- Display Profile Picture -->
                    <div class="text-center mb-3">
                        <img src="../image/<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="img-fluid rounded-circle" style="width: 150px; height: 150px;">
                    </div>
                    
                    <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($user_data['HoTen']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['Email']); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($user_data['SoDienThoai']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($user_data['DiaChi']); ?></p>
                    <p><strong>Ngày đăng ký:</strong> <?php echo htmlspecialchars($user_data['ngay_tao']); ?></p>
                    <a href="capnhathoso.php" class="btn btn-primary">Chỉnh sửa hồ sơ</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>