<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

include 'config.php'; // Tệp kết nối CSDL của bạn

// Nạp cấu hình VNPAY
if (file_exists("../vnpay_php/config.php")) {
    require_once("../vnpay_php/config.php");
}

// Lấy ID người dùng từ session (Khớp với file login của bạn)
$id_nguoidung = $_SESSION['id'] ?? null; 

// ================== 1. XỬ LÝ LOGIC ĐẦU VÀO SẢN PHẨM ==================

// Trường hợp 1: Nhấn "Mua ngay" từ trang chi tiết sản phẩm
if (isset($_POST['muangay'])) {
    $p_id = $_POST['product_id'] ?? null;
    $p_price = floatval($_POST['product_price'] ?? 0);

    if ($p_id) {
        $_SESSION['checkout_items'] = [[
            'product_id'    => $p_id,
            'product_name'  => $_POST['product_name'] ?? '',
            'product_price' => $p_price,
            'quantity'      => max(1, intval($_POST['quantity'] ?? 1)),
            'size'          => $_POST['size'] ?? 'N/A',
            'product_image' => $_POST['product_image'] ?? ''
        ]];
        $_SESSION['is_buy_now'] = true;
    }
} 
// Trường hợp 2: Nhấn "Mua hàng" từ giỏ hàng (Lấy từ bảng giohang)
elseif (isset($_POST['checkout'])) {
    if (!empty($_POST['selected_items']) && $id_nguoidung) {
        $tmp_items = [];
        // Chuyển mảng ID thành chuỗi để truy vấn SQL
        $ids = implode(',', array_map('intval', $_POST['selected_items']));
        
        $sql_select_cart = "SELECT * FROM giohang WHERE id IN ($ids) AND id_nguoidung = ?";
        $stmt_cart = $conn->prepare($sql_select_cart);
        $stmt_cart->bind_param("i", $id_nguoidung);
        $stmt_cart->execute();
        $result_cart = $stmt_cart->get_result();

        while ($item = $result_cart->fetch_assoc()) {
            // Truy vấn lại bảng sản phẩm để lấy giá mới nhất (tránh khách hàng sửa giá ở trình duyệt)
            $sql_p = "SELECT gia, gia_khuyen_mai FROM sanpham WHERE id = ?";
            $st_p = $conn->prepare($sql_p);
            $st_p->bind_param("i", $item['id_san_pham']);
            $st_p->execute();
            $res_p = $st_p->get_result()->fetch_assoc();
            $current_price = ($res_p['gia_khuyen_mai'] > 0) ? $res_p['gia_khuyen_mai'] : $res_p['gia'];

            $tmp_items[] = [
                'cart_id'       => $item['id'], 
                'product_id'    => $item['id_san_pham'], 
                'product_name'  => $item['ten_sp'],
                'product_price' => $current_price,
                'quantity'      => $item['so_luong'],
                'size'          => $item['kich_co'],
                'product_image' => $item['hinh_anh']
            ];
        }
        
        $_SESSION['selected_cart_ids'] = $_POST['selected_items'];
        $_SESSION['checkout_items'] = $tmp_items;
        $_SESSION['is_buy_now'] = false;
    } else {
        echo "<script>alert('Vui lòng chọn sản phẩm trong giỏ hàng!'); location='giohang.php';</script>";
        exit();
    }
}

// Lấy danh sách sản phẩm chờ thanh toán từ Session
$products_to_checkout = $_SESSION['checkout_items'] ?? [];

if (empty($products_to_checkout)) {
    echo "<script>alert('Không có sản phẩm để thanh toán!'); location='giohang.php';</script>";
    exit();
}

// ================== 2. TÍNH TOÁN TỔNG TIỀN ==================
$tong_tien_hang = 0;
$tong_so_luong = 0; // KHỞI TẠO BIẾN NÀY ĐỂ HẾT LỖI UNDEFINED

foreach ($products_to_checkout as $item) {
    $tong_tien_hang += ($item['product_price'] * $item['quantity']);
    $tong_so_luong += $item['quantity']; // Cộng dồn số lượng sản phẩm
}

$phi_ship = ($tong_tien_hang >= 1000000) ? 0 : 30000;
$tong_thanh_toan = $tong_tien_hang + $phi_ship;

// ================== 3. XỬ LÝ LƯU ĐƠN HÀNG KHI XÁC NHẬN ==================
if (isset($_POST['thanhtoan_confirm'])) {
    $hoten  = htmlspecialchars($_POST['hoten'] ?? '');
    $email  = htmlspecialchars($_POST['email'] ?? '');
    $sdt    = htmlspecialchars($_POST['sodienthoai'] ?? '');
    $diachi = htmlspecialchars($_POST['diachi'] ?? '');
    $pt     = $_POST['phuongthuc'] ?? 'COD';

    $conn->begin_transaction(); // Bắt đầu Transaction để đảm bảo an toàn dữ liệu
    try {
        $trangthai = ($pt == 'vnpay') ? "Chờ thanh toán" : "Đã Đặt Hàng";

        // Bước A: Lưu vào bảng hoadon
        $sql_hd = "INSERT INTO hoadon (NgayLap, TongTien, id_khachhang, HoTenKhachHang, Email, DiaChi, SoDienThoai, PhuongThucThanhToan, NgayDat, TrangThai) 
                   VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql_hd);
        $stmt->bind_param("dissssss", $tong_thanh_toan, $id_nguoidung, $hoten, $email, $diachi, $sdt, $pt, $trangthai);
        $stmt->execute();
        $id_hoadon = $conn->insert_id;

        // Bước B: Lưu chi tiết hóa đơn và xử lý kho
        foreach ($products_to_checkout as $item) {
            $t_total = $item['quantity'] * $item['product_price'];
            $sql_ct = "INSERT INTO chitiethoadon (IdHoaDon, IdSanPham, TenSanPham, KichThuoc, SoLuong, DonGia, ThanhTien, HinhAnh) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $st = $conn->prepare($sql_ct);
            $st->bind_param("iissidss", $id_hoadon, $item['product_id'], $item['product_name'], $item['size'], $item['quantity'], $item['product_price'], $t_total, $item['product_image']);
            $st->execute();

            // Nếu là COD thì trừ kho ngay
            if ($pt == 'COD') {
                $sql_update_kho = "UPDATE bien_the_san_pham SET so_luong = so_luong - ? WHERE san_pham_id = ? AND kich_co = ?";
                $up = $conn->prepare($sql_update_kho);
                $up->bind_param("iis", $item['quantity'], $item['product_id'], $item['size']);
                $up->execute();
            }
        }

        $conn->commit(); // Hoàn tất giao dịch CSDL

        // Bước C: Xử lý theo phương thức thanh toán
        if ($pt == 'vnpay') {
            // Cấu hình URL thanh toán VNPAY
            $vnp_TxnRef = $id_hoadon; 
            $vnp_OrderInfo = "Thanh toan don hang #" . $id_hoadon;
            $vnp_Amount = $tong_thanh_toan * 100;
            
            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],
                "vnp_Locale" => 'vn',
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => "billpayment",
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef
            ];

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnp_SecureHash;
            }
            header('Location: ' . $vnp_Url);
            exit();

        } else {
            // Xử lý COD thành công: Xóa sản phẩm trong giỏ hàng DB (nếu không phải Mua ngay)
            if (!empty($_SESSION['selected_cart_ids']) && !($_SESSION['is_buy_now'] ?? false)) {
                $del_ids = implode(',', array_map('intval', $_SESSION['selected_cart_ids']));
                $conn->query("DELETE FROM giohang WHERE id IN ($del_ids) AND id_nguoidung = $id_nguoidung");
            }
            
            // Dọn dẹp Session
            unset($_SESSION['checkout_items'], $_SESSION['selected_cart_ids'], $_SESSION['is_buy_now']);
            
            echo "<script>alert('Đặt hàng thành công!'); location='lichsu.php';</script>";
            exit();
        }

    } catch (Exception $e) {
        $conn->rollback(); // Lỗi thì hủy bỏ toàn bộ thay đổi CSDL
        die("Lỗi hệ thống: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="../css/thanhtoan.css">
</head>
<body>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7 form-section">
            <div style="max-width: 580px; margin-left: auto; margin-right: auto;"> 
                <h2 class="mb-4 fw-bold">Thông tin thanh toán</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">HỌ VÀ TÊN</label>
                        <input type="text" name="hoten" class="form-control" value="<?= $_SESSION['hoten'] ?? '' ?>" required placeholder="VD: Nguyễn Văn A">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label small fw-bold">EMAIL</label>
                            <input type="email" name="email" class="form-control" required placeholder="example@gmail.com">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label small fw-bold">SỐ ĐIỆN THOẠI</label>
                            <input type="tel" name="sodienthoai" class="form-control" required placeholder="09xx xxx xxx">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">ĐỊA CHỈ GIAO HÀNG</label>
                        <textarea name="diachi" class="form-control" rows="2" required placeholder="Địa chỉ chi tiết..."></textarea>
                    </div>

                    <h5 class="mt-4 mb-3 fw-bold">Phương thức thanh toán</h5>
                    <div class="card mb-4">
                        <div class="list-group list-group-flush">
                            <label class="list-group-item d-flex gap-3 py-3">
                                <input class="form-check-input" type="radio" name="phuongthuc" value="COD" checked>
                                <span>
                                    <strong class="d-block">Thanh toán khi nhận hàng (COD)</strong>
                                    <small class="text-muted">Bạn chỉ thanh toán khi đã nhận được hàng.</small>
                                </span>
                            </label>
                            <label class="list-group-item d-flex gap-3 py-3">
                                <input class="form-check-input" type="radio" name="phuongthuc" value="vnpay">
                                <span>
                                    <strong class="d-block">Thanh toán qua VNPAY</strong>
                                    <small class="text-muted">Thanh toán nhanh chóng qua ATM/Internet Banking/QR Code.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" name="thanhtoan_confirm" class="btn btn-checkout w-100 fw-bold">
                        HOÀN TẤT ĐẶT HÀNG
                    </button>
                    
                     <div class="mt-3 text-center">
                        <a href="javascript:history.back()" class="text-secondary text-decoration-none small">
                            ← Quay lại 
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5 order-summary">
            <h4 class="mb-4">Đơn hàng của bạn (<?= $tong_so_luong ?>)</h4>
            <?php foreach ($products_to_checkout as $item): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="position-relative">
                        <img src="../image/<?= htmlspecialchars($item['product_image']) ?>" class="product-img border" style="width: 60px; height: 60px; object-fit: cover;">
                    </div>

                    <div class="ms-3 flex-grow-1">
                        <div class="fw-bold text-truncate" style="max-width: 200px;">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </div>
                        <small class="text-muted">Size: <?= htmlspecialchars($item['size']) ?></small>
                    </div>

                    <div style="width: 60px; text-align: center;" class="fw-bold">
                        x <?= $item['quantity'] ?>
                    </div>

                    <div class="text-end fw-semibold" style="width: 120px;">
                        <?= number_format($item['product_price'] * $item['quantity'], 0, ',', '.') ?>đ
                    </div>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Tạm tính</span>
                <span><?= number_format($tong_tien_hang, 0, ',', '.') ?>đ</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Phí vận chuyển</span>
                <span><?= ($phi_ship == 0) ? 'Miễn phí' : number_format($phi_ship, 0, ',', '.') . 'đ' ?></span>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <span class="h5 fw-bold">Tổng cộng</span>
                <span class="h3 fw-bold text-danger"><?= number_format($tong_thanh_toan, 0, ',', '.') ?>đ</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
