<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ===== 1. CẤU HÌNH HỆ THỐNG =====
// Đảm bảo đường dẫn này đúng để kết nối database ($conn)
include '../php/config.php'; 
// Chứa $vnp_HashSecret
require_once("config.php"); 

$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';

// ===== 2. XỬ LÝ DỮ LIỆU VNPAY GỬI VỀ =====
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData); 

$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

if (!isset($vnp_HashSecret)) {
    die("Lỗi: Chưa cấu hình vnp_HashSecret trong file config.");
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret); 

// Lấy thông tin từ VNPAY
$vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
$vnp_TxnRef = $_GET['vnp_TxnRef'] ?? ''; 
$vnp_Amount = $_GET['vnp_Amount'] ?? 0;
$vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? 'N/A';

// Tách ID hóa đơn (Ví dụ: "123_17123456" -> 123)
$order_ref = explode('_', $vnp_TxnRef);
$order_id = (int)$order_ref[0];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KẾT QUẢ THANH TOÁN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; }
        .result-icon { font-size: 3rem; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4 shadow-sm">
                <h3 class="text-center mb-4 text-primary">Kết quả thanh toán</h3>

                <?php
                // 3. KIỂM TRA CHỮ KÝ BẢO MẬT
                if ($secureHash === $vnp_SecureHash) {
                    
                    // 4. KIỂM TRA MÃ PHẢN HỒI (00 = THÀNH CÔNG)
                    if ($vnp_ResponseCode == '00') {
                        
                        if (isset($conn)) {
                            // Bước A: Kiểm tra xem đơn hàng đã được cập nhật trước đó chưa
                            $sql_check = "SELECT TrangThai FROM hoadon WHERE IdHoaDon = ?";
                            $stmt_check = $conn->prepare($sql_check);
                            $stmt_check->bind_param("i", $order_id);
                            $stmt_check->execute();
                            $res_check = $stmt_check->get_result();
                            $orderData = $res_check->fetch_assoc();

                            if ($orderData && ($orderData['TrangThai'] == 'Đã Thanh Toán' || $orderData['TrangThai'] == 'Đã xác nhận')) {
                                echo "<div class='alert alert-info text-center'>Đơn hàng này đã được hệ thống xử lý trước đó.</div>";
                            } else {
                                // BẮT ĐẦU TRANSACTION ĐỂ ĐẢM BẢO DỮ LIỆU ĐỒNG NHẤT
                                $conn->begin_transaction();
                                try {
                                    // Bước B: Lấy chi tiết đơn hàng (Lấy IdSanPham, SoLuong và KichThuoc)
                                    $sql_ct = "SELECT IdSanPham, SoLuong, KichThuoc FROM chitiethoadon WHERE IdHoaDon = ?";
                                    $stmt_ct = $conn->prepare($sql_ct);
                                    $stmt_ct->bind_param("i", $order_id);
                                    $stmt_ct->execute();
                                    $result_ct = $stmt_ct->get_result();

                                    while ($row = $result_ct->fetch_assoc()) {
                                        $id_sp = $row['IdSanPham'];
                                        $qty_mua = $row['SoLuong'];
                                        $size_mua = $row['KichThuoc'];

                                        // Bước B1: Kiểm tra tồn kho tại bảng biến thể (khớp theo ID và Size)
                                        $sql_stock = "SELECT so_luong FROM bien_the_san_pham WHERE san_pham_id = ? AND kich_co = ? FOR UPDATE";
                                        $stmt_stock = $conn->prepare($sql_stock);
                                        
                                        if (!$stmt_stock) {
                                            throw new Exception("Lỗi truy vấn kho: " . $conn->error);
                                        }

                                        $stmt_stock->bind_param("is", $id_sp, $size_mua);
                                        $stmt_stock->execute();
                                        $stock_data = $stmt_stock->get_result()->fetch_assoc();

                                        if (!$stock_data) {
                                            throw new Exception("Không tìm thấy Sản phẩm ID $id_sp - Size $size_mua trong kho!");
                                        }

                                        if ($stock_data['so_luong'] < $qty_mua) {
                                            throw new Exception("Sản phẩm ID $id_sp (Size $size_mua) không đủ số lượng tồn kho!");
                                        }

                                        // Bước B2: Cập nhật trừ kho
                                        $sql_update_stock = "UPDATE bien_the_san_pham SET so_luong = so_luong - ? WHERE san_pham_id = ? AND kich_co = ?";
                                        $up_stmt = $conn->prepare($sql_update_stock);
                                        $up_stmt->bind_param("iis", $qty_mua, $id_sp, $size_mua);
                                        $up_stmt->execute();
                                    }

                                    // Bước C: Cập nhật trạng thái hóa đơn sang "Đã Thanh Toán"
                                    $sql_update_hd = "UPDATE hoadon SET TrangThai = 'Đã Thanh Toán' WHERE IdHoaDon = ?";
                                    $stmt_hd = $conn->prepare($sql_update_hd);
                                    $stmt_hd->bind_param("i", $order_id);
                                    $stmt_hd->execute();

                                    $conn->commit();

                                    // Xóa giỏ hàng
                                    unset($_SESSION['cart']);
                                    unset($_SESSION['buy_now']);

                                    echo "<div class='text-center mb-3'><span class='result-icon text-success'>✔</span></div>";
                                    echo "<div class='alert alert-success fw-bold text-center'>Thanh toán thành công và đã cập nhật kho!</div>";

                                } catch (Exception $e) {
                                    $conn->rollback();
                                    echo "<div class='alert alert-danger'><b>Lỗi xử lý:</b> " . $e->getMessage() . "</div>";
                                }
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Lỗi: Không tìm thấy kết nối Database ($conn).</div>";
                        }
                    } else {
                        echo "<div class='text-center mb-3'><span class='result-icon text-danger'>✘</span></div>";
                        echo "<div class='alert alert-danger text-center'>Giao dịch thất bại! Mã lỗi: $vnp_ResponseCode</div>";
                    }
                } else {
                    echo "<div class='alert alert-warning text-center'>Chữ ký không hợp lệ! Giao dịch có dấu hiệu bị can thiệp.</div>";
                }
                ?>

                <hr>
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted">Mã đơn hàng:</td>
                        <td class="fw-bold text-end"><?php echo htmlspecialchars($vnp_TxnRef); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Số tiền:</td>
                        <td class="fw-bold text-danger text-end"><?php echo number_format($vnp_Amount / 100, 0, ',', '.'); ?> đ</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Mã giao dịch VNPAY:</td>
                        <td class="fw-bold text-end"><?php echo htmlspecialchars($vnp_TransactionNo); ?></td>
                    </tr>
                </table>

                <div class="d-grid gap-2 mt-4">
                    <a href="../index.php" class="btn btn-primary btn-lg">Quay về trang chủ</a>
                    <a href="../php/lichsu.php" class="btn btn-outline-secondary">Lịch sử đơn hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>