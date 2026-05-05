<?php
include 'config.php'; // Kết nối CSDL

// Truy vấn sản phẩm kèm rating trung bình và số lượng đánh giá
$sql = "SELECT sp.*, 
               IFNULL(AVG(d.so_sao), 0) AS rating_avg,
               COUNT(d.ma_danh_gia) AS rating_count
        FROM sanpham sp
        LEFT JOIN danh_gia d ON sp.id = d.san_pham_id
        WHERE sp.danh_muc_id = 14
        GROUP BY sp.id";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn)); 
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bộ Sưu Tập Trang Sức Cao Cấp</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- CSS riêng -->
    <link rel="stylesheet" href="../css/trangsuc.css"> 
</head>

<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-5 pt-4">
        <div class="text-center mb-5">
            <h1 class="collection-title text-uppercase">Sản Phẩm Mới Nhất</h1>
        </div>

        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php if (isset($row['id'])): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <a href="chitiet.php?id=<?php echo $row['id']; ?>" class="product-link">
                            <div class="card h-100 product-card shadow-sm">
                                <div class="img-wrapper">
                                    <img src="../image/<?php echo $row['hinh_anh']; ?>" 
                                         alt="<?php echo htmlspecialchars($row['ten_sp']); ?>" 
                                         loading="lazy">
                                </div>
                                <div class="card-body text-center">
                                    <h5 class="product-name">
                                        <?php echo $row['ten_sp']; ?>
                                    </h5>
                                    <p class="product-price">
                                        <?php echo number_format($row['gia'], 0, ',', '.'); ?> VNĐ
                                    </p>

                                    <!-- Star Rating -->
                                    <div class="star-rating">
                                        <?php 
                                            $rating = floor($row['rating_avg']); // sao đầy
                                            for($i=1; $i<=5; $i++){
                                                if($i <= $rating){
                                                    echo '<i class="bi bi-star-fill"></i>'; // đầy sao
                                                } else {
                                                    echo '<i class="bi bi-star"></i>'; // sao rỗng
                                                }
                                            }
                                            // Hiển thị số trung bình và số lượt đánh giá
                                            if($row['rating_count'] > 0){
                                                echo ' <span class="rating-text">(' 
                                                    . round($row['rating_avg'],1) 
                                                    . ' | ' . $row['rating_count'] . ' đánh giá)</span>';
                                            } else {
                                                echo ' <span class="rating-text">(Chưa có đánh giá)</span>';
                                            }
                                        ?>
                                    </div>

                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>