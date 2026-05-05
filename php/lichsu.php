<?php
session_start();
include 'config.php'; 

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['id_khachhang'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}

$id_khachhang = $_SESSION['id_khachhang'];

// 2. LẤY BỘ LỌC TỪ URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'dat';

// 3. XÂY DỰNG SQL (Đã thêm Subquery kiểm tra bảng danh_gia)
$sql = "SELECT 
            h.IdHoaDon, h.DiaChi, h.TongTien, h.NgayDat, h.TrangThai,
            c.TenSanPham, c.SoLuong AS SoLuongDat, c.DonGia, c.HinhAnh, c.KichThuoc,
            b.so_luong AS SoLuongKho,
            -- Kiểm tra xem ID hóa đơn này đã tồn tại trong bảng danh_gia chưa
            (SELECT COUNT(*) FROM danh_gia dg WHERE dg.id_hoadon = h.IdHoaDon) as DaDanhGia
        FROM HoaDon h 
        LEFT JOIN chitiethoadon c ON h.IdHoaDon = c.IdHoaDon 
        LEFT JOIN bien_the_san_pham b ON c.IdSanPham = b.san_pham_id AND c.KichThuoc = b.kich_co
        WHERE h.id_khachhang = ?";

// 4. THÊM ĐIỀU KIỆN LỌC
if ($filter == 'dat') {
    $sql .= " AND (h.TrangThai = 'Đã Đặt Hàng' OR h.TrangThai = 'Đã Thanh Toán')";
} elseif ($filter == 'da_xac_nhan') {
    $sql .= " AND h.TrangThai = 'Đã xác nhận'";
} elseif ($filter == 'dang_giao') {
    $sql .= " AND h.TrangThai = 'Đang giao'";
} elseif ($filter == 'da_giao') {
    $sql .= " AND (h.TrangThai = 'Đã giao' OR h.TrangThai = 'Hoàn Thành')";
} elseif ($filter == 'da_huy') {
    $sql .= " AND (h.TrangThai = 'Đã Hủy' OR h.TrangThai = 'Đã hủy')";
}

$sql .= " ORDER BY h.NgayDat DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_khachhang); 
$stmt->execute();
$result = $stmt->get_result();

// 5. GOM NHÓM DỮ LIỆU
$orders = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['IdHoaDon'];
    if (!isset($orders[$id])) {
        $orders[$id] = [
            'DiaChi' => $row['DiaChi'],
            'TongTien' => $row['TongTien'],
            'NgayDat' => $row['NgayDat'],
            'TrangThai' => trim($row['TrangThai']),
            'DaDanhGia' => (int)$row['DaDanhGia'], // Lưu lại trạng thái đánh giá
            'items' => []
        ];
    }
    if ($row['TenSanPham']) {
        $orders[$id]['items'][] = [
            'Ten' => $row['TenSanPham'],
            'SoLuong' => $row['SoLuongDat'],
            'DonGia' => $row['DonGia'],
            'HinhAnh' => $row['HinhAnh'],
            'KichThuoc' => $row['KichThuoc']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/lichsu.css"> 
</head>
<body>

<?php include('menu.php'); ?>

<div class="nav-filter mb-4 d-flex container">
    <a href="?filter=dat" class="<?php echo ($filter=='dat')?'active':''; ?>">Chờ xác nhận</a>
    <a href="?filter=da_xac_nhan" class="<?php echo ($filter=='da_xac_nhan')?'active':''; ?>">Đã xác nhận</a>
    <a href="?filter=dang_giao" class="<?php echo ($filter=='dang_giao')?'active':''; ?>">Đang giao</a>
    <a href="?filter=da_giao" class="<?php echo ($filter=='da_giao')?'active':''; ?>">Đã giao</a>
    <a href="?filter=da_huy" class="<?php echo ($filter=='da_huy')?'active':''; ?>">Đã hủy</a>
</div>

<div class="container pb-5">
    <?php if (empty($orders)): ?>
        <div class="text-center p-5 bg-white rounded shadow-sm">
            <i class="bi bi-bag-x fs-1 text-muted"></i>
            <p class="mt-2 text-muted">Bạn chưa có đơn hàng nào ở mục này.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $id => $order): ?>
        <div class="card card-order mb-3 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-0">
                <span><b>Mã đơn: #<?php echo $id; ?></b> <small class="text-muted ms-2"><?php echo date('d/m/Y H:i', strtotime($order['NgayDat'])); ?></small></span>
                <span class="status-text text-uppercase fw-bold text-primary">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <?php echo ($order['TrangThai'] == 'Đã Thanh Toán') ? 'ĐÃ THANH TOÁN (VNPAY)' : $order['TrangThai']; ?>
                </span>
            </div>

            <div class="card-body py-0">
                <?php foreach ($order['items'] as $item): ?>
                <div class="d-flex py-3 align-items-center border-bottom">
                    <img src="../image/<?php echo $item['HinhAnh']; ?>" class="product-img me-3" style="width: 80px; height: 80px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-dark"><?php echo $item['Ten']; ?></h6>
                        <div class="text-muted small">Phân loại: Size <?php echo $item['KichThuoc']; ?></div>
                        <div class="small">Số lượng: x<?php echo $item['SoLuong']; ?></div>
                    </div>
                    <div class="text-danger fw-bold"><?php echo number_format($item['DonGia'], 0, ',', '.'); ?>đ</div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card-footer bg-white py-3 border-0">
                <div class="row align-items-center">
                    <div class="col-md-7 mb-3 mb-md-0 text-muted small">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo $order['DiaChi']; ?>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <div class="mb-3">
                            <span class="text-muted small">Thành tiền:</span>
                            <span class="text-danger fw-bold fs-4 ms-2"><?php echo number_format($order['TongTien'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-md-end gap-2">
                            <?php 
                            $st = $order['TrangThai'];
                            
                            // 1. Chờ xác nhận (COD)
                            if ($st == 'Đã Đặt Hàng'): ?>
                                <a href="huy_donhang.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Xác nhận hủy đơn?')">Hủy đơn</a>
                            
                           
                            <?php elseif ($st == 'Đã Thanh Toán'): ?>
                                <span class="badge bg-info text-dark py-2"><i class="bi bi-shield-check me-1"></i> Đã thanh toán</span>

                           
                            <?php elseif ($st == 'Đang giao' || $st == 'Đã giao'): ?>
                                <a href="da_nhan_hang.php?id=<?php echo $id; ?>" class="btn btn-success btn-sm" onclick="return confirm('Bạn đã nhận được hàng?')">
                                    <i class="bi bi-check2-circle me-1"></i> Đã nhận được hàng
                                </a>

                            <?php elseif ($st == 'Hoàn Thành'): ?>
                                <?php if ($order['DaDanhGia'] == 0): ?>
                                    <a href="vietdanhgia.php?order_id=<?php echo $id; ?>" class="btn btn-warning btn-sm fw-bold">
                                        <i class="bi bi-star-fill me-1"></i> Viết đánh giá
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-success border py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> Đã đánh giá
                                    </span>
                                <?php endif; ?>
                                <a href="mua_lai.php?id=<?php echo $id; ?>" class="btn btn-outline-danger btn-sm">Mua lại</a>

                            <?php elseif (in_array($st, ['Đã Hủy', 'Đã hủy'])): ?>
                                <a href="mua_lai.php?id=<?php echo $id; ?>" class="btn btn-outline-danger btn-sm">Mua lại</a>
                            <?php endif; ?>

                            <a href="chitietdonhang.php?id=<?php echo $id; ?>" class="btn btn-light btn-sm border">Chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>
</body>
</html>