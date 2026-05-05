<?php
session_start();
include '../php/config.php';

// ===== LẤY ID =====
$phieu_nhap_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ===== QUERY =====
$sql = "SELECT id, phieu_nhap_id, idsp, tensp, so_luong, don_gia, tong_gia_tri, kich_thuoc 
        FROM chitietphieunhap 
        WHERE phieu_nhap_id = $phieu_nhap_id";

$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu Nhập</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/ctpn.css">
</head>

<body>

<?php include 'header.php'; ?>

<div class="container main-content mt-4">

    <!-- THÔNG BÁO -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-0">CHI TIẾT PHIẾU NHẬP</h2>
            <span class="badge bg-secondary">Mã phiếu: <?= $phieu_nhap_id ?></span>
        </div>
        <a href="quan_ly_nhap.php" class="btn btn-success">
            <i class="bi bi-arrow-left-circle"></i> Quay lại
        </a>
    </div>

    <!-- TABLE -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-secondary text-center">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Mã SP</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Kích thước</th>
                    <th>Số lượng</th>
                    <th class="text-end">Đơn giá</th>
                    <th class="text-end pe-4">Thành tiền</th>
                </tr>
                </thead>

                <tbody>

                <?php
                if ($result && $result->num_rows > 0) {

                    $grand_total = 0;

                    while ($row = $result->fetch_assoc()) {

                        $grand_total += $row['tong_gia_tri'];

                        echo "<tr class='text-center'>";

                        echo "<td class='ps-4'>{$row['id']}</td>";

                        echo "<td>
                                <span class='badge bg-secondary-subtle text-secondary border'>
                                    {$row['idsp']}
                                </span>
                              </td>";

                        echo "<td class='fw-bold'>" . htmlspecialchars($row['tensp']) . "</td>";

                        // 👉 KÍCH THƯỚC
                        echo "<td>
                                <span class='badge bg-info-subtle text-dark border'>
                                    " . htmlspecialchars($row['kich_thuoc']) . "
                                </span>
                              </td>";

                        echo "<td>{$row['so_luong']}</td>";

                        echo "<td class='text-end'>" . number_format($row['don_gia'], 0, ',', '.') . " đ</td>";

                        echo "<td class='text-end pe-4 fw-bold'>" . number_format($row['tong_gia_tri'], 0, ',', '.') . " đ</td>";

                        echo "</tr>";
                    }

                    // ===== TỔNG =====
                    echo "<tr class='table-info'>";
                    echo "<td colspan='6' class='text-end fw-bold py-3'>Tổng thanh toán:</td>";
                    echo "<td class='text-end pe-4 fw-bold text-danger py-3 fs-5'>" . number_format($grand_total, 0, ',', '.') . " đ</td>";
                    echo "</tr>";

                } else {
                    echo "<tr>
                            <td colspan='7' class='text-center py-5 text-muted'>
                                <i class='bi bi-inbox fs-1 d-block mb-2'></i>
                                Không có dữ liệu
                            </td>
                          </tr>";
                }
                ?>

                </tbody>

            </table>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="mt-3 text-muted small">
        <i class="bi bi-info-circle"></i>
        Dữ liệu được lấy từ hệ thống kho
    </div>

</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>