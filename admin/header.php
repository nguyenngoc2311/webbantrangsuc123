<?php 
$page = basename($_SERVER['PHP_SELF']); 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Jewelry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/header.css">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-gem-gold gold-icon"></i>
            <span>LUXURY ADMIN</span>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">CHUNG</div>
            <ul>
                <li><a href="../php/index.php" class="<?= $page == 'index.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Trang Chủ</a></li>
                <li><a href="quan_ly_san_pham.php" class="<?= $page == 'quan_ly_san_pham.php' ? 'active' : '' ?>"><i class="bi bi-diamond-half"></i> Quản Lý Sản Phẩm</a></li>
                <li><a href="quan_ly_danh_muc.php" class="<?= $page == 'quan_ly_danh_muc.php' ? 'active' : '' ?>"><i class="bi bi-grid-1x2"></i> Quản Lý Danh Mục</a></li>
                <li><a href="quan_ly_thuong_hieu.php" class="<?= $page == 'quan_ly_thuong_hieu.php' ? 'active' : '' ?>"><i class="bi bi-patch-check"></i> Quản Lý Thương Hiệu</a></li>
            </ul>

            <div class="nav-section">GIAO DỊCH</div>
            <ul>
                <li><a href="quan_ly_don_hang.php" class="<?= $page == 'quan_ly_don_hang.php' ? 'active' : '' ?>"><i class="bi bi-cart3"></i> Quản Lý Đơn Hàng</a></li>
                <li><a href="quan_ly_khach_hang.php" class="<?= $page == 'quan_ly_khach_hang.php' ? 'active' : '' ?>"><i class="bi bi-people"></i> Quản Lý Khách Hàng</a></li>
            </ul>

            <div class="nav-section">DỮ LIỆU & HỆ THỐNG</div>
            <ul>
                <li><a href="quan_ly_danh_gia.php" class="<?= $page == 'quan_ly_danh_gia.php' ? 'active' : '' ?>"><i class="bi bi-chat-left-quote"></i> Quản Lý Đánh Giá</a></li>
                <li><a href="thong_ke_ban_hang.php" class="<?= $page == 'thong_ke_ban_hang.php' ? 'active' : '' ?>"><i class="bi bi-graph-up-arrow"></i> Thống Kê Bán Hàng</a></li>
                <li><a href="quan_ly_nguoi_dung.php" class="<?= $page == 'quan_ly_nguoi_dung.php' ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> Quản Lý Tài Khoản</a></li>
            </ul>
            <div class="nav-section">Kho</div>
             <ul>
                <li><a href="quan_ly_ton_kho.php" class="<?= $page == 'quan_ly_ton_kho.php' ? 'active' : '' ?>"><i class="bi bi-chat-left-quote"></i> Quản Lý Tồn Kho</a></li>
                <li><a href="quan_ly_nhap.php" class="<?= $page == 'quan_ly_nhap.php' ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> Quản Lý Nhập </a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="../php/dangxuat.php" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i> <span>Đăng Xuất</span>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>