<?php
session_start();
require_once('../php/config.php');

if (!isset($_GET['id'])) {
    die("ID không hợp lệ.");
}

$order_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM hoadon WHERE IdHoaDon = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Đơn hàng không tồn tại.");
}

$stmt = $conn->prepare("SELECT * FROM chitiethoadon WHERE IdHoaDon = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$details = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hóa đơn #<?= $order['IdHoaDon'] ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- BOOTSTRAP ICONS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link rel="stylesheet" href="../css/inhoadon.css">

</head>

<body>

<!-- BUTTON -->
<div class="no-print text-center my-2">
    <button onclick="window.print()" class="btn btn-dark btn-sm">
        <i class="bi bi-printer"></i> In hóa đơn
    </button>

    <a href="chi_tiet_don_hang.php?id=<?= $order['IdHoaDon'] ?>" class="back-link">
        ← Quay lại
    </a>
</div>

<!-- BILL -->
<div class="bill-box">

    <div class="header">
        <h5>Luxury Jewelry</h5>
        <div><b>CỬA HÀNG TRANG SỨC CAO CẤP</b></div>
        <div>ĐT: 0368446960</div>
        <div>An Giang, Việt Nam</div>
    </div>

    <div class="info">
        <div><b>Mã HD:</b> #<?= $order['IdHoaDon'] ?></div>
        <div><b>Ngày:</b> <?= date("d/m/Y H:i", strtotime($order['NgayLap'])) ?></div>
        <div><b>Thanh toán:</b> <?= $order['PhuongThucThanhToan'] ?></div>
    </div>

    <div class="info">
        <div><b>Khách:</b> <?= $order['HoTenKhachHang'] ?></div>
        <div><b>SĐT:</b> <?= $order['SoDienThoai'] ?></div>
        <div><b>Địa chỉ:</b> <?= $order['DiaChi'] ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Sản phẩm</th>
                <th>SL</th>
                <th>Giá</th>
                <th>TT</th>
            </tr>
        </thead>

        <tbody>

        <?php
        $stt = 1;
        $tong = 0;

        while ($row = $details->fetch_assoc()):
            $thanhtien = $row['SoLuong'] * $row['DonGia'];
            $tong += $thanhtien;
        ?>

        <tr>
            <td><?= $stt++ ?></td>
            <td><?= $row['TenSanPham'] ?></td>
            <td><?= $row['SoLuong'] ?></td>
            <td><?= number_format($row['DonGia']) ?></td>
            <td><?= number_format($thanhtien) ?></td>
        </tr>

        <?php endwhile; ?>

        <tr>
            <td colspan="4" class="total">TỔNG TIỀN</td>
            <td class="total"><?= number_format($tong) ?></td>
        </tr>

        </tbody>
    </table>

    <div class="footer">
        Cảm ơn quý khách đã mua hàng <br>
    </div>

</div>

</body>
</html>