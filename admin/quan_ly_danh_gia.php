<?php
include '../php/config.php';
session_start();

// ===== XỬ LÝ =====

// Xóa đánh giá
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM danh_gia WHERE ma_danh_gia=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: quan_ly_danh_gia.php");
    exit();
}

// Đánh dấu đã đọc 1 cái
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE danh_gia SET trang_thai='1' WHERE ma_danh_gia=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: quan_ly_danh_gia.php");
    exit();
}

// Đánh dấu tất cả đã đọc
if (isset($_POST['mark_all_read'])) {
    $conn->query("UPDATE danh_gia SET trang_thai='1'");
    header("Location: quan_ly_danh_gia.php");
    exit();
}

// ===== LẤY DATA (Đã sửa SQL để lấy ảnh đánh giá) =====
$sql = "SELECT dg.*, sp.ten_sp 
        FROM danh_gia dg
        LEFT JOIN sanpham sp ON dg.san_pham_id = sp.id
        ORDER BY dg.thoi_gian_danh_gia DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đánh giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/qldg.css">

</head>
<body>

<div class="wrapper">
    <div class="sidebar">
        <?php include('header.php'); ?>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>QUẢN LÝ ĐÁNH GIÁ</h5>
                    <form method="POST" onsubmit="return confirm('Xác nhận đánh dấu tất cả đã đọc?')">
                        <button name="mark_all_read" class="btn btn-primary btn-sm">
                            <i class="fas fa-check-double me-1"></i> Đánh dấu tất cả đã đọc
                        </button>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Ảnh khách gửi</th> <th>Người gửi</th>
                                    <th>Thời gian</th>
                                    <th>Trạng thái</th>
                                    <th>Nội dung</th>
                                    <th>Sao</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): 
                                    $is_read = ($row['trang_thai'] == '1' || $row['trang_thai'] == 'Đã đọc');
                                ?>
                                <tr class="<?php echo !$is_read ? 'unread' : ''; ?>">
                                    <td><small><strong><?php echo htmlspecialchars($row['ten_sp']); ?></strong></small></td>
                                    
                                    <td>
                                        <?php if(!empty($row['hinh_anh'])): ?>
                                            <a href="../image/<?php echo $row['hinh_anh']; ?>" target="_blank">
                                                <img src="../image/<?php echo $row['hinh_anh']; ?>" class="img-review" alt="Feedback">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Không có ảnh</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['hoten']); ?></div>
                                        <small class="text-muted">ID: <?php echo $row['Idnguoidung']; ?></small>
                                    </td>
                                    <td><small><?php echo date('d/m/Y H:i', strtotime($row['thoi_gian_danh_gia'])); ?></small></td>
                                    <td>
                                        <?php if($is_read): ?>
                                            <span class="badge bg-light text-dark border"><i class="fas fa-check-circle text-success me-1"></i> Đã đọc</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning"><i class="fas fa-envelope me-1"></i> Mới</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small text-wrap" style="max-width: 250px;">
                                            <?php echo htmlspecialchars($row['nhan_xet']); ?>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $row['so_sao'] ? 'star-gold' : 'text-secondary opacity-25'; ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if(!$is_read): ?>
                                            <a href="?mark_read=<?php echo $row['ma_danh_gia']; ?>" class="btn btn-sm btn-outline-success" title="Đánh dấu đã đọc">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete_id=<?php echo $row['ma_danh_gia']; ?>" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')" 
                                           class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>

                                <?php if($result->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Chưa có đánh giá nào khách hàng gửi về.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>