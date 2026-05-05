<?php
session_start();
include 'config.php';

// Kiểm tra đăng nhập và tham số ID đơn hàng
if (!isset($_SESSION['id_khachhang']) || !isset($_GET['id'])) {
    header("Location: lichsu.php");
    exit();
}

$id_hoadon = intval($_GET['id']);
$id_khachhang = $_SESSION['id_khachhang'];

$sql = "UPDATE HoaDon 
        SET TrangThai = 'Hoàn Thành' 
        WHERE IdHoaDon = ? AND id_khachhang = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_hoadon, $id_khachhang);

if ($stmt->execute()) {
    // Nếu thành công, dùng JavaScript thông báo và đẩy về tab "Đã giao"
    echo "<script>
            alert('Xác nhận thành công! Bạn có thể viết đánh giá cho sản phẩm ngay bây giờ.');
            window.location.href = 'lichsu.php?filter=da_giao';
          </script>";
} else {
    // Nếu lỗi
    echo "<script>
            alert('Có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại!');
            window.history.back();
          </script>";
}

$stmt->close();
$conn->close();
?>