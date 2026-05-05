<?php
session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = (int)$_POST['cart_id'];
    $id_nguoidung = $_SESSION['id'];

    $sql = "DELETE FROM giohang WHERE id = ? AND id_nguoidung = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $id_nguoidung);
    $stmt->execute();
}

header("Location: giohang.php");
exit();