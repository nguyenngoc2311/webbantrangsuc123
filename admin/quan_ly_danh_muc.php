<?php
include('../php/config.php');

// --- 1. Xử lý THÊM DANH MỤC ---
if (isset($_POST['add_category'])) {
    $ten_danh_muc = mysqli_real_escape_string($conn, $_POST['ten_danh_muc']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $trang_thai = $_POST['trang_thai'];

    $sql = "INSERT INTO danhmuc (ten_danh_muc, mo_ta, trang_thai) 
            VALUES ('$ten_danh_muc', '$mo_ta', '$trang_thai')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Thêm danh mục thành công!'); window.location.href='quan_ly_danh_muc.php';</script>";
        exit;
    }
}

// --- 2. Xử lý SỬA DANH MỤC ---
if (isset($_POST['edit_category_submit'])) {
    $id = $_POST['id'];
    $ten_danh_muc = mysqli_real_escape_string($conn, $_POST['ten_danh_muc']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $trang_thai = $_POST['trang_thai'];

    $sql = "UPDATE danhmuc SET ten_danh_muc='$ten_danh_muc', mo_ta='$mo_ta', trang_thai='$trang_thai' WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Cập nhật danh mục thành công!'); window.location.href='quan_ly_danh_muc.php';</script>";
        exit;
    }
}

// --- 3. Xử lý XÓA DANH MỤC ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    if (mysqli_query($conn, "DELETE FROM danhmuc WHERE id=$id")) {
        echo "<script>alert('Xóa danh mục thành công!'); window.location.href='quan_ly_danh_muc.php';</script>";
        exit;
    }
}

// Fetch categories
$result = mysqli_query($conn, "SELECT * FROM danhmuc ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Danh Mục | Luxury Jewelry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/qlth.css"> </head>
<body>

    <?php include('header.php'); ?>

    <div class="main-content">
        <div class="container-fluid">
            
            <div class="d-flex justify-content-between align-items-center mb-4 card p-4 shadow-sm border-0 flex-row bg-white">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Quản Lý Danh Mục</h4>
                    <p class="text-muted small mb-0">Tổ chức trang sức theo nhóm loại sản phẩm</p>
                </div>
                <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-lg me-2"></i>Thêm Danh Mục
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">ID</th>
                                <th>Tên Danh Mục</th>
                                <th style="width: 40%;">Mô Tả</th>
                                <th>Trạng Thái</th>
                                <th class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted">#<?php echo $row['id']; ?></td>
                                <td class="fw-semibold"><?php echo $row['ten_danh_muc']; ?></td>
                                <td class="text-muted small"><?php echo $row['mo_ta']; ?></td>
                                <td>
                                    <?php if ($row['trang_thai'] == 1): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success px-3">
                                            <i class="bi bi-check-circle-fill me-1"></i> Hoạt động
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-danger-subtle text-danger px-3">
                                            <i class="bi bi-x-circle-fill me-1"></i> Ngừng bán
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $row['id']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editCategoryModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg">
                                        <form method="POST">
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title" style="color: #d4af37;">Cập Nhật Danh Mục #<?php echo $row['id']; ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Tên Danh Mục</label>
                                                    <input type="text" class="form-control shadow-sm" name="ten_danh_muc" value="<?php echo $row['ten_danh_muc']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Mô Tả</label>
                                                    <textarea class="form-control shadow-sm" name="mo_ta" rows="3"><?php echo $row['mo_ta']; ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Trạng Thái</label>
                                                    <select name="trang_thai" class="form-select shadow-sm">
                                                        <option value="1" <?php echo ($row['trang_thai'] == 1 ? 'selected' : ''); ?>>Hoạt động</option>
                                                        <option value="0" <?php echo ($row['trang_thai'] == 0 ? 'selected' : ''); ?>>Không hoạt động</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-0">
                                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" name="edit_category_submit" class="btn btn-primary px-4">Lưu Thay Đổi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form method="POST">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" style="color: #d4af37;">Thêm Danh Mục Mới</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên Danh Mục</label>
                            <input type="text" name="ten_danh_muc" class="form-control shadow-sm" placeholder="VD: Nhẫn Kim Cương" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô Tả</label>
                            <textarea name="mo_ta" class="form-control shadow-sm" rows="3" placeholder="Mô tả ngắn về danh mục này..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Trạng Thái</label>
                            <select name="trang_thai" class="form-select shadow-sm">
                                <option value="1">Hoạt động</option>
                                <option value="0">Không hoạt động</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="add_category" class="btn btn-primary px-4">Xác Nhận Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>