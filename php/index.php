<?php
include "config.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== 1. SẢN PHẨM ĐÁNH GIÁ CAO (REALTIME) =====
$sql_top_rated = "
    SELECT sp.*, 
           IFNULL(AVG(d.so_sao), 0) AS avg_sao,
           COUNT(d.ma_danh_gia) AS tong_danhgia
    FROM sanpham sp
    LEFT JOIN danh_gia d ON sp.id = d.san_pham_id
    GROUP BY sp.id
    HAVING avg_sao >= 4
    ORDER BY avg_sao DESC, tong_danhgia DESC
";

$result_top_rated = mysqli_query($conn, $sql_top_rated);

// ===== 2. DANH MỤC =====
$sql_dm = "SELECT * FROM danhmuc";
$result_dm = mysqli_query($conn, $sql_dm);

if (!$result_dm) {
    die("Lỗi SQL danh mục: " . mysqli_error($conn));
}

$products = [];

while ($dm = mysqli_fetch_assoc($result_dm)) {
    $danhmuc_id = (int)$dm['id'];

    $sql_sp = "
        SELECT sp.*, 
               IFNULL(AVG(d.so_sao), 0) AS avg_sao,
               COUNT(d.ma_danh_gia) AS tong_danhgia
        FROM sanpham sp
        LEFT JOIN danh_gia d ON sp.id = d.san_pham_id
        WHERE sp.danh_muc_id = $danhmuc_id
        GROUP BY sp.id
        ORDER BY sp.id DESC
        LIMIT 5
    ";

    $result_sp = mysqli_query($conn, $sql_sp);

    if ($result_sp) {
        while ($sp = mysqli_fetch_assoc($result_sp)) {
            $products[] = $sp;
        }
    }
}
// ===== 3. TỪ KHÓA TÌM KIẾM NHIỀU NHẤT =====
$sql_top_keyword = "
    SELECT tu_khoa, COUNT(*) AS so_lan
    FROM tu_khoa_tim_kiem
    GROUP BY tu_khoa
    HAVING COUNT(*) >= 2
    ORDER BY so_lan DESC
    LIMIT 10
";

$result_top_keyword = mysqli_query($conn, $sql_top_keyword);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Jewelry - Trang Sức Cao Cấp</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container">

<?php include "menu.php"; ?>

<!-- ===== BANNER ===== -->
<div class="banner mb-4">
    <img src="https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=1920" class="img-fluid rounded shadow">
    <div class="banner-text text-center mt-3">
        <h1 class="display-4 fw-bold">Luxury Jewelry</h1>
    </div>
</div>
<!-- ===== TRENDING KEYWORD ===== -->
<h2 class="title text-center mt-5 mb-4"> Từ Khóa Tìm Kiếm Nhiều Nhất</h2>

<div class="text-center mb-5">

    <?php if (mysqli_num_rows($result_top_keyword) > 0): ?>

        <?php while ($k = mysqli_fetch_assoc($result_top_keyword)): ?>

            <a href="timkiem.php?keyword=<?php echo urlencode($k['tu_khoa']); ?>"
               class="btn btn-outline-dark rounded-pill m-1 px-3">

                <?php echo htmlspecialchars($k['tu_khoa']); ?>

            </a>

        <?php endwhile; ?>

    <?php else: ?>

        <p class="text-muted">Chưa có dữ liệu tìm kiếm</p>

    <?php endif; ?>

</div>
<!-- ===== TOP RATED ===== -->
<h2 class="title text-center mt-5 mb-4">Sản Phẩm Được Đánh Giá Cao</h2>

<div class="product-slider position-relative">

    <button class="slider-btn left btn btn-dark position-absolute top-50 start-0 translate-middle-y z-3"
            onclick="slideTopRatedLeft()">❮</button>

    <div class="product-container d-flex overflow-hidden" id="topRatedContainer">

        <?php if (mysqli_num_rows($result_top_rated) > 0): ?>
            <?php while ($top_sp = mysqli_fetch_assoc($result_top_rated)): ?>
            
            <div class="flex-shrink-0 px-2" style="width: 250px;">

                <a href="chitiet.php?id=<?php echo $top_sp['id']; ?>"
                   class="product-card border p-3 d-block text-decoration-none text-dark bg-white h-100">

                    <div class="product-img text-center" style="height: 150px;">
                        <img src="../image/<?php echo htmlspecialchars($top_sp['hinh_anh']); ?>"
                             class="img-fluid h-100"
                             style="object-fit: contain;">
                    </div>

                    <h6 class="product-name mt-2 text-truncate">
                        <?php echo htmlspecialchars($top_sp['ten_sp']); ?>
                    </h6>

                    <div class="rating">
                        <?php
                        $avg = round($top_sp['avg_sao']);
                        for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $avg)
                                ? "<span class='star active'>★</span>"
                                : "<span class='star'>★</span>";
                        }
                        ?>
                        <small class="text-muted">
                            (<?php echo $top_sp['tong_danhgia']; ?>)
                        </small>
                    </div>

                    <div class="price mt-2 text-danger fw-bold">
                        <?php
                        $price_display = ($top_sp['gia_khuyen_mai'] > 0)
                            ? $top_sp['gia_khuyen_mai']
                            : $top_sp['gia'];

                        echo number_format($price_display) . "đ";
                        ?>
                    </div>

                </a>

            </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">Chưa có đánh giá nào nổi bật.</p>
        <?php endif; ?>

    </div>

    <button class="slider-btn right btn btn-dark position-absolute top-50 end-0 translate-middle-y z-3"
            onclick="slideTopRatedRight()">❯</button>

</div>

<!-- ===== PROMO ===== -->
<div class="promo-banner shadow-sm">
    <img src="../image/bt.jpg" alt="Promotion">
    <div class="promo-content">
        <h2 class="fw-bold">Sản Phẩm Mới</h2>
        <a href="trangsuc.php" class="btn btn-light rounded-pill px-4">Xem ngay</a>
    </div>
</div>

<hr class="my-5">

<!-- ===== SLIDER ===== -->
<h2 class="title text-center mt-5 mb-4">Sản phẩm nổi bật</h2>

<div class="product-slider position-relative">

    <button class="slider-btn left btn btn-dark position-absolute top-50 start-0 translate-middle-y z-3"
            onclick="slideLeft()">❮</button>

    <div class="product-container d-flex overflow-hidden" id="productContainer">

        <?php foreach ($products as $sp): ?>
        <div class="flex-shrink-0 px-2" style="width: 250px;">

            <a href="chitiet.php?id=<?php echo $sp['id']; ?>"
               class="product-card border p-3 d-block text-decoration-none text-dark bg-white h-100">

                <div class="product-img position-relative text-center">

                    <?php if (!empty($sp['gia_khuyen_mai']) && $sp['gia'] > 0):
                        $giam = 100 - ($sp['gia_khuyen_mai'] / $sp['gia'] * 100);
                    ?>
                        <span class="badge bg-danger position-absolute top-0 start-0">
                            -<?php echo round($giam); ?>%
                        </span>
                    <?php endif; ?>

                    <img src="../image/<?php echo htmlspecialchars($sp['hinh_anh']); ?>"
                         class="img-fluid"
                         style="height: 150px; object-fit: contain;">
                </div>

                <h6 class="product-name mt-2 text-truncate">
                    <?php echo htmlspecialchars($sp['ten_sp']); ?>
                </h6>

                <div class="rating">
                    <?php
                    $avg = round($sp['avg_sao']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo ($i <= $avg)
                            ? "<span class='star active'>★</span>"
                            : "<span class='star'>★</span>";
                    }
                    ?>
                </div>

                <div class="price mt-2">
                    <?php if (!empty($sp['gia_khuyen_mai']) && $sp['gia_khuyen_mai'] > 0): ?>
                        <span class="text-danger fw-bold">
                            <?php echo number_format($sp['gia_khuyen_mai']); ?>đ
                        </span>
                        <small class="text-decoration-line-through text-muted">
                            <?php echo number_format($sp['gia']); ?>đ
                        </small>
                    <?php else: ?>
                        <span class="text-dark fw-bold">
                            <?php echo number_format($sp['gia']); ?>đ
                        </span>
                    <?php endif; ?>
                </div>

            </a>
        </div>
        <?php endforeach; ?>

    </div>

    <button class="slider-btn right btn btn-dark position-absolute top-50 end-0 translate-middle-y z-3"
            onclick="slideRight()">❯</button>

</div>

<!-- ===== BUTTON ===== -->
<div class="text-center mt-5 mb-5">
    <a href="sanpham.php"
       class="btn btn-success px-5 py-2 rounded-pill shadow">
        Xem tất cả sản phẩm
    </a>
</div>

</div>

<?php include "footer.php"; ?>

<script src="../js/index.js"></script>

</body>
</html>