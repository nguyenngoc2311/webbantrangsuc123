<?php
include '../php/config.php'; 

// Khởi tạo session
session_start();

// 1. Xử lý CẬP NHẬT TRẠNG THÁI
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $new_status = mysqli_real_escape_string($conn, $_GET['update_status']);
    
    // Kiểm tra trạng thái hiện tại trước khi cho phép cập nhật (Bảo mật backend)
    $check_current_sql = "SELECT TrangThai FROM hoadon WHERE IdHoaDon = '$id'";
    $check_res = mysqli_query($conn, $check_current_sql);
    $current_row = mysqli_fetch_assoc($check_res);

    if ($current_row) {
        $current_st = $current_row['TrangThai'];
        // Nếu đã giao, hoàn thành hoặc đã hủy thì không cho phép đổi nữa qua URL
        if ($current_st == 'Đã giao' || $current_st == 'Hoàn thành' || $current_st == 'Đã Hủy') {
            $_SESSION['error'] = "Đơn hàng #$id đã đóng (Đã giao/Hoàn thành/Hủy), không thể thay đổi trạng thái!";
        } else {
            $update_sql = "UPDATE hoadon SET TrangThai = '$new_status' WHERE IdHoaDon = '$id'";
            if (mysqli_query($conn, $update_sql)) {
                $_SESSION['message'] = "Cập nhật đơn hàng #$id thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi cập nhật trạng thái!";
            }
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// 2. Xử lý XÓA ĐƠN HÀNG
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $check_status_sql = "SELECT TrangThai FROM hoadon WHERE IdHoaDon = '$delete_id'";
    $status_result = mysqli_query($conn, $check_status_sql);
    
    if (mysqli_num_rows($status_result) > 0) {
        $status_row = mysqli_fetch_assoc($status_result);
        if ($status_row['TrangThai'] == 'Đã Hủy') {
            mysqli_query($conn, "DELETE FROM chitiethoadon WHERE IdHoaDon = '$delete_id'");
            if (mysqli_query($conn, "DELETE FROM hoadon WHERE IdHoaDon = '$delete_id'")) {
                $_SESSION['message'] = "Đã xóa đơn hàng thành công!";
            }
        } else {
            $_SESSION['error'] = "Chỉ có thể xóa đơn hàng đã hủy!";
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// 3. Lấy dữ liệu hiển thị
$sql = "SELECT * FROM hoadon ORDER BY IdHoaDon DESC";
$result = $conn->query($sql);
$total_invoices = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Hóa Đơn | Luxury Jewelry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/qldh.css">
    
</head>
<body>

    <?php include('header.php'); ?>

    <div class="container main-content mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-0 text-dark">QUẢN LÝ HÓA ĐƠN</h2>
                <span class="badge bg-secondary">Tổng số: <?php echo $total_invoices; ?> hóa đơn</span>
            </div>
            <a href="xuat_excel.php" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Xuất Excel</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-center">
                            <th>Mã đơn</th>
                            <th>Khách Hàng</th>
                            <th>Tổng Tiền</th>
                            <th>Ngày Lập</th>
                            <th>Phương Thức</th>
                            <th>Trạng Thái</th>
                            <th>Xoá</th>
                            <th>Chi Tiết</th> 
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_invoices > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="text-center">
                                    <td class="fw-bold text-primary">#<?php echo $row['IdHoaDon']; ?></td>
                                    <td class="text-start">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($row['HoTenKhachHang']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['SoDienThoai']); ?></small>
                                    </td>
                                    <td class="fw-bold text-dark"><?php echo number_format($row['TongTien'], 0, ',', '.'); ?>₫</td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayLap'])); ?></td>
                                    <td><small class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['PhuongThucThanhToan']); ?></small></td>
                                    
                                    <td>
                                        <?php 
                                            $st = $row['TrangThai'];
                                            $class = "";
                                            if($st == 'Đã xác nhận') $class = "text-confirmed";
                                            elseif($st == 'Đang giao') $class = "text-shipping";
                                            elseif($st == 'Đã giao' || $st == 'Hoàn thành') $class = "text-delivered";
                                            elseif($st == 'Đã Hủy') $class = "text-canceled";
                                            echo "<span class='status-label $class'>$st</span>";
                                        ?>
                                    </td>

                                    <td>
                                        <a href="?delete_id=<?php echo $row['IdHoaDon']; ?>" 
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa hóa đơn này?')"
                                        class="text-danger fs-5">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>

                                    <!-- Thêm cột chi tiết -->
                                    <td>
                                        <a href="chi_tiet_don_hang.php?id=<?php echo $row['IdHoaDon']; ?>" class="text-info fs-5">
                                            <i class="bi bi-eye"></i> <!-- Icon mắt để xem chi tiết -->
                                        </a>
                                    </td>

                                    <td>
                                        <?php 
                                            // Kiểm tra nếu trạng thái là kết thúc thì ẩn các nút cập nhật
                                            $isFinished = ($st == 'Đã giao' || $st == 'Hoàn Thành'); 
                                        ?>
                                        
                                        <?php if (!$isFinished): ?>
                                            <div class="btn-status-group d-flex justify-content-center gap-1">
                                                <a href="?update_status=Đã xác nhận&id=<?php echo $row['IdHoaDon']; ?>" class="btn btn-sm btn-outline-primary" title="Xác nhận">Xác nhận</a>
                                                <a href="?update_status=Đang giao&id=<?php echo $row['IdHoaDon']; ?>" class="btn btn-sm btn-outline-warning" title="Giao hàng">Đang giao</a>
                                                <a href="?update_status=Đã giao&id=<?php echo $row['IdHoaDon']; ?>" class="btn btn-sm btn-outline-success" title="Hoàn tất">Đã giao</a>
                                                <a href="?update_status=Đã Hủy&id=<?php echo $row['IdHoaDon']; ?>" class="btn btn-sm btn-outline-danger" title="Hủy đơn">Hủy</a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small italic"><i class="bi bi-lock-fill"></i> Đã hoàn tất</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center py-4 text-muted">Chưa có dữ liệu đơn hàng nào trong hệ thống.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>