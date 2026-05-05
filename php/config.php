<?php
// 1. Kết nối Database
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "trangsuc_db1"; 
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8");

