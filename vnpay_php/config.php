<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

/*
 * Cấu hình VNPAY khớp với biến trong file thanh toán
 */
$vnp_TmnCode = "S1WII163"; // Mã định danh thương nhân
$vnp_HashSecret = "ZDLD4Z1M3YMO1P1729WMN54G3O1GPXZ0"; // Chuỗi bí mật
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://localhost/WEBBANTRANGSUC/vnpay_php/vnpay_return.php";

// Thời gian hết hạn (Nếu cần dùng thêm trong mảng inputData)
$startTime = date("YmdHis");
$expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));
?>