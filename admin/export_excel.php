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

// lọc cho danh sách hóa đơn (giữ nguyên nếu bạn cần)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$where_clause = " WHERE 1=1";
if (!empty($start_date) && !empty($end_date)) {
    $start_date_esc = mysqli_real_escape_string($conn, $start_date);
    $end_date_esc = mysqli_real_escape_string($conn, $end_date);
    $where_clause .= " AND NgayDat BETWEEN '$start_date_esc 00:00:00' AND '$end_date_esc 23:59:59'";
}

// header Excel
$filename = "Bao_Cao_Tong_Hop_" . date('d-m-Y') . ".xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
echo "\xEF\xBB\xBF";
?>

<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    table { border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 5px; }
    th { background: #ddd; }
    h2, h3 { color: #333; }
</style>
</head>

<body>

<h2>Báo cáo tổng hợp</h2>
<p>Ngày xuất: <?php echo date('d/m/Y'); ?></p>

<!-- ================= DOANH THU THÁNG ================= -->
<h3>Doanh thu theo tháng (Năm <?php echo date('Y'); ?>)</h3>
<table>
<tr>
    <th>Tháng</th>
    <th>Doanh Thu</th>
</tr>

<?php
$sql_month = "SELECT MONTH(NgayDat) as month, SUM(TongTien) as monthly_revenue
              FROM hoadon 
              WHERE YEAR(NgayDat) = YEAR(CURDATE()) AND TrangThai <> 'Đã Hủy'
              GROUP BY MONTH(NgayDat)
              ORDER BY MONTH(NgayDat) ASC";

$res_month = $conn->query($sql_month);

while ($m = $res_month->fetch_assoc()):
?>
<tr>
    <td>Tháng <?php echo $m['month']; ?></td>
    <td><?php echo formatMoney($m['monthly_revenue']); ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- ================= DOANH THU NĂM ================= -->
<h3>Doanh thu theo năm</h3>
<table>
<tr>
    <th>Năm</th>
    <th>Doanh Thu</th>
</tr>

<?php
$sql_year = "SELECT YEAR(NgayDat) as year, SUM(TongTien) as yearly_revenue
             FROM hoadon
             WHERE TrangThai <> 'Đã Hủy'
             GROUP BY YEAR(NgayDat)
             ORDER BY YEAR(NgayDat) DESC";

$res_year = $conn->query($sql_year);

while ($y = $res_year->fetch_assoc()):
?>
<tr>
    <td>Năm <?php echo $y['year']; ?></td>
    <td><?php echo formatMoney($y['yearly_revenue']); ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- ================= DOANH THU NGÀY (ĐÃ FIX) ================= -->
<h3>Doanh thu hôm nay</h3>
<table>
<tr>
    <th>Ngày</th>
    <th>Doanh Thu</th>
</tr>

<?php
$sql_day = "SELECT DATE(NgayDat) as day, SUM(TongTien) as daily_revenue
            FROM hoadon
            WHERE TrangThai <> 'Đã Hủy'
            AND DATE(NgayDat) = CURDATE()
            GROUP BY DATE(NgayDat)";

$res_day = $conn->query($sql_day);

if (!$res_day) {
    die("Lỗi SQL: " . $conn->error);
}

if ($res_day->num_rows == 0) {
    echo "<tr><td colspan='2'>Hôm nay chưa có doanh thu</td></tr>";
}

while ($d = $res_day->fetch_assoc()):
?>
<tr>
    <td style="mso-number-format:'\@';">
        <?php echo formatDate($d['day']); ?>
    </td>
    <td>
        <?php echo formatMoney($d['daily_revenue']); ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<!-- ================= DANH SÁCH HÓA ĐƠN ================= -->
<h3>Chi tiết danh sách hóa đơn</h3>
<table>
<tr>
    <th>Mã HĐ</th>
    <th>Khách Hàng</th>
    <th>SĐT</th>
    <th>Địa Chỉ</th>
    <th>Ngày Đặt</th>
    <th>Tổng Tiền</th>
    <th>Trạng Thái</th>
</tr>

<?php
$sql_list = "SELECT * FROM hoadon $where_clause ORDER BY NgayDat DESC";
$res_list = $conn->query($sql_list);

$total_revenue = 0;

while ($row = $res_list->fetch_assoc()):
    $total_revenue += $row['TongTien'];
?>
<tr>
    <td>#<?php echo $row['IdHoaDon']; ?></td>
    <td><?php echo htmlspecialchars($row['HoTenKhachHang']); ?></td>
    <td style="mso-number-format:'\@';"><?php echo formatPhone($row['SoDienThoai']); ?></td>
    <td><?php echo htmlspecialchars($row['DiaChi']); ?></td>
    <td style="mso-number-format:'\@';"><?php echo formatDate($row['NgayDat']); ?></td>
    <td><?php echo formatMoney($row['TongTien']); ?></td>
    <td><?php echo htmlspecialchars($row['TrangThai']); ?></td>
</tr>
<?php endwhile; ?>

<tr>
    <td colspan="5"><strong>Tổng doanh thu</strong></td>
    <td colspan="2"><strong><?php echo formatMoney($total_revenue); ?></strong></td>
</tr>

</table>

</body>
</html>

<?php
$conn->close();
exit;