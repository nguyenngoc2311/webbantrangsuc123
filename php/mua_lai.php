<?php
session_start();
include 'config.php'; 

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['id_khachhang'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_hoadon_cu = intval($_GET['id']);
    $id_khachhang = $_SESSION['id_khachhang'];

    // 2. Truy vấn lấy thông tin sản phẩm từ hóa đơn cũ
    // Chỉ lấy dữ liệu, không thực hiện thay đổi CSDL
    $sql = "SELECT c.IdSanPham, c.TenSanPham, c.SoLuong, c.DonGia, c.HinhAnh, c.KichThuoc
            FROM chitiethoadon c
            INNER JOIN hoadon h ON c.IdHoaDon = h.IdHoaDon
            WHERE c.IdHoaDon = ? AND h.id_khachhang = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Lỗi hệ thống (Prepare): " . $conn->error);
    }
    $stmt->bind_param("ii", $id_hoadon_cu, $id_khachhang);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // 3. Duyệt sản phẩm và đẩy vào Session
        while ($item = $result->fetch_assoc()) {
            $p_id    = $item['IdSanPham'];
            $p_qty   = (int)$item['SoLuong'];
            $p_size  = $item['KichThuoc']; 
            
            // Tạo key duy nhất cho sản phẩm + kích thước
            $cart_key = $p_id . '-' . $p_size; 

            // Cập nhật Session giỏ hàng
            if (isset($_SESSION['cart'][$cart_key])) {
                // Nếu đã có trong giỏ thì cộng dồn số lượng
                $_SESSION['cart'][$cart_key]['quantity'] += $p_qty;
            } else {
                // Nếu chưa có thì thêm mới
                $_SESSION['cart'][$cart_key] = [
                    'product_id' => $p_id,
                    'name'       => $item['TenSanPham'],
                    'price'      => (float)$item['DonGia'],
                    'quantity'   => $p_qty,
                    'image'      => $item['HinhAnh'],
                    'size'       => $p_size
                ];
            }
        }

        // Thông báo thành công và chuyển hướng
        echo "<script>alert('Đã thêm sản phẩm từ đơn hàng cũ vào giỏ hàng!'); window.location.href='giohang.php';</script>";
        exit();

    } else {
        echo "<script>alert('Không tìm thấy dữ liệu đơn hàng hợp lệ.'); window.location.href='lichsu.php';</script>";
    }
} else {
    header("Location: lichsu.php");
    exit();
}
?>