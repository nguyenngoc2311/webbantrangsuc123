<?php
include '../php/config.php';
session_start();

// ================= SESSION =================
$id_nhan_vien_login = $_SESSION['user_id'] ?? 1;

// ================= 1. NHÂN VIÊN =================
$sql_nv_list = "SELECT id, hoten FROM nhanvien";
$stmt_nv_list = $conn->prepare($sql_nv_list);
if (!$stmt_nv_list) die("Lỗi NV: " . $conn->error);

$stmt_nv_list->execute();
$nhanvien_list = $stmt_nv_list->get_result();

// ================= 2. KIỂM TRA ID =================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: quan_ly_ton_kho.php");
    exit();
}

$id = (int) $_GET['id'];
$size_selected = $_GET['size'] ?? '';

// ================= 3. SẢN PHẨM =================
$sql = "
    SELECT s.*, t.id as id_thuong_hieu, t.ten_thuong_hieu 
    FROM sanpham s 
    LEFT JOIN thuong_hieu t ON s.thuong_hieu_id = t.id 
    WHERE s.id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Lỗi SP: " . $conn->error);

$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Sản phẩm không tồn tại!");
}

// ================= 4. SIZE =================
$sql_sizes = "
    SELECT kich_co, so_luong 
    FROM bien_the_san_pham 
    WHERE san_pham_id = ?
";

$stmt_s = $conn->prepare($sql_sizes);
if (!$stmt_s) die("Lỗi size: " . $conn->error);

$stmt_s->bind_param("i", $id);
$stmt_s->execute();

$sizes_result = $stmt_s->get_result();
$sizes_data = [];

while ($row = $sizes_result->fetch_assoc()) {
    $sizes_data[] = $row;
}

// ================= 5. NHẬP KHO =================
if (isset($_POST['update_stock'])) {

    $so_luong_nhap    = (int) $_POST['so_luong_moi'];
    $don_gia          = (float) $_POST['don_gia'];
    $kich_co_nhap     = $_POST['kich_co'];
    $id_nhanvien_chon = (int) $_POST['nhanvien_id'];

    $tong_gia_tri = $so_luong_nhap * $don_gia;
    $ngay_nhap    = date('Y-m-d H:i:s');

    if ($so_luong_nhap > 0 && !empty($kich_co_nhap)) {

        $conn->begin_transaction();

        try {

            // ================= NHÂN VIÊN =================
            $sql_nv = "SELECT hoten FROM nhanvien WHERE id = ?";
            $stmt_nv = $conn->prepare($sql_nv);
            if (!$stmt_nv) throw new Exception($conn->error);

            $stmt_nv->bind_param("i", $id_nhanvien_chon);
            $stmt_nv->execute();

            $nv_data = $stmt_nv->get_result()->fetch_assoc();
            $ho_ten_nv = $nv_data['hoten'] ?? '';

            // ================= PHIẾU NHẬP (FIX LỖI Ở ĐÂY) =================
            $sql_phieu = "
                INSERT INTO phieunhap 
                (idnhanvien, tongtien, ngaynhap) 
                VALUES (?, ?, ?)
            ";

            $stmt_p = $conn->prepare($sql_phieu);
            if (!$stmt_p) throw new Exception("PHIẾU NHẬP ERROR: " . $conn->error);

            $stmt_p->bind_param(
                "ids",
                $id_nhanvien_chon,
                $tong_gia_tri,
                $ngay_nhap
            );

            $stmt_p->execute();
            $phieu_nhap_id = $conn->insert_id;

            // ================= CHI TIẾT PHIẾU NHẬP =================
            $sql_ct = "
                INSERT INTO chitietphieunhap 
                (phieu_nhap_id, idsp, tensp, kich_thuoc, so_luong, don_gia, tong_gia_tri) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt_ct = $conn->prepare($sql_ct);
            if (!$stmt_ct) throw new Exception($conn->error);

            $stmt_ct->bind_param(
                "iissidd",
                $phieu_nhap_id,
                $id,
                $product['ten_sp'],
                $kich_co_nhap,
                $so_luong_nhap,
                $don_gia,
                $tong_gia_tri
            );

            $stmt_ct->execute();

            // ================= UPDATE TỒN KHO =================
            $sql_update = "
                UPDATE bien_the_san_pham 
                SET so_luong = so_luong + ? 
                WHERE san_pham_id = ? AND kich_co = ?
            ";

            $stmt_u = $conn->prepare($sql_update);
            if (!$stmt_u) throw new Exception($conn->error);

            $stmt_u->bind_param("iis", $so_luong_nhap, $id, $kich_co_nhap);
            $stmt_u->execute();

            $conn->commit();

            echo "<script>
                alert('Nhập kho thành công!');
                window.location.href='quan_ly_ton_kho.php';
            </script>";
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            echo "Lỗi hệ thống: " . $e->getMessage();
        }
    } else {
        echo "Dữ liệu không hợp lệ!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nhập Kho | <?= htmlspecialchars($product['ten_sp']); ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/nhapthem.css">
</head>

<body>

<div class="container mt-5 pb-5">
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card">

<div class="card-header bg-primary text-white py-3">
    <h5 class="mb-0">Phiếu Nhập Kho Sản Phẩm</h5>
</div>

<div class="card-body p-4">

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<!-- THÔNG TIN SẢN PHẨM -->
<div class="product-info p-3 mb-4">
    <h6 class="text-primary fw-bold mb-3">THÔNG TIN SẢN PHẨM</h6>

    <div class="row">
        <div class="col-md-7">
            <p><strong>Tên:</strong> <?= htmlspecialchars($product['ten_sp']); ?></p>
            <p class="text-muted small">#<?= $product['id']; ?></p>
        </div>

        <div class="col-md-5">
            <p><strong>Thương hiệu:</strong> <?= htmlspecialchars($product['ten_thuong_hieu']); ?></p>
        </div>
    </div>
</div>

<!-- FORM -->
<form method="POST">
<div class="row g-3">

<div class="col-md-6">
<label class="form-label fw-bold">Nhân viên</label>
<select name="nhanvien_id" class="form-select">

<?php 
$nhanvien_list->data_seek(0);
while($nv = $nhanvien_list->fetch_assoc()): ?>
<option value="<?= $nv['id']; ?>" <?= ($nv['id'] == $id_nhan_vien_login ? 'selected' : ''); ?>>
<?= htmlspecialchars($nv['hoten']); ?>
</option>
<?php endwhile; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label fw-bold">Kích cỡ</label>
<select name="kich_co" class="form-select" required>

<option value="">-- Chọn kích cỡ --</option>

<?php foreach($sizes_data as $s): ?>
<option value="<?= $s['kich_co']; ?>" <?= ($s['kich_co'] == $size_selected ? 'selected' : ''); ?>>
Size <?= $s['kich_co']; ?> (Tồn: <?= $s['so_luong']; ?>)
</option>
<?php endforeach; ?>

</select>
</div>

<div class="col-md-6">
<label class="form-label fw-bold">Số lượng</label>
<input type="number" name="so_luong_moi" class="form-control" min="1" required>
</div>

<div class="col-md-6">
<label class="form-label fw-bold">Đơn giá</label>
<div class="input-group">
<input type="number" name="don_gia" class="form-control" required>
<span class="input-group-text">VNĐ</span>
</div>
</div>

</div>

<div class="mt-4 pt-3 border-top d-flex justify-content-between">
<a href="quan_ly_ton_kho.php" class="btn btn-outline-secondary">Quay Lại</a>

<button type="submit" name="update_stock" class="btn btn-success px-5 fw-bold">
Xác nhận nhập kho
</button>
</div>

</form>

</div>
</div>
</div>
</div>
</div>

</body>
</html>