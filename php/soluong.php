<?php
session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = (int)$_POST['cart_id'];
    $action = $_POST['action'];
    $id_nguoidung = $_SESSION['id'];

    if ($action === 'increase') {
        $sql = "UPDATE giohang SET so_luong = so_luong + 1 WHERE id = ? AND id_nguoidung = ?";
    } elseif ($action === 'decrease') {
        // Không cho giảm xuống dưới 1
        $sql = "UPDATE giohang SET so_luong = GREATEST(so_luong - 1, 1) WHERE id = ? AND id_nguoidung = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $id_nguoidung);
    $stmt->execute();
}

header("Location: giohang.php"); // Quay lại giỏ hàng
exit();