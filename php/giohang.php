<?php
session_start();
include('config.php'); // Kết nối CSDL của bạn
$message = '';

// Lấy ID người dùng từ session (đảm bảo bạn đã lưu Idnguoidung khi đăng nhập)
$id_nguoidung = $_SESSION['id'] ?? null;

// ===== 1. Thêm sản phẩm vào CSDL =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$id_nguoidung) {
        echo "<script>alert('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng!'); window.location.href = 'login.php';</script>";
        exit();
    } else {
        $product_id   = $_POST['product_id'];
        $product_name = $_POST['product_name'];
        $price        = !empty($_POST['product_price']) ? $_POST['product_price'] : 0;
        $quantity     = (int)$_POST['quantity'];
        $image        = $_POST['product_image'];
        $size         = $_POST['size'];

        // Lấy ID biến thể dựa trên product_id và size
        $sql_variant = "SELECT id FROM bien_the_san_pham WHERE san_pham_id = ? AND kich_co = ?";
        $stmt_var = $conn->prepare($sql_variant);
        $stmt_var->bind_param("is", $product_id, $size);
        $stmt_var->execute();
        $res_var = $stmt_var->get_result();
        $variant = $res_var->fetch_assoc();
        $id_bien_the = $variant['id'];

        // Kiểm tra sản phẩm đã có trong giỏ hàng CSDL chưa
        $sql_check = "SELECT id, so_luong FROM giohang WHERE id_nguoidung = ? AND id_bien_the = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $id_nguoidung, $id_bien_the);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            // Đã có thì cập nhật số lượng
            $cart_item = $res_check->fetch_assoc();
            $new_qty = $cart_item['so_luong'] + $quantity;
            $sql_update = "UPDATE giohang SET so_luong = ? WHERE id = ?";
            $stmt_up = $conn->prepare($sql_update);
            $stmt_up->bind_param("ii", $new_qty, $cart_item['id']);
            $stmt_up->execute();
        } else {
            // Chưa có thì chèn mới (Thêm cả hinh_anh, ten_sp, kich_co theo ý bạn)
            $sql_insert = "INSERT INTO giohang (id_nguoidung, id_san_pham, id_bien_the, ten_sp, hinh_anh, kich_co, so_luong) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($sql_insert);
            $stmt_ins->bind_param("iiisssi", $id_nguoidung, $product_id, $id_bien_the, $product_name, $image, $size, $quantity);
            $stmt_ins->execute();
        }

        echo "<script>alert('Sản phẩm đã được thêm vào giỏ hàng!'); window.location.href = 'giohang.php';</script>";
        exit();
    }
}

// ===== 2. Lấy dữ liệu giỏ hàng từ DATABASE =====
$cart_items = [];
$total = 0;
if ($id_nguoidung) {
    // Join với bảng sanpham để lấy giá (gia hoặc gia_khuyen_mai)
    $sql_cart = "SELECT g.*, s.gia, s.gia_khuyen_mai 
                 FROM giohang g 
                 JOIN sanpham s ON g.id_san_pham = s.id 
                 WHERE g.id_nguoidung = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $id_nguoidung);
    $stmt_cart->execute();
    $result = $stmt_cart->get_result();
    while ($row = $result->fetch_assoc()) {
        $price = ($row['gia_khuyen_mai'] > 0) ? $row['gia_khuyen_mai'] : $row['gia'];
        $row['current_price'] = $price; // Lưu giá hiện tại để tính toán
        $cart_items[] = $row;
        $total += $price * $row['so_luong'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="../css/giohang.css">
</head>
<body>

<?php include('menu.php'); ?>

<div class="cart-container">
    <?php if($message != ''): ?>
        <p style="color:red; font-weight:bold; text-align:center;"><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="cart-left">
        <h2>Giỏ hàng của bạn</h2>
        <p>Có <?php echo count($cart_items); ?> sản phẩm trong giỏ hàng</p>
        
        <table class="cart-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"> Chọn tất cả</th>
                    <th>Sản phẩm</th>
                    <th>Thông tin</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tổng tiền</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($cart_items)): ?>
                    <tr><td colspan="7" style="text-align:center;">Giỏ hàng trống</td></tr>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="selected_items[]" value="<?php echo $item['id']; ?>" 
                                   form="main-cart-form" checked class="item-checkbox">
                        </td>
                        <td>
                            <img src="../image/<?php echo urlencode($item['hinh_anh']); ?>" width="80" class="product-image">
                        </td>
                        <td>
                            <p><strong><?php echo $item['ten_sp']; ?></strong></p>
                            <p>Size: <?php echo $item['kich_co']; ?></p>
                        </td>
                        <td><?php echo number_format($item['current_price'], 0, ',', '.') . " đ"; ?></td>
                        <td>
                            <form action="soluong.php" method="POST" class="quantity-control-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="action" value="decrease" class="quantity-btn">-</button>
                                <span class="quantity"><?php echo $item['so_luong']; ?></span>
                                <button type="submit" name="action" value="increase" class="quantity-btn">+</button>
                            </form>
                        </td>
                        <td class="item-total">
                            <?php echo number_format($item['current_price'] * $item['so_luong'], 0, ',', '.') . " đ"; ?>
                        </td>
                        <td>
                            <form action="xoasp.php" method="POST">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <form id="main-cart-form" action="thanhtoan.php" method="POST" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary" name="checkout">Mua hàng</button>
        </form>
    </div>

    <div class="cart-right">
        <h4>Tóm tắt đơn hàng</h4>
        <p>Tổng tiền hàng: <span id="total-price"><?php echo number_format($total, 0, ',', '.') . " đ"; ?></span></p>
        <p>Tạm tính: <span id="subtotal"><?php echo number_format($total, 0, ',', '.') . " đ"; ?></span></p>
        <p style="color:red; font-size: 1.2em;">
            Tổng tiền: <span id="final-total"><?php echo number_format($total, 0, ',', '.') . " đ"; ?></span>
        </p>
        <button class="btn btn-secondary" onclick="window.location.href='index.php';">Tiếp Tục Mua Sắm</button>
    </div>
</div>

<script src="../js/cart.js"></script>

<?php include('footer.php'); ?>
</body>
</html>