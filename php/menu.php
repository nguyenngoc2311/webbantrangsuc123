<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('../php/config.php');

// ================= DEFAULT VALUES =================
$u_id = $_SESSION['id'] ?? null;
$u_data = null;
$avatar_src = "https://via.placeholder.com/40";
$hoten = null;

// ================= USER INFO (ONLY WHEN LOGIN) =================
if ($u_id) {
    $q = mysqli_query($conn, "SELECT anh_dai_dien, HoTen FROM nguoidungs WHERE Idnguoidung = '$u_id'");
    $u_data = mysqli_fetch_assoc($q);

    if ($u_data) {
        $hoten = $u_data['HoTen'];

        $avatar_src = "../image/" . ($u_data['anh_dai_dien'] ?? '');

        if (!empty($u_data['anh_dai_dien']) && file_exists($avatar_src)) {
            $avatar_src .= "?v=" . time();
        } else {
            $avatar_src = "https://via.placeholder.com/40";
        }
    }
}

// ================= CART =================
$unique_product_count = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $unique_product_count = count($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Jewelry</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">
        
        <a class="navbar-brand fw-bold text-danger" href="index.php">
            <i class="bi bi-gem"></i> Luxury Jewelry
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex ms-auto me-3" action="timkiem.php" method="GET">
                <div class="input-group">
                    <input class="form-control" type="search" name="keyword" placeholder="Tìm sản phẩm..." required>
                    <button class="btn btn-outline-dark" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="thongtin.php">Thông tin</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Sản phẩm</a>
                       <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=1">Nhẫn</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=2">Dây chuyền</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=3">Bông tai</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=4">Vòng tay</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=12">Charm</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=5">Bộ trang sức</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=7">Lắc tay</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=9">Mặt dây chuyền</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=10">Nhẫn cưới</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=11">Trang sức nam</a></li>
                        <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=6">Trang sức trẻ em</a></li>
                         <li><a class="dropdown-item" href="sanpham.php?danh_muc_id=13">Đồng Hồ</a></li>

                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="lichsu.php" 
                        <?php if (!isset($_SESSION['hoten'])): ?>
                            onclick="alert('Vui lòng đăng nhập để xem lịch sử đơn hàng!'); window.location.href='login.php'; return false;"
                        <?php endif; ?>
                    >Đơn hàng</a>
                </li>

                <?php if (isset($_SESSION['hoten'])): ?>
                    <?php if (isset($_SESSION['vaitro']) && $_SESSION['vaitro'] == "Admin"): ?>
                        <li class="nav-item">
                            <a class="nav-link text-primary fw-bold" href="../admin/admin.php" title="Quản trị">
                                <i class="bi bi-person-square"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <img src="<?php echo $avatar_src; ?>" 
                                alt="Profile" 
                                class="rounded-circle me-2" 
                                style="width: 32px; height: 32px; object-fit: cover; border: 1px solid #ececec;">
                            <span class="d-none d-md-block"><?php echo htmlspecialchars($u_data['HoTen']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="capnhathoso.php">Hồ sơ của tôi</a></li>
                            <li><a class="dropdown-item" href="xemhosomoi.php">Xem hồ sơ</a></li> <!-- Added this line -->
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="dangxuat.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                                    <?php else: ?>
                    <li class="nav-item border-start ms-lg-2 ps-lg-2">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-person-circle"></i> Đăng nhập
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="giohang.php" class="nav-link position-relative px-3">
                        <i class="bi bi-cart3" style="font-size: 1.2rem;"></i>
                        <?php if ($unique_product_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                <?php echo $unique_product_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="lienhe.php" title="Liên hệ">
                        <i class="bi bi-geo-alt"></i>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>