<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['id_khachhang'])) {
    die("Bạn cần đăng nhập để thực hiện chức năng này.");
}

$id_khachhang = $_SESSION['id_khachhang'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die("Mã đơn hàng không hợp lệ.");
}

// 2. LẤY DANH SÁCH SẢN PHẨM TRONG ĐƠN HÀNG
$sql_items = "SELECT c.IdSanPham, c.TenSanPham, c.HinhAnh 
              FROM chitiethoadon c 
              JOIN HoaDon h ON c.IdHoaDon = h.IdHoaDon 
              WHERE h.IdHoaDon = ? AND h.id_khachhang = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("ii", $order_id, $id_khachhang);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = $result_items->fetch_all(MYSQLI_ASSOC);

if (empty($items)) {
    die("Đơn hàng không hợp lệ hoặc không có sản phẩm.");
}

// 3. XỬ LÝ SUBMIT ĐÁNH GIÁ
if (isset($_POST['submit_review'])) {
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $product_ids = $_POST['product_id']; 
    $ratings = $_POST['rating'];         
    $comments = $_POST['comment'];       
    $success_count = 0;

    foreach ($product_ids as $index => $p_id) {
        $p_id = intval($p_id);
        $rating = isset($ratings[$p_id]) ? intval($ratings[$p_id]) : 5;
        $comment = mysqli_real_escape_string($conn, $comments[$index]);
        
        $media_name = null;
        $video_name = null;

        // Xử lý Upload File cho từng sản phẩm
        $file_key = 'media_' . $p_id;
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
            $file_original = basename($_FILES[$file_key]['name']);
            $file_ext = strtolower(pathinfo($file_original, PATHINFO_EXTENSION));
            $new_file_name = $file_original;
            $upload_path = "../image/" . $new_file_name;

            if (!is_dir('../image/')) {
                mkdir('../image/', 0777, true);
            }

            if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path)) {
                if (in_array($file_ext, ['jpg','jpeg','png','gif'])) {
                    $media_name = $new_file_name;
                } elseif (in_array($file_ext, ['mp4','avi','mov'])) {
                    $video_name = $new_file_name;
                }
            }
        }

        // TRẠNG THÁI LÀ CHỮ
        $trang_thai = "Chưa đọc"; 

        // Câu lệnh SQL: Có 9 dấu ? (thoi_gian_danh_gia dùng NOW() nên không cần ?)
        $sql_insert = "INSERT INTO danh_gia (san_pham_id, id_hoadon, Idnguoidung, so_sao, nhan_xet, thoi_gian_danh_gia, hinh_anh, video, hoten, trang_thai)
                       VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
        
        if ($stmt_ins = $conn->prepare($sql_insert)) {
            // "iiiisssss" tương ứng với 9 dấu ? ở trên
            $stmt_ins->bind_param("iiiisssss", 
                $p_id, 
                $order_id, 
                $id_khachhang, 
                $rating, 
                $comment, 
                $media_name, 
                $video_name, 
                $user_name, 
                $trang_thai
            );
            
            if ($stmt_ins->execute()) {
                $success_count++;
            }
            $stmt_ins->close();
        }
    }

    if ($success_count > 0) {
        echo "<script>alert('Gửi đánh giá thành công! '); window.location.href='lichsu.php?filter=da_giao';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh Giá Đơn Hàng #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/vietdanhgia.css">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h4 class="mb-4 text-center fw-bold text-uppercase">Viết đánh giá sản phẩm</h4>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body">
                        <label class="form-label fw-bold">Tên hiển thị</label>
                        <input type="text" name="user_name" class="form-control" required value="<?php echo $_SESSION['hoten'] ?? ''; ?>">
                    </div>
                </div>

                <?php foreach ($items as $item): 
                    $pid = $item['IdSanPham'];
                ?>
                <div class="card product-card mb-3 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <img src="../image/<?php echo $item['HinhAnh']; ?>" class="rounded border" style="width: 70px; height: 70px; object-fit: cover;">
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['TenSanPham']); ?></h6>
                                <input type="hidden" name="product_id[]" value="<?php echo $pid; ?>">
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4 text-center">
                            <label class="form-label d-block fw-bold">Mức độ hài lòng</label>
                            <div class="star-rating">
                                <input type="radio" id="star5_<?php echo $pid; ?>" name="rating[<?php echo $pid; ?>]" value="5" checked><label for="star5_<?php echo $pid; ?>"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4_<?php echo $pid; ?>" name="rating[<?php echo $pid; ?>]" value="4"><label for="star4_<?php echo $pid; ?>"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3_<?php echo $pid; ?>" name="rating[<?php echo $pid; ?>]" value="3"><label for="star3_<?php echo $pid; ?>"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2_<?php echo $pid; ?>" name="rating[<?php echo $pid; ?>]" value="2"><label for="star2_<?php echo $pid; ?>"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1_<?php echo $pid; ?>" name="rating[<?php echo $pid; ?>]" value="1"><label for="star1_<?php echo $pid; ?>"><i class="fas fa-star"></i></label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nội dung</label>
                            <textarea name="comment[]" class="form-control" rows="3" placeholder="Chia sẻ cảm nhận..." required></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Ảnh/Video</label>
                            <input type="file" name="media_<?php echo $pid; ?>" class="form-control" accept="image/*,video/*">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="submit_review" class="btn btn-danger btn-lg py-3 fw-bold">HOÀN TẤT ĐÁNH GIÁ</button>
                    <a href="lichsu.php" class="btn btn-link text-muted text-decoration-none">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>