<?php
include "config.php"; 

// 1. TRUY VẤN CHẤT LIỆU
$sql_cl = "SELECT DISTINCT chat_lieu FROM sanpham WHERE chat_lieu IS NOT NULL AND chat_lieu != ''";
$result_cl = mysqli_query($conn, $sql_cl);

// 2. LẤY DỮ LIỆU LỌC
$id_dm = isset($_GET['danh_muc_id']) ? mysqli_real_escape_string($conn, $_GET['danh_muc_id']) : '';
$gia = isset($_GET['gia']) ? $_GET['gia'] : '';
$sao = isset($_GET['sao']) ? (int)$_GET['sao'] : ''; 
$chatlieu = isset($_GET['chatlieu']) ? mysqli_real_escape_string($conn, $_GET['chatlieu']) : '';

// ===== PHÂN TRANG =====
$limit = 25; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// ===== QUERY CHÍNH =====
$sql = "SELECT sp.*, AVG(d.so_sao) AS sao_trung_binh 
        FROM sanpham sp 
        LEFT JOIN danh_gia d ON sp.id = d.san_pham_id 
        WHERE 1=1
        AND sp.trang_thai = 1"; // 🔥 THÊM DÒNG NÀY

if ($id_dm != '') {
    $sql .= " AND sp.danh_muc_id = '$id_dm'";
}

if ($gia == 'duoi1tr') {
    $sql .= " AND sp.gia < 1000000"; 
} elseif ($gia == '1tr-8tr') {
    $sql .= " AND sp.gia BETWEEN 1000000 AND 8000000"; 
} elseif ($gia == 'tren8tr') {
    $sql .= " AND sp.gia > 8000000"; 
}

if ($chatlieu != '') {
    $sql .= " AND sp.chat_lieu = '$chatlieu'";
}

$sql .= " GROUP BY sp.id";

if ($sao != '') {
    if ($sao == 5) {
        $sql .= " HAVING sao_trung_binh >= 5";
    } else {
        $sql .= " HAVING sao_trung_binh >= $sao AND sao_trung_binh < " . ($sao + 1);
    }
}

// LIMIT
$sql .= " ORDER BY sp.id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// ===== ĐẾM TỔNG =====
$sql_count = "SELECT sp.id
              FROM sanpham sp 
              LEFT JOIN danh_gia d ON sp.id = d.san_pham_id 
              WHERE 1=1
              AND sp.trang_thai = 1"; // 🔥 THÊM DÒNG NÀY

if ($id_dm != '') {
    $sql_count .= " AND sp.danh_muc_id = '$id_dm'";
}

if ($gia == 'duoi1tr') {
    $sql_count .= " AND sp.gia < 1000000"; 
} elseif ($gia == '1tr-8tr') {
    $sql_count .= " AND sp.gia BETWEEN 1000000 AND 8000000"; 
} elseif ($gia == 'tren8tr') {
    $sql_count .= " AND sp.gia > 8000000"; 
}

if ($chatlieu != '') {
    $sql_count .= " AND sp.chat_lieu = '$chatlieu'";
}

$sql_count .= " GROUP BY sp.id";

if ($sao != '') {
    if ($sao == 5) {
        $sql_count .= " HAVING AVG(d.so_sao) >= 5";
    } else {
        $sql_count .= " HAVING AVG(d.so_sao) >= $sao AND AVG(d.so_sao) < " . ($sao + 1);
    }
}

$result_count = mysqli_query($conn, $sql_count);
$total_records = mysqli_num_rows($result_count);
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm - Luxury Jewelry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sanpham.css">
</head>
<body class="bg-light">

<?php include 'menu.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4 fw-bold text-gold">BỘ SƯU TẬP TRANG SỨC</h2>

    <!-- FILTER BADGE -->
    <div class="mb-4 d-flex flex-wrap align-items-center gap-2">
        <span class="text-secondary small fw-bold">Bộ lọc:</span>

        <?php if (!$gia && !$sao && !$chatlieu): ?>
            <span class="text-muted small italic">Tất cả sản phẩm</span>
        <?php else: ?>

            <?php if ($gia): ?>
                <a href="?<?php echo http_build_query(array_diff_key($_GET, ['gia'=>''])); ?>" class="filter-badge">
                    Giá: <?php echo ($gia=='duoi1tr'?'Dưới 1tr':($gia=='1tr-8tr'?'1-8tr':'Trên 8tr')); ?>
                </a>
            <?php endif; ?>

            <?php if ($sao): ?>
                <a href="?<?php echo http_build_query(array_diff_key($_GET, ['sao'=>''])); ?>" class="filter-badge">
                    <?php echo $sao; ?>+ Sao
                </a>
            <?php endif; ?>

            <?php if ($chatlieu): ?>
                <a href="?<?php echo http_build_query(array_diff_key($_GET, ['chatlieu'=>''])); ?>" class="filter-badge">
                    <?php echo htmlspecialchars($chatlieu); ?>
                </a>
            <?php endif; ?>

            <a href="sanpham.php" class="text-danger small ms-2">Xóa tất cả</a>
        <?php endif; ?>
    </div>

    <!-- FILTER DROPDOWN -->
    <div class="filter-bar shadow-sm p-3 mb-5 d-flex align-items-center flex-wrap gap-3 bg-white rounded">
        <span class="fw-bold">Bộ lọc:</span>

        <!-- Giá -->
        <div class="dropdown">
            <button class="btn btn-outline-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                Khoảng giá
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET,['gia'=>'duoi1tr'])); ?>">Dưới 1 triệu</a></li>
                <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET,['gia'=>'1tr-8tr'])); ?>">1 - 8 triệu</a></li>
                <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET,['gia'=>'tren8tr'])); ?>">Trên 8 triệu</a></li>
            </ul>
        </div>

        <!-- Sao -->
        <div class="dropdown">
            <button class="btn btn-outline-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                Đánh giá
            </button>
            <ul class="dropdown-menu">
                <?php for($i=5;$i>=1;$i--): ?>
                <li>
                    <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET,['sao'=>$i])); ?>">
                        <?php echo $i; ?> sao trở lên
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </div>

        <!-- Chất liệu -->
        <div class="dropdown">
            <button class="btn btn-outline-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                Chất liệu
            </button>
            <ul class="dropdown-menu">
                <?php while($row_cl = mysqli_fetch_assoc($result_cl)): ?>
                <li>
                    <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET,['chatlieu'=>$row_cl['chat_lieu']])); ?>">
                        <?php echo htmlspecialchars($row_cl['chat_lieu']); ?>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- LIST PRODUCT -->
    <div class="row g-4">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-6 col-md-4" style="width:20%;">
                    <a href="chitiet.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                        <div class="card h-100 product-card shadow-sm">
                            <div style="height:250px; overflow:hidden;">
                                <img src="../image/<?php echo $row['hinh_anh']; ?>" class="w-100 h-100 object-fit-cover">
                            </div>
                            <div class="card-body text-center">
                                <h6><?php echo $row['ten_sp']; ?></h6>
                                <div class="text-danger fw-bold">
                                    <?php echo number_format($row['gia'],0,',','.'); ?>đ
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">Không có sản phẩm</div>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">

            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page-1])); ?>">«</a>
            </li>
            <?php endif; ?>

            <?php for ($i=1; $i<=$total_pages; $i++): ?>
            <li class="page-item <?php echo ($i==$page)?'active':''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$i])); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$page+1])); ?>">»</a>
            </li>
            <?php endif; ?>

        </ul>
    </nav>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>
</body>
</html>