<?php
include('../php/config.php');

// --- PHÂN TRANG ---
$limit = 15; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- SEARCH + FILTER ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_dm = isset($_GET['danh_muc_id']) ? (int)$_GET['danh_muc_id'] : 0;

$where = [];
if ($search != '') {
    $where[] = "(sp.ten_sp LIKE '%$search%' OR sp.ma_sp LIKE '%$search%')";
}
if ($filter_dm > 0) {
    $where[] = "sp.danh_muc_id = $filter_dm";
}
$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// --- COUNT ---
$total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM sanpham sp $where_sql"))[0];
$total_pages = ceil($total_rows / $limit);

// --- DATA ---
$sql = "SELECT sp.*, 
        (SELECT GROUP_CONCAT(CONCAT(kich_co,' (',so_luong,')') SEPARATOR ', ')
         FROM bien_the_san_pham WHERE san_pham_id = sp.id) AS danh_sach_size
        FROM sanpham sp
        $where_sql
        ORDER BY sp.id DESC
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);



// ================== SET STATUS ==================
if (isset($_GET['set_status'])) {
    $id = (int)$_GET['set_status'];
    $value = (int)$_GET['value'];

    mysqli_query($conn, "UPDATE sanpham SET trang_thai = $value WHERE id = $id");
    header("Location: quan_ly_san_pham.php");
    exit;
}


// ================== DELETE ==================
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    mysqli_query($conn, "DELETE FROM sanpham WHERE id=$id");
    mysqli_query($conn, "DELETE FROM bien_the_san_pham WHERE san_pham_id=$id");

    header("Location: quan_ly_san_pham.php");
    exit;
}


// ================== ADD ==================
if (isset($_POST['add_product'])) {
    $ten_sp = mysqli_real_escape_string($conn, $_POST['ten_sp']);
    $ma_sp = mysqli_real_escape_string($conn, $_POST['ma_sp']);
    $gia = (int)$_POST['gia'];
    $gia_km = ($_POST['gia_khuyen_mai'] !== '') ? (int)$_POST['gia_khuyen_mai'] : NULL;
    $chat_lieu = mysqli_real_escape_string($conn, $_POST['chat_lieu']);
    $da_chinh = mysqli_real_escape_string($conn, $_POST['da_chinh']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $danh_muc_id = (int)$_POST['danh_muc_id'];
    $thuong_hieu_id = (int)$_POST['thuong_hieu_id'];

    // upload ảnh
    $hinh_anh = "";
        if ($_FILES['hinh_anh']['error'] == 0) {
            $hinh_anh = basename($_FILES['hinh_anh']['name']); // lấy tên gốc
            move_uploaded_file($_FILES['hinh_anh']['tmp_name'], '../image/' . $hinh_anh);
        }
    $gia_km_sql = $gia_km === NULL ? "NULL" : $gia_km;

    mysqli_query($conn, "INSERT INTO sanpham 
        (ten_sp, ma_sp, gia, gia_khuyen_mai, chat_lieu, da_chinh, mo_ta, hinh_anh, danh_muc_id, thuong_hieu_id, trang_thai)
        VALUES ('$ten_sp','$ma_sp',$gia,$gia_km_sql,'$chat_lieu','$da_chinh','$mo_ta','$hinh_anh',$danh_muc_id,$thuong_hieu_id,1)");

    $last_id = mysqli_insert_id($conn);

    if (isset($_POST['kich_co_arr'])) {
        foreach ($_POST['kich_co_arr'] as $i => $k) {
            if ($k != '') {
                $k = mysqli_real_escape_string($conn, $k);
                $sl = (int)$_POST['so_luong_arr'][$i];
                mysqli_query($conn, "INSERT INTO bien_the_san_pham VALUES (NULL,$last_id,'$k',$sl)");
            }
        }
    }

    header("Location: quan_ly_san_pham.php");
    exit;
}


// ================== UPDATE ==================
if (isset($_POST['edit_product_submit'])) {
    $id = (int)$_POST['id'];

    $ten_sp = mysqli_real_escape_string($conn, $_POST['ten_sp']);
    $ma_sp = mysqli_real_escape_string($conn, $_POST['ma_sp']);
    $gia = (int)$_POST['gia'];
    $gia_km = ($_POST['gia_khuyen_mai'] !== '') ? (int)$_POST['gia_khuyen_mai'] : NULL;
    $chat_lieu = mysqli_real_escape_string($conn, $_POST['chat_lieu']);
    $da_chinh = mysqli_real_escape_string($conn, $_POST['da_chinh']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $danh_muc_id = (int)$_POST['danh_muc_id'];
    $thuong_hieu_id = (int)$_POST['thuong_hieu_id'];
    $hinh_anh = $_POST['hinh_anh_old'];

    if ($_FILES['hinh_anh']['error'] == 0) {
    $hinh_anh = basename($_FILES['hinh_anh']['name']);
    move_uploaded_file($_FILES['hinh_anh']['tmp_name'], '../image/' . $hinh_anh);
  }

    $gia_km_sql = $gia_km === NULL ? "NULL" : $gia_km;

    mysqli_query($conn, "UPDATE sanpham SET 
        ten_sp='$ten_sp',
        ma_sp='$ma_sp',
        gia=$gia,
        gia_khuyen_mai=$gia_km_sql,
        chat_lieu='$chat_lieu',
        da_chinh='$da_chinh',
        mo_ta='$mo_ta',
        hinh_anh='$hinh_anh',
        danh_muc_id=$danh_muc_id,
        thuong_hieu_id=$thuong_hieu_id
        WHERE id=$id");

    mysqli_query($conn, "DELETE FROM bien_the_san_pham WHERE san_pham_id=$id");

    if (isset($_POST['kich_co_arr'])) {
        foreach ($_POST['kich_co_arr'] as $i => $k) {
            if ($k != '') {
                $k = mysqli_real_escape_string($conn, $k);
                $sl = (int)$_POST['so_luong_arr'][$i];
                mysqli_query($conn, "INSERT INTO bien_the_san_pham VALUES (NULL,$id,'$k',$sl)");
            }
        }
    }

    header("Location: quan_ly_san_pham.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sản Phẩm - Luxury Jewelry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/qlsp.css">
</head>
<body class="bg-light">
    <?php include('header.php'); ?>

    <div class="main-content container py-5">
        <div class="card p-3 shadow-sm border-0 mb-4">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h4 class="mb-0 text-primary fw-bold">Quản Lý Sản Phẩm</h4>
                </div>
                <div class="col-md-7">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <select name="danh_muc_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Tất cả danh mục</option>
                                <?php 
                                $q_dm = mysqli_query($conn, "SELECT * FROM danhmuc");
                                while($dm = mysqli_fetch_assoc($q_dm)): ?>
                                    <option value="<?= $dm['id'] ?>" <?= ($filter_dm == $dm['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dm['ten_danh_muc']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Tên hoặc mã SP..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                                <?php if($search != '' || $filter_dm != ''): ?>
                                    <a href="quan_ly_san_pham.php" class="btn btn-outline-secondary">Xóa lọc</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle-fill me-1"></i>Thêm Mới
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Hình Ảnh</th>
                            <th>Thông Tin Sản Phẩm</th>
                            <th>Giá Bán</th>
                            <th>Kích Cỡ (Kho)</th>
                            <th>Trạng Thái</th>
                            <th class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-3">#<?= $row['id'] ?></td>
                                    <td>
                                        <?php if($row['hinh_anh']): ?>
                                            <img src="../image/<?= $row['hinh_anh'] ?>" width="60" height="60" class="rounded shadow-sm">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" style="width:60px; height:60px; font-size:10px;">No Img</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['ten_sp']) ?></div>
                                        <small class="text-muted">Mã: <?= htmlspecialchars($row['ma_sp']) ?></small>
                                    </td>
                                    <td>
                                        <div class="text-primary fw-bold"><?= number_format($row['gia']) ?>đ</div>
                                        <?php if(!is_null($row['gia_khuyen_mai'])): ?>
                                            <small class="text-danger text-decoration-line-through"><?= number_format($row['gia_khuyen_mai']) ?>đ</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if(!empty($row['danh_sach_size'])){
                                            $sizes = explode(', ', $row['danh_sach_size']);
                                            foreach($sizes as $sz) echo "<span class='badge bg-info text-dark me-1'>$sz</span> ";
                                        } else {
                                            echo "<span class='text-muted small'>Chưa nhập size</span>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                              <select 
                                                    onchange="window.location.href='?page=<?= $page ?>&search=<?= urlencode($search) ?>&danh_muc_id=<?= $filter_dm ?>&set_status=<?= $row['id'] ?>&value='+this.value" 
                                                    class="form-select form-select-sm">

                                                    <option value="1" <?= $row['trang_thai'] == 1 ? 'selected' : '' ?>>Hoạt động</option>
                                                    <option value="0" <?= $row['trang_thai'] == 0 ? 'selected' : '' ?>>Tạm tắt</option>

                                                </select>
                                            <button class="btn btn-sm btn-outline-warning me-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xác nhận xóa sản phẩm này?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row['trang_thai'] == 1): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tạm tắt</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title fw-bold">Cập Nhật Sản Phẩm #<?= $row['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="hinh_anh_old" value="<?= $row['hinh_anh'] ?>">
                                                    <div class="row g-3">
                                                        <div class="col-md-6"><label class="form-label">Tên SP</label><input type="text" class="form-control" name="ten_sp" value="<?= htmlspecialchars($row['ten_sp']) ?>" required></div>
                                                        <div class="col-md-6"><label class="form-label">Mã SP</label><input type="text" class="form-control" name="ma_sp" value="<?= htmlspecialchars($row['ma_sp']) ?>" required></div>
                                                        <div class="col-md-4"><label class="form-label">Giá</label><input type="number" class="form-control" name="gia" value="<?= $row['gia'] ?>" required></div>
                                                        <div class="col-md-4"><label class="form-label">Giá KM</label><input type="number" class="form-control" name="gia_khuyen_mai" value="<?= $row['gia_khuyen_mai'] ?>"></div>
                                                        <div class="col-md-4"><label class="form-label">Hình ảnh</label><input type="file" class="form-control" name="hinh_anh"></div>
                                                        
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold text-primary">Kích cỡ & Số lượng</label>
                                                            <div class="variant-row">
                                                                <?php 
                                                                $current_id = $row['id'];
                                                                $q_v = mysqli_query($conn, "SELECT * FROM bien_the_san_pham WHERE san_pham_id = $current_id");
                                                                while($v = mysqli_fetch_assoc($q_v)): ?>
                                                                    <div class="row g-2 mb-2">
                                                                        <div class="col-6"><input type="text" name="kich_co_arr[]" class="form-control form-control-sm" value="<?= $v['kich_co'] ?>"></div>
                                                                        <div class="col-6"><input type="number" name="so_luong_arr[]" class="form-control form-control-sm" value="<?= $v['so_luong'] ?>"></div>
                                                                    </div>
                                                                <?php endwhile; ?>
                                                                <div class="row g-2 mb-2">
                                                                    <div class="col-6"><input type="text" name="kich_co_arr[]" class="form-control form-control-sm" placeholder="Thêm size mới..."></div>
                                                                    <div class="col-6"><input type="number" name="so_luong_arr[]" class="form-control form-control-sm" placeholder="SL..."></div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4"><label class="form-label">Chất Liệu</label><input type="text" class="form-control" name="chat_lieu" value="<?= htmlspecialchars($row['chat_lieu']) ?>"></div>
                                                        <div class="col-md-4"><label class="form-label">Đá Chính</label><input type="text" class="form-control" name="da_chinh" value="<?= htmlspecialchars($row['da_chinh']) ?>"></div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Danh Mục</label>
                                                            <select name="danh_muc_id" class="form-select">
                                                                <?php 
                                                                $q_dm_edit = mysqli_query($conn, "SELECT * FROM danhmuc");
                                                                while($dm_e = mysqli_fetch_assoc($q_dm_edit)): ?>
                                                                    <option value="<?= $dm_e['id'] ?>" <?= ($row['danh_muc_id'] == $dm_e['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dm_e['ten_danh_muc']) ?></option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6"><label class="form-label">Thương Hiệu ID</label><input type="number" class="form-control" name="thuong_hieu_id" value="<?= $row['thuong_hieu_id'] ?>"></div>
                                                        <div class="col-12"><label class="form-label">Mô tả</label><textarea class="form-control" name="mo_ta" rows="2"><?= htmlspecialchars($row['mo_ta']) ?></textarea></div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-warning" name="edit_product_submit">Lưu Thay Đổi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">Không tìm thấy sản phẩm nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= ($i==$page)?'active':'' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&danh_muc_id=<?= $filter_dm ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Thêm Sản Phẩm Mới</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên Sản Phẩm</label>
                                <input type="text" class="form-control" name="ten_sp" placeholder="VD: Nhẫn Kim Cương Luxury" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mã Sản Phẩm</label>
                                <input type="text" class="form-control" name="ma_sp" placeholder="VD: SP001" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giá Gốc</label>
                                <input type="number" class="form-control" name="gia" placeholder="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giá Khuyến Mãi</label>
                                <input type="number" class="form-control" name="gia_khuyen_mai" placeholder="Để trống nếu không có">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hình Ảnh</label>
                                <input type="file" class="form-control" name="hinh_anh" required>
                            </div>
                           <div class="col-12">
                                <label class="form-label fw-bold text-primary">Kích cỡ & Số lượng kho</label>
                                <div class="variant-row">
                                    <div class="row g-2 mb-1 font-monospace" style="font-size: 11px; color: #666;">
                                        <div class="col-6 ps-2">KÍCH CỠ</div>
                                        <div class="col-6 ps-2">SỐ LƯỢNG</div>
                                    </div>
                                    <?php for($i=0; $i<7; $i++): ?>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <input type="text" name="kich_co_arr[]" class="form-control form-control-sm" placeholder="Nhập size...">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" name="so_luong_arr[]" class="form-control form-control-sm" placeholder="0">
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                    <small class="text-muted d-block mt-1"><i>* Để trống các ô nếu không sử dụng hết.</i></small>
                                </div>
                            </div>
                            <div class="col-md-4"><label class="form-label">Chất Liệu</label><input type="text" class="form-control" name="chat_lieu" placeholder="VD: Vàng 18K"></div>
                            <div class="col-md-4"><label class="form-label">Đá Chính</label><input type="text" class="form-control" name="da_chinh" placeholder="VD: Kim cương"></div>
                            <div class="col-md-4">
                                <label class="form-label">Danh Mục</label>
                                <select name="danh_muc_id" class="form-select">
                                    <?php 
                                    $q_dm_add = mysqli_query($conn, "SELECT * FROM danhmuc");
                                    while($dm_a = mysqli_fetch_assoc($q_dm_add)): ?>
                                        <option value="<?= $dm_a['id'] ?>"><?= htmlspecialchars($dm_a['ten_danh_muc']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label">Thương Hiệu ID</label><input type="number" class="form-control" name="thuong_hieu_id" value="1"></div>
                            <div class="col-12"><label class="form-label">Mô tả sản phẩm</label><textarea class="form-control" name="mo_ta" rows="3" placeholder="Nhập thông tin chi tiết về sản phẩm..."></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary px-4" name="add_product">Xác Nhận Thêm Mới</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
