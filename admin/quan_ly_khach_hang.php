<?php
include '../php/config.php';
session_start();

// 1. LẤY DANH SÁCH TÀI KHOẢN TỪ BẢNG NGUOIDUNG ĐỂ ĐỔ VÀO SELECT
$user_res = mysqli_query($conn, "SELECT Idnguoidung, hoten FROM nguoidungs ORDER BY Idnguoidung ASC");
$user_list = mysqli_fetch_all($user_res, MYSQLI_ASSOC);

// 2. XỬ LÝ THÊM KHÁCH HÀNG
if (isset($_POST['add_customer'])) {
    $hoten = mysqli_real_escape_string($conn, $_POST['HoTen']);
    $email = mysqli_real_escape_string($conn, $_POST['Email']);
    $pass = mysqli_real_escape_string($conn, $_POST['MatKhau']);
    $sdt = mysqli_real_escape_string($conn, $_POST['SoDienThoai']);
    $diachi = mysqli_real_escape_string($conn, $_POST['DiaChi']);
    $u_id = mysqli_real_escape_string($conn, $_POST['user_id']);

    mysqli_query($conn, "INSERT INTO khachhang (HoTen, Email, MatKhau, SoDienThoai, DiaChi, user_id) 
                         VALUES ('$hoten', '$email', '$pass', '$sdt', '$diachi', '$u_id')");
    header("Location: quan_ly_khach_hang.php");
    exit;
}

// 3. XỬ LÝ CẬP NHẬT KHÁCH HÀNG
if (isset($_POST['edit_customer'])) {
    $id = mysqli_real_escape_string($conn, $_POST['MaKH']);
    $hoten = mysqli_real_escape_string($conn, $_POST['HoTen']);
    $email = mysqli_real_escape_string($conn, $_POST['Email']);
    $pass = mysqli_real_escape_string($conn, $_POST['MatKhau']);
    $sdt = mysqli_real_escape_string($conn, $_POST['SoDienThoai']);
    $diachi = mysqli_real_escape_string($conn, $_POST['DiaChi']);
    $u_id = mysqli_real_escape_string($conn, $_POST['user_id']);

    mysqli_query($conn, "UPDATE khachhang SET 
                         HoTen='$hoten', Email='$email', MatKhau='$pass', 
                         SoDienThoai='$sdt', DiaChi='$diachi', user_id='$u_id' 
                         WHERE MaKH='$id'");
    header("Location: quan_ly_khach_hang.php");
    exit;
}

// 4. XỬ LÝ XÓA KHÁCH HÀNG
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM khachhang WHERE MaKH=$id");
    header("Location: quan_ly_khach_hang.php");
    exit;
}

// 5. LẤY DỮ LIỆU KHÁCH HÀNG HIỂN THỊ RA BẢNG
$result = mysqli_query($conn, "SELECT * FROM khachhang ORDER BY MaKH DESC");
$customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/qlkh.css">
</head>
<body>

<?php include('header.php'); ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <div>
                <h4 class="fw-bold mb-0"><i class="bi bi-people-fill me-2"></i>Quản lý khách hàng</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small"><a href="#" class="text-muted text-decoration-none">Luxury Admin</a></li>
                        <li class="breadcrumb-item small active" aria-current="page">Khách hàng</li>
                    </ol>
                </nav>
            </div>
            <button class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-1"></i> Thêm khách hàng
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Mật khẩu</th>
                            <th>SĐT</th>
                            <th>Địa chỉ</th>
                            <th>User ID</th>
                            <th class="text-center pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $row): ?>
                        <tr>
                            <td class="fw-bold ps-4">#<?= $row['MaKH'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['HoTen']) ?></td>
                            <td><?= htmlspecialchars($row['Email']) ?></td>
                            <td><code class="text-muted small"><?= htmlspecialchars($row['MatKhau']) ?></code></td>
                            <td><?= htmlspecialchars($row['SoDienThoai']) ?></td>
                            <td class="small" style="max-width: 200px;"><?= htmlspecialchars($row['DiaChi']) ?></td>
                            <td><span class="badge bg-secondary">ID: <?= $row['user_id'] ?></span></td>
                            <td class="text-center pe-4">
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['MaKH'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete_id=<?= $row['MaKH'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Thêm khách hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input name="HoTen" class="form-control" placeholder="Họ tên" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="Email" class="form-control" placeholder="Email" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input name="MatKhau" class="form-control" placeholder="Mật khẩu" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input name="SoDienThoai" class="form-control" placeholder="Số điện thoại">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <input name="DiaChi" class="form-control" placeholder="Địa chỉ">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Chọn User ID liên kết --</option>
                        <?php foreach ($user_list as $u): ?>
                            <option value="<?= $u['Idnguoidung'] ?>">ID: <?= $u['Idnguoidung'] ?> - <?= htmlspecialchars($u['hoten']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button name="add_customer" class="btn btn-primary px-4">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($customers as $row): ?>
<div class="modal fade" id="editModal<?= $row['MaKH'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Sửa thông tin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="MaKH" value="<?= $row['MaKH'] ?>">
                
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input name="HoTen" value="<?= htmlspecialchars($row['HoTen']) ?>" class="form-control" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="Email" value="<?= htmlspecialchars($row['Email']) ?>" class="form-control" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input name="MatKhau" value="<?= htmlspecialchars($row['MatKhau']) ?>" class="form-control" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input name="SoDienThoai" value="<?= htmlspecialchars($row['SoDienThoai']) ?>" class="form-control">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <input name="DiaChi" value="<?= htmlspecialchars($row['DiaChi']) ?>" class="form-control">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <select name="user_id" class="form-select" required>
                        <?php foreach ($user_list as $u): ?>
                            <option value="<?= $u['Idnguoidung'] ?>" <?= ($u['Idnguoidung'] == $row['user_id']) ? 'selected' : '' ?>>
                                ID: <?= $u['Idnguoidung'] ?> - <?= htmlspecialchars($u['hoten']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button name="edit_customer" class="btn btn-warning px-4">Cập nhật</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>