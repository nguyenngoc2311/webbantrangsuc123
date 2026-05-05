<?php
include 'config.php'; 
include 'menu.php';  

$conn->set_charset("utf8mb4");

// ===== LẤY KEYWORD =====
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// ===== LƯU TỪ KHÓA TÌM KIẾM =====
if (!empty($keyword)) {
    $stmt_log = $conn->prepare("INSERT INTO tu_khoa_tim_kiem (tu_khoa) VALUES (?)");
    $stmt_log->bind_param("s", $keyword);
    $stmt_log->execute();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/timkiem.css">
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-dark">
                Kết quả cho: 
                <span class="text-primary">"<?php echo htmlspecialchars($keyword); ?>"</span>
            </h4>
            <hr>
        </div>
    </div>

    <div class="row g-4">

        <?php
        if (!empty($keyword)) {

            $sql = "
                SELECT sp.*, 
                       AVG(d.so_sao) AS trung_binh_sao, 
                       COUNT(d.ma_danh_gia) AS tong_luot_dg 
                FROM sanpham sp 
                LEFT JOIN danh_gia d ON sp.id = d.san_pham_id 
                WHERE sp.ten_sp LIKE ?
                GROUP BY sp.id
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("SQL lỗi: " . $conn->error);
            }

            $search_term = "%$keyword%";
            $stmt->bind_param("s", $search_term);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    $rating = round($row['trung_binh_sao']);
                    $total_reviews = $row['tong_luot_dg'];
        ?>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="card h-100 border-0 shadow-sm product-card">

                <a href="chitiet.php?id=<?php echo $row['id']; ?>" 
                   class="text-decoration-none text-dark h-100 d-flex flex-column">

                    <div class="product-img-container">
                        <img src="../image/<?php echo htmlspecialchars($row['hinh_anh']); ?>" 
                             alt="">
                    </div>

                    <div class="card-body text-center d-flex flex-column pt-0">

                        <h5 class="fw-bold mb-2">
                            <?php echo htmlspecialchars($row['ten_sp']); ?>
                        </h5>

                        <p class="text-danger fw-bold fs-5 mb-2">
                            <?php echo number_format($row['gia'], 0, ',', '.'); ?>đ
                        </p>

                        <div class="mt-auto">

                            <div class="mb-1">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    echo ($i <= $rating) 
                                        ? '<span class="text-warning">★</span>' 
                                        : '<span class="text-secondary">★</span>';
                                }
                                ?>
                            </div>

                            <small class="text-muted">
                                <?php echo ($total_reviews > 0) 
                                    ? "($total_reviews đánh giá)" 
                                    : "(Chưa có đánh giá)"; ?>
                            </small>

                        </div>
                    </div>

                </a>

            </div>
        </div>

        <?php
                }

            } else {
                echo '
                <div class="col-12 text-center py-5">
                    <h5 class="text-muted">Không tìm thấy sản phẩm nào!</h5>
                    <a href="index.php" class="btn btn-primary mt-3">Quay lại trang chủ</a>
                </div>';
            }

            $stmt->close();

        } else {
            echo '
            <div class="col-12 text-center py-5">
                <h5 class="text-muted">Vui lòng nhập từ khóa tìm kiếm!</h5>
            </div>';
        }
        ?>

    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>