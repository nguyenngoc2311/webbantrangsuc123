<?php
session_start();
include('../php/config.php');

// ===== CHECK LOGIN =====
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['id'];

// ================== THÊM ==================
if (isset($_POST['add_user'])) {
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $mat_khau_raw = $_POST['mat_khau'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $dia_chi = $_POST['dia_chi'];
    $vai_tro = $_POST['vai_tro'];

    $hashed_password = password_hash($mat_khau_raw, PASSWORD_DEFAULT);
    $anh_dai_dien = 'default.jpg';

    // Upload ảnh an toàn
    if (!empty($_FILES['anh_dai_dien']['name'])) {
        $allowed = ['image/jpeg','image/png','image/jpg'];
        if (in_array($_FILES['anh_dai_dien']['type'], $allowed)) {
            $file_name = time() . "_" . basename($_FILES["anh_dai_dien"]["name"]);
            move_uploaded_file($_FILES["anh_dai_dien"]["tmp_name"], "../image/" . $file_name);
            $anh_dai_dien = $file_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO nguoidungs 
    (anh_dai_dien, HoTen, Email, MatKhau, SoDienThoai, DiaChi, VaiTro, TrangThai, ngay_tao) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");

    $stmt->bind_param("sssssss", $anh_dai_dien, $ho_ten, $email, $hashed_password, $so_dien_thoai, $dia_chi, $vai_tro);
    $stmt->execute();

    header("Location: quan_ly_nguoi_dung.php");
    exit;
}

// ================== SỬA ==================
if (isset($_POST['edit_user_submit'])) {
    $id = (int)$_POST['id'];

    // check không sửa Admin nếu cần
    $check = $conn->query("SELECT VaiTro FROM nguoidungs WHERE Idnguoidung=$id")->fetch_assoc();
    
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $dia_chi = $_POST['dia_chi'];
    $vai_tro = $_POST['vai_tro'];

    $sql = "UPDATE nguoidungs SET HoTen=?, Email=?, SoDienThoai=?, DiaChi=?, VaiTro=?";

    $params = [$ho_ten, $email, $so_dien_thoai, $dia_chi, $vai_tro];
    $types = "sssss";

    // password
    if (!empty($_POST['mat_khau'])) {
        $hashed_password = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
        $sql .= ", MatKhau=?";
        $params[] = $hashed_password;
        $types .= "s";
    }

    // image
 if (!empty($_FILES['anh_dai_dien']['name'])) {
    $allowed = ['image/jpeg','image/png','image/jpg'];

    if (in_array($_FILES['anh_dai_dien']['type'], $allowed)) {

        // Lấy tên file gốc
        $file_name = basename($_FILES["anh_dai_dien"]["name"]);

        // Upload
        move_uploaded_file(
            $_FILES["anh_dai_dien"]["tmp_name"], 
            "../image/" . $file_name
        );

        // Lưu DB
        $sql .= ", anh_dai_dien=?";
        $params[] = $file_name;
        $types .= "s";
    }
}

    $sql .= " WHERE Idnguoidung=?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    header("Location: quan_ly_nguoi_dung.php");
    exit;
}

// ================== XÓA ==================
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    if ($id == $current_user_id) {
        echo "<script>alert('Không thể xóa chính mình');location='quan_ly_nguoi_dung.php';</script>";
        exit;
    }

    // check admin
    $user = $conn->query("SELECT VaiTro FROM nguoidungs WHERE Idnguoidung=$id")->fetch_assoc();
    if ($user['VaiTro'] == 'Admin') {
        echo "<script>alert('Không thể xóa Admin');location='quan_ly_nguoi_dung.php';</script>";
        exit;
    }

    $conn->query("DELETE FROM nguoidungs WHERE Idnguoidung=$id");

    header("Location: quan_ly_nguoi_dung.php");
    exit;
}

// ================== KHÓA ==================
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];

    if ($id == $current_user_id) {
        echo "<script>alert('Không thể tự khóa');location='quan_ly_nguoi_dung.php';</script>";
        exit;
    }

    $user = $conn->query("SELECT VaiTro FROM nguoidungs WHERE Idnguoidung=$id")->fetch_assoc();
    if ($user['VaiTro'] == 'Admin') {
        echo "<script>alert('Không thể khóa Admin');location='quan_ly_nguoi_dung.php';</script>";
        exit;
    }

    $conn->query("UPDATE nguoidungs SET TrangThai = IF(TrangThai=1,0,1) WHERE Idnguoidung=$id");

    header("Location: quan_ly_nguoi_dung.php");
    exit;
}

// ================== LOAD ==================
$result = $conn->query("SELECT * FROM nguoidungs ORDER BY Idnguoidung ASC");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/qlnd.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <?php include('header.php'); ?>
    </nav>

    <div id="content">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
                <h1 class="h4 mb-0 text-gray-800 fw-bold">Quản Lý Thành Viên</h1>
                <button class="btn btn-primary btn-sm shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus-circle me-1"></i> Thêm Người Dùng
                </button>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Thành viên</th>
                                <th>Email</th>
                                <th>Mật khẩu</th>
                                <th>Liên hệ</th>
                                <th>Vai Trò</th>
                                <th>Trạng Thái</th>
                                <th class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $row): ?>
                            <tr>
                                <td class="ps-4 text-muted small">#<?= $row['Idnguoidung'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../image/<?= $row['anh_dai_dien'] ?>" class="user-avatar me-2 shadow-sm" width="40" height="40" style="border-radius:50%; object-fit:cover;">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['HoTen']) ?></div>
                                    </div>
                                </td>
                                <td class="small"><?= htmlspecialchars($row['Email']) ?></td>
                                <td><code class="hash-text"><?= substr($row['MatKhau'], 0, 10) ?>...</code></td>
                                <td>
                                    <div class="small fw-bold text-dark"><?= htmlspecialchars($row['SoDienThoai']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($row['DiaChi']) ?></div>
                                </td>
                                <td><span class="badge <?= ($row['VaiTro']=='Admin')?'bg-danger':'bg-primary' ?>"><?= $row['VaiTro'] ?></span></td>
                                <td>
                                    <span class="badge <?= ($row['TrangThai'] == 1)?'bg-success-subtle text-success':'bg-secondary-subtle text-secondary' ?> border">
                                        <?= ($row['TrangThai'] == 1)?'Hoạt động':'Đã khóa' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">

                                        <?php if (isset($row['VaiTro']) && $row['VaiTro'] != 'Admin'): ?>

                                            <?php if ($row['Idnguoidung'] != $current_user_id): ?>
                                                <a href="?toggle_status=<?= $row['Idnguoidung'] ?>" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas <?= $row['TrangThai'] ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                                </a>
                                            <?php endif; ?>

                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $row['Idnguoidung'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <?php if ($row['Idnguoidung'] != $current_user_id): ?>
                                                <a href="?delete_id=<?= $row['Idnguoidung'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa người dùng này?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <span class="text-muted small">Admin </span>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Thêm Người Dùng Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input name="ho_ten" class="form-control mb-3" placeholder="Họ tên đầy đủ" required>
                <input name="email" type="email" class="form-control mb-3" placeholder="Địa chỉ Email" required>
                <input name="mat_khau" type="password" class="form-control mb-3" placeholder="Mật khẩu" required>
                <input name="so_dien_thoai" class="form-control mb-3" placeholder="Số điện thoại">
                <input name="dia_chi" class="form-control mb-3" placeholder="Địa chỉ liên hệ">
                <select name="vai_tro" class="form-select mb-3">
                    <option value="Khách hàng">Khách hàng</option>
                    <option value="Admin">Admin</option>
                </select>
                <label class="form-label small fw-bold">Ảnh đại diện</label>
                <input type="file" name="anh_dai_dien" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100" name="add_user">Tạo tài khoản</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($users as $row): ?>
<div class="modal fade" id="editUserModal<?= $row['Idnguoidung'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật: <?= htmlspecialchars($row['HoTen']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" value="<?= $row['Idnguoidung'] ?>">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Họ tên</label>
                    <input name="ho_ten" class="form-control" value="<?= htmlspecialchars($row['HoTen']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Email</label>
                    <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($row['Email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Mật khẩu mới (Để trống nếu không đổi)</label>
                    <input name="mat_khau" type="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Số điện thoại</label>
                    <input name="so_dien_thoai" class="form-control" value="<?= htmlspecialchars($row['SoDienThoai']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Địa chỉ</label>
                    <input name="dia_chi" class="form-control" value="<?= htmlspecialchars($row['DiaChi']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Vai trò</label>
                    <select name="vai_tro" class="form-select">
                        <option value="Admin" <?= ($row['VaiTro']=='Admin')?'selected':'' ?>>Admin</option>
                        <option value="Khách hàng" <?= ($row['VaiTro']=='Khách hàng')?'selected':'' ?>>Khách hàng</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Ảnh đại diện mới</label>
                    <input type="file" name="anh_dai_dien" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary" name="edit_user_submit">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>