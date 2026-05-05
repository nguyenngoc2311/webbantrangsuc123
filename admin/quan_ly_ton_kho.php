<?php
include '../php/config.php'; 

$nguong_canh_bao = 8; 
$limit = 35; 

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// --- FILTER ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$size_filter = isset($_GET['size']) ? $_GET['size'] : '';

// =====================
// WHERE LIST (CÓ STATUS)
// =====================
$where_list = "WHERE 1=1";

// =====================
// WHERE STAT (KHÔNG STATUS)
// =====================
$where_stat = "WHERE 1=1";

// SEARCH
if (!empty($search)) {
    $where_list .= " AND (s.ten_sp LIKE '%$search%' OR dm.ten_danh_muc LIKE '%$search%')";
    $where_stat .= " AND (s.ten_sp LIKE '%$search%' OR dm.ten_danh_muc LIKE '%$search%')";
}

// CATEGORY
if ($category_filter > 0) {
    $where_list .= " AND s.danh_muc_id = $category_filter";
    $where_stat .= " AND s.danh_muc_id = $category_filter";
}

// SIZE
if (!empty($size_filter)) {
    $where_list .= " AND bt.kich_co LIKE '%$size_filter%'";
    $where_stat .= " AND bt.kich_co LIKE '%$size_filter%'";
}

// =====================
// STATUS (CHỈ LIST)
// =====================
if ($status_filter == 'con') {
    $where_list .= " AND bt.so_luong > $nguong_canh_bao";
} elseif ($status_filter == 'sap') {
    $where_list .= " AND bt.so_luong > 0 AND bt.so_luong <= $nguong_canh_bao";
} elseif ($status_filter == 'het') {
    $where_list .= " AND bt.so_luong = 0";
}

// =====================
// DANH MỤC
// =====================
$danhmuc_sql = "SELECT id, ten_danh_muc FROM danhmuc WHERE trang_thai = 1"; 
$danhmuc_result = mysqli_query($conn, $danhmuc_sql);

// =====================
// SIZE
// =====================
$size_sql = "SELECT DISTINCT kich_co FROM bien_the_san_pham"; 
$size_result = mysqli_query($conn, $size_sql);

// =====================
// PAGE
// =====================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// =====================
// COUNT (LIST)
// =====================
$total_sql = "SELECT COUNT(*) as total 
FROM bien_the_san_pham bt 
INNER JOIN sanpham s ON bt.san_pham_id = s.id 
INNER JOIN danhmuc dm ON s.danh_muc_id = dm.id
$where_list";

$total_result = mysqli_query($conn, $total_sql);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);

// =====================
// DATA LIST
// =====================
$sql = "SELECT s.id, s.ten_sp, s.gia, s.hinh_anh, 
               bt.kich_co, bt.so_luong,
               MAX(pn.ngaynhap) as ngay_nhap_cuoi
FROM bien_the_san_pham bt
INNER JOIN sanpham s ON bt.san_pham_id = s.id
INNER JOIN danhmuc dm ON s.danh_muc_id = dm.id
LEFT JOIN chitietphieunhap ctpn ON s.id = ctpn.idsp
LEFT JOIN phieunhap pn ON ctpn.phieu_nhap_id = pn.id
$where_list
GROUP BY s.id, bt.kich_co
ORDER BY bt.so_luong ASC, s.id DESC
LIMIT $limit OFFSET " . (($page - 1) * $limit);

$result = mysqli_query($conn, $sql);

// =====================
// STAT (KHÔNG STATUS)
// =====================
$stat_sql = "SELECT 
    SUM(CASE WHEN bt.so_luong = 0 THEN 1 ELSE 0 END) AS het_hang,
    SUM(CASE WHEN bt.so_luong > 0 AND bt.so_luong <= $nguong_canh_bao THEN 1 ELSE 0 END) AS sap_het,
    SUM(CASE WHEN bt.so_luong > $nguong_canh_bao THEN 1 ELSE 0 END) AS con_hang
FROM bien_the_san_pham bt
INNER JOIN sanpham s ON bt.san_pham_id = s.id
INNER JOIN danhmuc dm ON s.danh_muc_id = dm.id
$where_stat";

$stat_result = mysqli_query($conn, $stat_sql);
$stat = mysqli_fetch_assoc($stat_result);

$so_het = $stat['het_hang'] ?? 0;
$so_sap_het = $stat['sap_het'] ?? 0;
$so_con = $stat['con_hang'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tồn kho sắp hết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/tonkho.css">
</head>
<body>
    <?php include 'header.php'; ?>
       
    <div class="main-wrapper">
        <main class="content-area">
            <div class="container-fluid px-4 py-4">
                <div class="card shadow-sm border-0">
                    <div class="row px-4 pt-3">
                            <div class="col-md-4 mb-2">
                                 <?php $query['status'] = 'con'; ?>
                                <a href="?<?= http_build_query($query) ?>" class="text-decoration-none">
                                    <div class="alert alert-success mb-0 py-2">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Còn hàng: <strong><?php echo $so_con; ?></strong>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <?php $query['status'] = 'sap'; ?>
                                <a href="?<?= http_build_query($query) ?>" class="text-decoration-none">
                                    <div class="alert alert-warning mb-0 py-2">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Sắp hết: <strong><?php echo $so_sap_het; ?></strong>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <?php $query['status'] = 'het'; ?>
                                <a href="?<?= http_build_query($query) ?>" class="text-decoration-none">
                                    <div class="alert alert-danger mb-0 py-2">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Hết hàng: <strong><?php echo $so_het; ?></strong>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-lg-3">
                                <h2 class="mb-0 h5 fw-bold text-uppercase text-nowrap">Tồn kho sắp hết</h2>
                            </div>
                            <div class="col-lg-9">
                                <form action="" method="GET" class="toolbar-container d-flex gap-2 justify-content-end">
                                    <select name="category_id" class="form-select form-select-sm" style="max-width: 200px;" onchange="this.form.submit()">
                                        <option value="0">Tất cả danh mục</option>
                                        <?php while($dm = mysqli_fetch_assoc($danhmuc_result)): ?>
                                            <option value="<?php echo $dm['id']; ?>" <?php echo ($category_filter == $dm['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dm['ten_danh_muc']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>

                                    <div class="input-group input-group-sm" style="max-width: 200px;">
                                        <input type="text" name="size" class="form-control" 
                                               placeholder="Nhập kích cỡ..." 
                                               value="<?php echo htmlspecialchars($size_filter); ?>"/>
                                    </div>

                                    <div class="input-group input-group-sm" style="max-width: 250px;">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Tìm tên sản phẩm..." 
                                               value="<?php echo htmlspecialchars($search); ?>"/>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>

                                    <a href="?" class="btn btn-sm btn-outline-secondary text-nowrap" title="Reset bộ lọc">
                                        <i class="bi bi-arrow-clockwise"></i> Làm mới
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted bg-light" style="font-size: 13px;">
                                        <th class="ps-4">ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Thông tin sản phẩm</th>
                                        <th>Giá bán</th>
                                        <th>Kích cỡ (Tồn)</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($result) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td class="ps-4 text-muted">#<?php echo $row['id']; ?></td>
                                                <td>
                                                    <img src="../image/<?php echo htmlspecialchars($row['hinh_anh']); ?>" 
                                                         style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;"
                                                         onerror="this.src='https://via.placeholder.com/45'">
                                                </td>
                                                <td>
                                                    <div class="product-name fw-bold"><?php echo htmlspecialchars($row['ten_sp']); ?></div>
                                                    <div class="product-code" style="font-size: 0.85rem; color: #6c757d;">Mã: PNJDH<?php echo $row['id']; ?></div>
                                                </td>
                                                <td>
                                                    <div class="price-text fw-bold text-dark"><?php echo number_format($row['gia'], 0, ',', '.'); ?>đ</div>
                                                </td>
                                                <td>
                                                    <span class="badge-size-custom <?php echo ($row['so_luong'] <= $nguong_canh_bao) ? 'badge-danger-custom' : ''; ?>">
                                                        Size: <?php echo $row['kich_co']; ?> — <strong><?php echo $row['so_luong']; ?></strong>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $sl = $row['so_luong'];
                                                    if ($sl == 0) {
                                                        echo '<span class="badge bg-danger rounded-pill badge-status"><i class="bi bi-x-circle me-1"></i> Hết hàng</span>';
                                                    } elseif ($sl <= $nguong_canh_bao && $sl > 0) {
                                                        echo '<span class="badge bg-warning text-dark rounded-pill badge-status"><i class="bi bi-exclamation-triangle me-1"></i> Sắp hết</span>';
                                                    } else {
                                                        echo '<span class="badge bg-success rounded-pill badge-status"><i class="bi bi-check-circle me-1"></i> Còn hàng</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="nhap_them.php?id=<?php echo $row['id']; ?>&size=<?php echo urlencode($row['kich_co']); ?>" 
                                                       class="btn btn-sm btn-outline-primary border-0" title="Nhập hàng">
                                                       <i class="bi bi-plus-circle fs-5"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bi bi-box-seam d-block fs-1 mb-2"></i>
                                                Không tìm thấy sản phẩm nào cần cảnh báo.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Phân trang đơn giản -->
                    <div class="card-footer bg-white py-4 border-top">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_filter; ?>&size=<?php echo urlencode($size_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>