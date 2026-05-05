<?php
include('../php/config.php');
session_start();

// ================== LỌC NGÀY ==================
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

// ================== CHUẨN TRẠNG THÁI ==================
$status = "Hoàn thành";

// ================== WHERE ==================
$where = "WHERE TrangThai = '$status'";

if (!empty($start_date) && !empty($end_date)) {
    $start = mysqli_real_escape_string($conn, $start_date);
    $end   = mysqli_real_escape_string($conn, $end_date);
    $where .= " AND NgayDat BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
}

// ================== TỔNG DOANH THU ==================
$sql_total = "SELECT SUM(TongTien) AS total FROM hoadon $where";
$result_total = mysqli_query($conn, $sql_total);
$revenue = mysqli_fetch_assoc($result_total)['total'] ?? 0;

// ================== THEO THÁNG ==================
$sql_month = "
SELECT MONTH(NgayDat) AS month, SUM(TongTien) AS revenue
FROM hoadon
WHERE YEAR(NgayDat) = YEAR(CURDATE())
AND TrangThai = '$status'
GROUP BY MONTH(NgayDat)
ORDER BY MONTH(NgayDat)
";
$result_month = mysqli_query($conn, $sql_month);

// ================== THEO NĂM ==================
$sql_year = "
SELECT YEAR(NgayDat) AS year, SUM(TongTien) AS revenue
FROM hoadon
WHERE TrangThai = '$status'
GROUP BY YEAR(NgayDat)
ORDER BY YEAR(NgayDat)
";
$result_year = mysqli_query($conn, $sql_year);

// ================== DOANH THU THEO NGÀY ==================
$sql_day = "
SELECT DATE(NgayDat) AS day, SUM(TongTien) AS revenue
FROM hoadon
WHERE TrangThai = '$status'
GROUP BY DATE(NgayDat)
ORDER BY DATE(NgayDat) DESC
";
$result_day = mysqli_query($conn, $sql_day);

// ================== DANH SÁCH ==================
$sql_list = "SELECT * FROM hoadon $where ORDER BY NgayDat DESC";
$result_list = mysqli_query($conn, $sql_list);
$total_invoices = mysqli_num_rows($result_list);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Doanh Thu | Luxury Jewelry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/thongke.css">
</head>
<body>

<?php include('header.php'); ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-uppercase">
                <i class="bi bi-graph-up-arrow me-2 text-warning"></i>Thống kê doanh thu
            </h3>
            <span class="badge bg-light text-dark shadow-sm p-2">
                <i class="bi bi-calendar3 me-1"></i> Hôm nay: <?php echo date('d/m/Y'); ?>
            </span>
        </div>

        <div class="filter-wrapper mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Từ ngày</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Đến ngày</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <a href="thong_ke_ban_hang.php" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                    <a href="export_excel.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                       class="btn btn-success px-4 float-end">
                        <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                    </a>
                </div>
            </form>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="card card-stat card-revenue">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50">Tổng doanh thu</p>
                                <h2 class="mb-0 fw-bold"><?php echo number_format($revenue,0,',','.'); ?>đ</h2>
                            </div>
                            <div class="icon-box"><i class="bi bi-currency-dollar"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card card-stat border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Số đơn hoàn thành</p>
                                <h2 class="mb-0 fw-bold text-primary"><?php echo $total_invoices; ?></h2>
                            </div>
                            <div class="icon-box bg-primary-light text-primary"><i class="bi bi-cart-check"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-bar-chart-line me-2"></i>Doanh thu theo tháng</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tháng</th>
                                    <th class="text-end pe-4">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($m = mysqli_fetch_assoc($result_month)): ?>
                                <tr>
                                    <td class="ps-4">Tháng <?php echo $m['month']; ?></td>
                                    <td class="text-end pe-4 fw-bold text-primary">
                                        <?php echo number_format($m['revenue'],0,',','.'); ?>đ
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-graph-up me-2"></i>Doanh thu theo năm</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Năm</th>
                                    <th class="text-end pe-4">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($y = mysqli_fetch_assoc($result_year)): ?>
                                <tr>
                                    <td class="ps-4"><?php echo $y['year']; ?></td>
                                    <td class="text-end pe-4 fw-bold text-success">
                                        <?php echo number_format($y['revenue'],0,',','.'); ?>đ
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-calendar-day me-2"></i>Doanh thu theo ngày</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Ngày</th>
                                    <th class="text-end pe-4">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($d = mysqli_fetch_assoc($result_day)): ?>
                                <tr>
                                    <td class="ps-4"><?php echo date('d/m/Y', strtotime($d['day'])); ?></td>
                                    <td class="text-end pe-4 fw-bold text-info">
                                        <?php echo number_format($d['revenue'], 0, ',', '.'); ?>đ
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-list-check me-2"></i>Chi tiết hóa đơn hoàn thành</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th class="text-center">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($total_invoices > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($result_list)): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-secondary">#<?php echo $row['IdHoaDon']; ?></td>
                                            <td>
                                                <div class="fw-bold"><?php echo $row['HoTenKhachHang']; ?></div>
                                                <div class="small text-muted"><?php echo $row['SoDienThoai']; ?></div>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($row['NgayDat'])); ?></td>
                                            <td class="fw-bold text-dark">
                                                <?php echo number_format($row['TongTien'],0,',','.'); ?>đ
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill bg-success-light text-success px-3">
                                                    <i class="bi bi-check-circle me-1"></i> Hoàn thành
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">Không tìm thấy dữ liệu phù hợp</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>