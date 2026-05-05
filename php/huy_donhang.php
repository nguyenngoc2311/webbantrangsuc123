<?php
session_start();
include 'config.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['id_khachhang'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}

$id_khachhang = $_SESSION['id_khachhang'];
$id_hoadon = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_hoadon <= 0) {
    echo "<script>alert('Mã hóa đơn không hợp lệ!'); window.location.href='lichsu.php';</script>";
    exit();
}

// 2. Lấy thông tin hóa đơn để kiểm tra quyền sở hữu và trạng thái
$sql = "SELECT TrangThai FROM HoaDon WHERE IdHoaDon = ? AND id_khachhang = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Lỗi SQL (Prepare): " . $conn->error);
}

$stmt->bind_param("ii", $id_hoadon, $id_khachhang);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $hoadon = $result->fetch_assoc();
    $trang_thai = trim($hoadon['TrangThai']);

    // ĐIỀU KIỆN HỦY: Chỉ cho phép hủy khi đang "Đã Đặt Hàng"
    // (Đơn "Đã Thanh Toán" thường cần quy trình hoàn tiền riêng nên tạm thời chưa cho hủy ở đây)
    if ($trang_thai == "Đã Đặt Hàng") {
        
        $conn->begin_transaction();

        try {
            // Bước 1: Cập nhật trạng thái hóa đơn thành "Đã Hủy"
            $update_order_sql = "UPDATE HoaDon SET TrangThai = 'Đã Hủy' WHERE IdHoaDon = ?";
            $stmt_order = $conn->prepare($update_order_sql);
            if (!$stmt_order) throw new Exception("Lỗi chuẩn bị SQL Hóa đơn");
            
            $stmt_order->bind_param("i", $id_hoadon);
            $stmt_order->execute();

            // Bước 2: Cập nhật lại kho hàng vào bảng bien_the_san_pham
            // Dựa vào JOIN giữa bien_the_san_pham và chitiethoadon
            // Khớp: san_pham_id = IdSanPham AND kich_co = KichThuoc
            $update_stock_sql = "UPDATE bien_the_san_pham b
                                 INNER JOIN chitiethoadon c ON b.san_pham_id = c.IdSanPham AND b.kich_co = c.KichThuoc
                                 SET b.so_luong = b.so_luong + c.SoLuong
                                 WHERE c.IdHoaDon = ?";
            
            $stmt_stock = $conn->prepare($update_stock_sql);
            if (!$stmt_stock) {
                throw new Exception("Lỗi chuẩn bị SQL Kho hàng: " . $conn->error);
            }

            $stmt_stock->bind_param("i", $id_hoadon);
            $stmt_stock->execute();

            // Hoàn tất giao dịch
            $conn->commit();
            echo "<script>alert('Hủy đơn hàng #$id_hoadon thành công. Sản phẩm đã được hoàn lại kho!'); window.location.href='lichsu.php';</script>";

        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.history.back();</script>";
        }

    } else {
        echo "<script>alert('Không thể hủy đơn hàng này do đang ở trạng thái: $trang_thai'); window.location.href='lichsu.php';</script>";
    }
} else {
    echo "<script>alert('Hóa đơn không tồn tại hoặc bạn không có quyền truy cập.'); window.location.href='lichsu.php';</script>";
}

$conn->close();
?>