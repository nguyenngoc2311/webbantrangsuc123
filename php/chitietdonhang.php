<?php
session_start();
require_once('config.php');

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // 1. Lấy thông tin tổng quát của hóa đơn
    $sql = "SELECT * FROM HoaDon WHERE IdHoaDon = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        die("Hóa đơn không tồn tại.");
    }

    // 2. Truy vấn chi tiết hóa đơn (Sử dụng bảng chitiethoadon)
    $sql_details = "SELECT * FROM chitiethoadon WHERE IdHoaDon = ?";
    $stmt_details = $conn->prepare($sql_details);
    $stmt_details->bind_param("i", $order_id);
    $stmt_details->execute();
    $details_result = $stmt_details->get_result();
} else {
    die("ID hóa đơn không hợp lệ.");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết hóa đơn #<?php echo $order['IdHoaDon']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/donhang.css">
</head>
<body class="bg-light">

    <?php include('menu.php'); ?>

    <div class="container my-5" style="min-height: 70vh;">
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0 text-primary fw-bold">
                            <i class="bi bi-file-earmark-text me-2"></i>Chi tiết hóa đơn <?php echo $order['IdHoaDon']; ?>
                        </h4>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <a href="lichsu.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Quay Lại
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-primary text-white h-100 shadow-sm">
                            <small class="d-block text-uppercase fw-semibold" style="opacity: 0.8;">Tổng thanh toán</small>
                            <span class="fw-bold fs-4"><?php echo number_format($order['TongTien'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light text-uppercase shadow-sm">
                            <tr>
                                <th class="ps-3" width="100">Sản phẩm</th>
                                <th>Tên sản phẩm</th>
                                <th class="text-center">Size</th>
                                <th class="text-center">SL</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($details_result->num_rows > 0) {
                                while($detail = $details_result->fetch_assoc()) {
                                    // SỬA TẠI ĐÂY: Thay 'Gia' bằng 'DonGia' theo câu lệnh INSERT của bạn
                                    $subtotal = $detail['DonGia'] * $detail['SoLuong']; 
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <img src="../image/<?php echo $detail['HinhAnh']; ?>" class="rounded shadow-sm border" style="width: 60px; height: 60px; object-fit: cover;" alt="SP">
                                        </td>
                                        <td><span class="fw-bold text-dark"><?php echo $detail['TenSanPham']; ?></span></td>
                                        <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $detail['KichThuoc']; ?></span></td>
                                        <td class="text-center fw-bold"><?php echo $detail['SoLuong']; ?></td>
                                        <td><?php echo number_format($detail['DonGia'], 0, ',', '.'); ?>đ</td>
                                        <td><strong class="text-primary"><?php echo number_format($detail['ThanhTien'], 0, ',', '.'); ?>đ</strong></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-5 text-muted'>Không tìm thấy chi tiết sản phẩm nào.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>