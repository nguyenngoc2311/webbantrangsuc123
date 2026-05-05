<?php
include('config.php');
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php"); 
    exit();
}

$user_id = $_SESSION['id'];  

// 1. Lấy dữ liệu người dùng hiện tại
$sql = "SELECT * FROM nguoidungs WHERE Idnguoidung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error_msg = "";

// 2. Xử lý cập nhật khi nhấn nút
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_hoten = $_POST['hoten'];
    $new_email = $_POST['email'];
    $new_sdt   = $_POST['soDienThoai'];
    $new_diachi = $_POST['diaChi'];
    $file_name = $user['anh_dai_dien']; // Giữ tên cũ nếu không chọn file mới

    // Nếu có chọn file mới (Choose File)
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $target_dir = "../image/"; 
        $file_name = basename($_FILES["avatar"]["name"]); // Lấy tên gốc (VD: 2.jpg)
        $target_file = $target_dir . $file_name;

        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allow_types = array('jpg', 'png', 'jpeg', 'gif', 'webp');

        if (in_array($file_extension, $allow_types)) {
            move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file);
        } else {
            $error_msg = "Định dạng ảnh không hợp lệ.";
        }
    }

    if (empty($error_msg)) {
        $update_sql = "UPDATE nguoidungs SET HoTen=?, Email=?, SoDienThoai=?, DiaChi=?, anh_dai_dien=? WHERE Idnguoidung=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssssi", $new_hoten, $new_email, $new_sdt, $new_diachi, $file_name, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['hoten'] = $new_hoten; // Cập nhật session tên để hiện ở navbar
            header("Location: index.php?success=1");  // Quay về trang chủ
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật hồ sơ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css">
    <style>
        .avatar-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0" style="border-radius: 15px;">
                    <div class="card-header bg-primary text-white text-center py-3" style="border-radius: 15px 15px 0 0;">
                        <h5 class="mb-0">Thông tin cá nhân</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_GET['success'])) echo '<div class="alert alert-success">Đã cập nhật!</div>'; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="../image/<?php echo $user['anh_dai_dien']; ?>?v=<?php echo time(); ?>" 
                                     class="avatar-preview" onerror="this.src='https://via.placeholder.com/120';">
                                <div class="mt-3">
                                    <input type="file" name="avatar" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" name="hoten" class="form-control" value="<?php echo htmlspecialchars($user['HoTen']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="soDienThoai" class="form-control" value="<?php echo htmlspecialchars($user['SoDienThoai']); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" name="diaChi" class="form-control" value="<?php echo htmlspecialchars($user['DiaChi']); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Lưu thay đổi</button>
                        </form>

                        <!-- Nút quay về trang chủ -->
                        <a href="index.php" class="btn btn-secondary w-100 mt-3">Quay về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>