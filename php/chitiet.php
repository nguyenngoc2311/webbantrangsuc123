<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================== LOGIN ==================
$is_logged_in = isset($_SESSION['id']); 

// ================== LẤY SẢN PHẨM ==================
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) die("Sản phẩm không hợp lệ.");

$sql = "SELECT * FROM sanpham WHERE id = $product_id";
$result = mysqli_query($conn, $sql);
if (!$result) die("Lỗi SQL: " . mysqli_error($conn));
$product = mysqli_fetch_assoc($result);
if (!$product) die("Sản phẩm không tồn tại.");

// ================== KHO SẢN PHẨM ==================
$sql_stock = "SELECT kich_co, so_luong FROM bien_the_san_pham WHERE san_pham_id = $product_id";
$stock_result = mysqli_query($conn, $sql_stock);
if (!$stock_result) die("Lỗi SQL lấy thông tin kho: " . mysqli_error($conn));

$stock_data = [];
$sizes = [];
while ($row_stock = mysqli_fetch_assoc($stock_result)) {
    $size_val = trim($row_stock['kich_co']);
    $stock_data[$size_val] = $row_stock['so_luong'];
    $sizes[] = $size_val;
}

// ================== SẢN PHẨM LIÊN QUAN ==================
$danh_muc_id = $product['danh_muc_id'];
$sql_lienquan = "SELECT sp.*, AVG(d.so_sao) AS avg_rating, COUNT(d.san_pham_id) AS total_reviews
                 FROM sanpham sp
                 LEFT JOIN danh_gia d ON sp.id = d.san_pham_id
                 WHERE sp.danh_muc_id = ? AND sp.id != ?
                 GROUP BY sp.id";
$stmt_lq = $conn->prepare($sql_lienquan);
if (!$stmt_lq) die("Lỗi SQL liên quan: " . $conn->error);
$stmt_lq->bind_param("ii", $danh_muc_id, $product_id);
$stmt_lq->execute();
$result_lienquan = $stmt_lq->get_result();


// ================== GIỎ HÀNG ==================
if ($is_logged_in && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    $size = $_POST['size'];
    $available_stock = $stock_data[$size] ?? 0;

    // Kiểm tra nếu số lượng khách chọn lớn hơn kho (chỉ kiểm tra, không trừ)
    if ($quantity > $available_stock) {
        echo "<script>alert('Số lượng vượt quá kho! Còn lại $available_stock');</script>";
    } else {
        
        // Chỉ thêm sản phẩm vào SESSION giỏ hàng
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $key = $product_id . "_" . $size;

        $_SESSION['cart'][$key] = [
            'id' => $product_id,
            'product_id' => $product_id, // Đồng bộ key với trang thanhtoan
            'name' => $product['ten_sp'],
            'product_name' => $product['ten_sp'],
            'price' => !empty($product['gia_khuyen_mai']) ? $product['gia_khuyen_mai'] : $product['gia'],
            'image' => $product['hinh_anh'],
            'product_image' => $product['hinh_anh'],
            'size' => $size,
            'quantity' => isset($_SESSION['cart'][$key]) ? $_SESSION['cart'][$key]['quantity'] + $quantity : $quantity
        ];

        header("Location: giohang.php");
        exit();
    }
}

// ================== THƯƠNG HIỆU ==================
$thuong_hieu_id = $product['thuong_hieu_id'];
$sql_th = "SELECT ten_thuong_hieu FROM thuong_hieu WHERE id = $thuong_hieu_id";
$res_th = mysqli_query($conn, $sql_th);
$row_th = mysqli_fetch_assoc($res_th);
$thuong_hieu_name = $row_th['ten_thuong_hieu'] ?? 'Không có';

// ================== ĐÁNH GIÁ ==================
if (isset($_POST['submit_review'])) {
    if (!$is_logged_in) {
        echo "<script>alert('Vui lòng đăng nhập!'); window.location='login.php';</script>"; exit();
    }
    
    $id_nguoidung = intval($_SESSION['id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $hoten = trim($_SESSION['hoten'] ?? '');
    $image_name = null; // Mặc định là không có ảnh

    // XỬ LÝ UPLOAD ẢNH
    if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] == 0) {
        $target_dir = "../image/"; // Thư mục lưu ảnh (Bạn phải tạo thư mục này trước)
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["review_image"]["name"], PATHINFO_EXTENSION);
        // Đổi tên file để tránh trùng lặp: review_123456789.jpg
        $image_name = "review_" . time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $image_name;

        // Kiểm tra định dạng (tùy chọn) và di chuyển file
        $allow_types = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array(strtolower($file_extension), $allow_types)) {
            move_uploaded_file($_FILES["review_image"]["tmp_name"], $target_file);
        } else {
            $image_name = null; // Nếu file không hợp lệ thì bỏ qua ảnh
        }
    }

    if ($id_nguoidung <= 0 || $rating < 1 || $rating > 5) {
        die("Dữ liệu không hợp lệ!");
    }
    if (empty($comment)) {
        die("Vui lòng nhập nội dung đánh giá!");
    }

    // Check if user already reviewed
    $check_sql = "SELECT ma_danh_gia FROM danh_gia WHERE san_pham_id = ? AND Idnguoidung = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $product_id, $id_nguoidung);
    $check_stmt->execute();
    $check = $check_stmt->get_result();

    if ($check->num_rows == 0) {
        // CẬP NHẬT CÂU LỆNH INSERT (Thêm cột hinh_anh)
        $sql_insert = "INSERT INTO danh_gia (san_pham_id, Idnguoidung, so_sao, nhan_xet, thoi_gian_danh_gia, hoten, hinh_anh)
                       VALUES (?, ?, ?, ?, NOW(), ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiisss", $product_id, $id_nguoidung, $rating, $comment, $hoten, $image_name);
        
        if ($stmt_insert->execute()) {
            header("Location: chitiet.php?id=$product_id#reviews");
            exit();
        } else {
            die("Lỗi khi lưu đánh giá!");
        }
    } else {
        echo "<script>alert('Bạn đã đánh giá sản phẩm này rồi!'); window.location='chitiet.php?id=$product_id';</script>";
    }
}
// ================== THỐNG KÊ ĐÁNH GIÁ ==================
$sql_stat = "SELECT AVG(so_sao) AS avg, COUNT(*) AS total FROM danh_gia WHERE san_pham_id = $product_id";
$res_stat = mysqli_query($conn, $sql_stat);
$data_stat = mysqli_fetch_assoc($res_stat);
$review_count = $data_stat['total'];
$avg_rating = $review_count > 0 ? round($data_stat['avg'], 1) : "N/A";

// ================== LẤY TẤT CẢ ĐÁNH GIÁ ==================
$sort = $_GET['sort'] ?? 'newest';
$sort_query = " ORDER BY thoi_gian_danh_gia DESC";
if ($sort == 'oldest') $sort_query = " ORDER BY thoi_gian_danh_gia ASC";
elseif ($sort == 'highest_rating') $sort_query = " ORDER BY so_sao DESC";

$sql_all_reviews = "SELECT * FROM danh_gia WHERE san_pham_id = $product_id $sort_query";
$result_all_reviews = mysqli_query($conn, $sql_all_reviews);
if (!$result_all_reviews) die("Lỗi SQL đánh giá: " . mysqli_error($conn));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['ten_sp']); ?></title>
    <link rel="stylesheet" href="../css/chitiet.css">
    <link rel="stylesheet" href="../css/danhgia.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'menu.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <img src="../image/<?php echo htmlspecialchars($product['hinh_anh']); ?>" class="img-fluid product-image">
        </div>
        <div class="col-md-6 product-info">
            <h2><?php echo htmlspecialchars($product['ten_sp']); ?></h2>
            <p class="product-code">Mã: <?php echo htmlspecialchars($product['ma_sp']); ?></p>

            <?php if (!empty($product['gia_khuyen_mai']) && $product['gia_khuyen_mai'] > 0): ?>
                <p class="price">
                    <span class="text-decoration-line-through text-muted"><?php echo number_format($product['gia'], 0, ',', '.'); ?>đ</span>
                    <span class="text-danger fw-bold fs-3"><?php echo number_format($product['gia_khuyen_mai'], 0, ',', '.'); ?>đ</span>
                </p>
            <?php else: ?>
                <p class="price fw-bold fs-3 text-danger"><?php echo number_format($product['gia'], 0, ',', '.'); ?>đ</p>
            <?php endif; ?>

            <?php
                
                $selected_size = $_GET['size'] ?? ''; 

                if (empty($selected_size)) {
                    foreach ($sizes as $s) {
                        if (($stock_data[trim($s)] ?? 0) > 0) {
                            $selected_size = trim($s);
                            break;
                        }
                    }
                }
                if (empty($selected_size)) {
                    $selected_size = trim($sizes[0]);
                }

                $current_stock = $stock_data[$selected_size] ?? 0;
                ?>

                <p><strong>Số lượng còn lại:</strong> 
                    <span id="stock-available" style="font-weight: bold; color: red;">
                        <?php echo $current_stock; ?>
                    </span> chiếc
                </p>

                <form method="POST" id="product-form" action="giohang.php">
                      <div class="size-selector mb-3">
                        <label class="fw-bold mb-2">Chọn Kích cỡ:</label>
                        <div class="custom-size-group">
                            <?php foreach ($sizes as $s): 
                                $size_val = htmlspecialchars(trim($s));
                                $available_stock = $stock_data[$size_val] ?? 0;
                                $is_out_of_stock = ($available_stock <= 0);
                                $is_active = ($selected_size == $size_val);
                                $link = "?id=" . ($product['id'] ?? $_GET['id']) . "&size=" . $size_val;
                            ?>
                                <a href="<?php echo $link; ?>" 
                                class="size-node <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_out_of_stock ? 'out' : ''; ?>">
                                    <span class="t1"><?php echo $size_val; ?></span>
                                    <?php if($is_out_of_stock) echo '<span class="t2">(Hết)</span>'; ?>
                                </a>
                                
                                <?php if ($is_active): ?>
                                    <input type="hidden" name="size" value="<?php echo $size_val; ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="quantity-selector mb-3">
                        <label class="fw-bold">Số lượng:</label>
                        <input type="number" name="quantity" id="product-quantity" value="1" min="1" 
                            max="<?php echo $current_stock; ?>" 
                            class="form-control w-25" required>
                    </div>

                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['ten_sp']); ?>">
                    <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['hinh_anh']); ?>">
                    
                    <?php 
                        $current_price = ($product['gia_khuyen_mai'] > 0) ? $product['gia_khuyen_mai'] : $product['gia']; 
                    ?>
                    <input type="hidden" name="product_price" value="<?php echo (float)$current_price; ?>">

                    <div class="d-grid gap-2">
                        <button type="submit" name="add_to_cart" class="btn btn-outline-secondary btn-lg"
                            <?php if (!$is_logged_in) echo "onclick=\"alert('Vui lòng đăng nhập!'); window.location.href='login.php'; return false;\""; ?>
                            <?php if ($current_stock <= 0) echo "disabled style='opacity: 0.4; cursor: not-allowed;'"; ?>>
                            <?php echo ($current_stock <= 0) ? "Hết hàng" : "Thêm vào giỏ hàng"; ?>
                        </button>

                        <button type="submit" name="muangay" class="btn btn-danger btn-lg" formaction="thanhtoan.php"
                            <?php if (!$is_logged_in) echo "onclick=\"alert('Vui lòng đăng nhập để mua ngay!'); window.location.href='login.php'; return false;\""; ?>
                            <?php if ($current_stock <= 0) echo "disabled style='opacity: 0.4; cursor: not-allowed;'"; ?>>
                            Mua ngay
                        </button>
                    </div>
                </form>

               <script src="../js/soluong.js"></script>

        </div>
    </div>

    <!-- Tabs mô tả và hướng dẫn -->
    <div class="mt-5">
       <ul class="nav nav-tabs" id="productTab" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#desc">Mô tả</a></li>
            <?php if (in_array($product['danh_muc_id'], [1,10,4,7])): ?>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#guide">Cách đo size</a></li>
            <?php endif; ?>
        </ul>
        <div class="tab-content mt-3 p-3 border">
            <div class="tab-pane fade show active" id="desc">
                <h3>Mô tả sản phẩm</h3>
                <p><?php echo nl2br(htmlspecialchars($product['mo_ta'])); ?></p>
                <hr>
                <p><strong>Chất liệu:</strong> <?php echo $product['chat_lieu'] ?? 'Không có thông tin'; ?></p>
                <p><strong>Đá chính:</strong> <?php echo $product['da_chinh'] ?? 'Không có thông tin'; ?></p>
                <p><strong>Thương hiệu:</strong> <?php echo htmlspecialchars($thuong_hieu_name); ?></p>
                <p><strong>Trọng lượng:</strong> <?php echo $product['trong_luong'] ?? 'Không có thông tin'; ?> kg</p>
            </div>
            <?php if (in_array($product['danh_muc_id'], [1,10,4,7])): ?>
                <div class="tab-pane fade" id="guide">
                    <?php if (in_array($product['danh_muc_id'], [1,10])) include 'sizenhan.php';
                          elseif (in_array($product['danh_muc_id'], [4,7])) include 'sizevong.php'; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sản phẩm liên quan -->
    <div class="related-products mt-5">
        <h3>Sản phẩm liên quan</h3>
        <div class="carousel-container">
            <div class="carousel-wrapper">
                <div class="row flex-nowrap">
                    <?php while ($related_product = mysqli_fetch_assoc($result_lienquan)): ?>
                        <?php $avg = $related_product['avg_rating'] ? round($related_product['avg_rating'], 1) : 0; ?>
                        <div class="col-md-3">
                            <a href="chitiet.php?id=<?php echo $related_product['id']; ?>" 
                               style="text-decoration: none; color: inherit;">
                                <div class="product-card p-2">
                                    <img src="../image/<?php echo htmlspecialchars($related_product['hinh_anh']); ?>" class="img-fluid">
                                    <h5 class="product-title mt-2"><?php echo htmlspecialchars($related_product['ten_sp']); ?></h5>
                                    <div class="product-rating mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span style="color:<?php echo ($i <= $avg) ? '#ffc107' : '#e4e5e9'; ?>;">★</span>
                                        <?php endfor; ?>
                                        <small class="text-muted">(<?php echo $related_product['total_reviews']; ?>)</small>
                                    </div>
                                    <p class="product-price">
                                        <?php echo number_format($related_product['gia_khuyen_mai'] ? $related_product['gia_khuyen_mai'] : $related_product['gia'], 0, ',', '.'); ?>đ
                                    </p>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Đánh giá -->
    <div id="reviews" class="product-reviews">
        <h3>Đánh giá từ khách hàng</h3>
        
        <div class="review-header">
            <div class="rating-overview">
                <div class="rating-score">
                    <?php echo $avg_rating; ?>
                </div>
                
                <div class="stars-display">
                    <span>★★★★★</span>
                    <div class="stars-active" style="width: <?php echo ($avg_rating != 'N/A') ? ($avg_rating * 20) : 0; ?>%">
                        ★★★★★
                    </div>
                </div>
                
                <div class="text-muted">Tổng <?php echo $review_count; ?> đánh giá</div>
            </div>
            
            <div class="review-filters">
                <select class="form-select" onchange="window.location.href='chitiet.php?id=<?php echo $product_id; ?>&sort='+this.value;">
                    <option value="newest" <?php echo $sort=='newest'?'selected':''; ?>>Mới nhất</option>
                    <option value="highest_rating" <?php echo $sort=='highest_rating'?'selected':''; ?>>Đánh giá cao</option>
                </select>
            </div>
        </div>

        <div class="review-list">
            <?php while ($rev = mysqli_fetch_assoc($result_all_reviews)): ?>
                <div class="review-item">
                    <div class="d-flex align-items-center mb-1">
                        <strong><?php echo htmlspecialchars($rev['hoten']); ?></strong>
                        <span class="text-warning">
                            <?php 
                            for($i=1; $i<=5; $i++) {
                                echo $i <= $rev['so_sao'] ? '★' : '☆';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($rev['nhan_xet'])); ?></p>

                    <?php if (!empty($rev['hinh_anh'])): ?>
                        <div class="review-img-container">
                            <img src="../image/<?php echo $rev['hinh_anh']; ?>" 
                                alt="Ảnh đánh giá" 
                                onclick="window.open(this.src)">
                        </div>
                    <?php endif; ?>

                    <small class="text-muted">
                        <?php echo date('d/m/Y H:i', strtotime($rev['thoi_gian_danh_gia'])); ?>
                    </small>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
 <?php include 'footer.php'; ?>
</body>
</html>