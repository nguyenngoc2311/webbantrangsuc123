<?php
include '../php/config.php';

function formatPhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if ($phone === '') {
        return '';
    }
    if (strlen($phone) == 10) {
        return substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7, 3);
    }
    return $phone;
}

function formatDate($datetime) {
    if (empty($datetime) || $datetime === '0000-00-00' || $datetime === '0000-00-00 00:00:00') {
        return '';
    }
    return date('d/m/Y', strtotime($datetime));
}

function formatMoney($value) {
    return number_format((float)$value, 0, ',', '.') . ' ₫';
}

$sql = "SELECT * FROM hoadon";
$result = $conn->query($sql);

$filename = "Bao_Cao_Hoa_Don_" . date('d-m-Y') . ".xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
echo "\xEF\xBB\xBF";
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;font-family:Arial, sans-serif;">
    <tr style="background:#4CAF50;color:#ffffff;font-weight:bold;">
        <th>Mã HĐ</th>
        <th>Ngày Lập</th>
        <th>Tổng Tiền</th>
        <th>ID Khách</th>
        <th>Họ Tên Khách Hàng</th>
        <th>Email</th>
        <th>Địa Chỉ</th>
        <th>Số Điện Thoại</th>
        <th>Phương Thức TT</th>
        <th>Ngày Đặt</th>
        <th>Trạng Thái</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['IdHoaDon']; ?></td>
                <td style="mso-number-format:'\@';"><?php echo formatDate($row['NgayLap']); ?></td>
                <td><?php echo formatMoney($row['TongTien']); ?></td>
                <td><?php echo $row['id_khachhang']; ?></td>
                <td><?php echo htmlspecialchars($row['HoTenKhachHang']); ?></td>
                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                <td><?php echo htmlspecialchars($row['DiaChi']); ?></td>
                <td style="mso-number-format:'\@';"><?php echo formatPhone($row['SoDienThoai']); ?></td>
                <td><?php echo htmlspecialchars($row['PhuongThucThanhToan']); ?></td>
                <td style="mso-number-format:'\@';"><?php echo formatDate($row['NgayDat']); ?></td>
                <td><?php echo htmlspecialchars($row['TrangThai']); ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="11" style="text-align:center;">Không có dữ liệu</td>
        </tr>
    <?php endif; ?>
</table>
</body>
</html>
<?php
$conn->close();
exit;
