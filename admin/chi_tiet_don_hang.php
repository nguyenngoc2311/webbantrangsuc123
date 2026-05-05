<?php
session_start();
require_once('../php/config.php');

if (!isset($_GET['id'])) {
    die("ID không hợp lệ.");
}

$order_id = intval($_GET['id']);

/* ===== 1. LẤY ĐƠN HÀNG ===== */
$stmt = $conn->prepare("SELECT * FROM hoadon WHERE IdHoaDon = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Đơn hàng không tồn tại.");
}

$order = $result->fetch_assoc();

/* ===== 2. LẤY CHI TIẾT ===== */
$stmt = $conn->prepare("SELECT * FROM chitiethoadon WHERE IdHoaDon = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$details = $stmt->get_result();

/* ===== 3. FUNCTION TRẠNG THÁI (DÙNG CHUNG) ===== */
function renderStatus($st) {
    if ($st == 'Chờ xử lý') {
        return "<span class='badge bg-warning text-dark'>Chờ xử lý</span>";
    } elseif ($st == 'Đã xác nhận') {
        return "<span class='badge bg-info text-white'>Đã xác nhận</span>";
    } elseif ($st == 'Đang giao') {
        return "<span class='badge bg-primary'>Đang giao</span>";
    } elseif ($st == 'Đã giao') {
        return "<span class='badge bg-success'>Đã giao</span>";
    } elseif ($st == 'Đã Hủy') {
        return "<span class='badge bg-danger'>Đã Hủy</span>";
    }
    return "<span class='badge bg-secondary'>$st</span>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chi tiết đơn hàng #<?= $order['IdHoaDon'] ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/ctdh.css">

</head>
<body>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Chi tiết đơn hàng <?= $order['IdHoaDon'] ?></h3>

        <div>
            <a href="inhoadon.php?id=<?= $order['IdHoaDon'] ?>" class="btn btn-dark btn-sm">
                <i class="bi bi-printer"></i> In hóa đơn
            </a>
            <a href="quan_ly_don_hang.php" class="btn btn-secondary">
                ← Quay lại
            </a>
        </div>
    </div>

    <div class="row">

        <!-- LEFT -->
        <div class="col-md-5">

            <!-- INFO -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">Thông tin đơn hàng</div>
                <div class="card-body">

                    <p><b>Mã đơn:</b> #<?= $order['IdHoaDon'] ?></p>

                    <p><b>Ngày đặt:</b> 
                        <?= date("d/m/Y", strtotime($order['NgayLap'])) ?>
                    </p>

                    <p><b>Tổng tiền:</b> 
                        <span class="text-danger fw-bold">
                            <?= number_format($order['TongTien'],0,',','.') ?>đ
                        </span>
                    </p>

                    <p><b>Thanh toán:</b> 
                        <?= htmlspecialchars($order['PhuongThucThanhToan']) ?>
                    </p>

                    <p><b>Trạng thái:</b><br>
                        <?= renderStatus($order['TrangThai']) ?>
                    </p>

                </div>
            </div>

            <!-- CUSTOMER -->
            <div class="card">
                <div class="card-header bg-success text-white">Khách hàng</div>
                <div class="card-body">

                    <p><b>Họ tên:</b> <?= htmlspecialchars($order['HoTenKhachHang']) ?></p>
                    <p><b>Email:</b> <?= $order['Email'] ?? '-' ?></p>
                    <p><b>Điện thoại:</b> <?= $order['SoDienThoai'] ?></p>
                    <p><b>Địa chỉ:</b> <?= $order['DiaChi'] ?></p>

                    <?php if (!empty($order['GhiChu'])): ?>
                        <p><b>Ghi chú:</b> 
                            <span class="text-warning"><?= $order['GhiChu'] ?></span>
                        </p>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- RIGHT -->
        <div class="col-md-7">

            <div class="card">
                <div class="card-header bg-warning">Sản phẩm trong đơn</div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Hình</th>
                                <th>Tên</th>
                                <th>Size</th>
                                <th>SL</th>
                                <th>Giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php
                        $stt = 1;
                        $tong = 0;

                        if ($details->num_rows > 0):
                            while ($row = $details->fetch_assoc()):
                                $thanhtien = $row['SoLuong'] * $row['DonGia'];
                                $tong += $thanhtien;
                        ?>
                            <tr>
                                <td><?= $stt++ ?></td>

                                <td>
                                    <?php if (!empty($row['HinhAnh'])): ?>
                                        <img src="../image/<?= $row['HinhAnh'] ?>" width="40">
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($row['TenSanPham']) ?></td>
                                <td><?= $row['KichThuoc'] ?></td>
                                <td><?= $row['SoLuong'] ?></td>

                                <td><?= number_format($row['DonGia'],0,',','.') ?>đ</td>

                                <td class="text-danger fw-bold">
                                    <?= number_format($thanhtien,0,',','.') ?>đ
                                </td>
                            </tr>

                        <?php endwhile; ?>

                        <!-- TOTAL -->
                        <tr class="table-warning">
                            <td colspan="6" class="text-end fw-bold">TỔNG CỘNG</td>
                            <td class="text-danger fw-bold">
                                <?= number_format($tong,0,',','.') ?>đ
                            </td>
                        </tr>

                        <?php else: ?>
                            <tr>
                                <td colspan="7">Không có sản phẩm</td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>