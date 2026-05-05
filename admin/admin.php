<?php include('header.php'); ?>
<?php 
include('../php/config.php');

// ======================
// 1. TỔNG SẢN PHẨM
// ======================
$sql_products = "SELECT COUNT(*) AS total_products FROM sanpham";
$result_products = $conn->query($sql_products);
$row_products = $result_products->fetch_assoc();
$total_products = $row_products['total_products'] ?? 0;

// ======================
// 2. TỔNG ĐƠN HOÀN THÀNH
// ======================
$sql_orders = "SELECT COUNT(*) AS total_orders 
               FROM hoadon 
               WHERE TrangThai = 'Hoàn Thành'";
$result_orders = $conn->query($sql_orders);
$row_orders = $result_orders->fetch_assoc();
$total_orders = $row_orders['total_orders'] ?? 0;

// ======================
// 3. DOANH THU
// ======================
$sql_revenue = "SELECT SUM(TongTien) AS total_revenue 
                FROM hoadon 
                WHERE TrangThai = 'Hoàn Thành'";
$result_revenue = $conn->query($sql_revenue);
$row_revenue = $result_revenue->fetch_assoc();
$total_revenue = $row_revenue['total_revenue'] ?? 0;

// ======================
// 4. DANH SÁCH ĐƠN HÀNG
// ======================
$sql_list_orders = "
    SELECT IdHoaDon, HoTenKhachHang, SoDienThoai, TongTien, NgayLap, TrangThai
    FROM hoadon
    WHERE TrangThai = 'Hoàn Thành'
    ORDER BY NgayLap DESC
    LIMIT 10
";
$result_list_orders = $conn->query($sql_list_orders);
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-9 main-content">

            <h3 class="text-center">Dashboard quản lý</h3>

            <!-- THỐNG KÊ -->
            <div class="row mt-4">

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h5>Sản phẩm</h5>
                            <h3 class="text-success"><?php echo $total_products; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h5>Đơn hoàn thành</h5>
                            <h3 class="text-primary"><?php echo $total_orders; ?></h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- DOANH THU -->
            <div class="card mt-4 shadow-sm">
                <div class="card-body">

                    <h5 class="text-center">Tổng doanh thu</h5>
                    <h3 class="text-center text-success">
                        <?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ
                    </h3>

                    <!-- DANH SÁCH ĐƠN -->
                    <hr>
                    <h5 class="mt-4">Đơn hàng hoàn thành</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mt-3">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>SĐT</th>
                                    <th>Tổng tiền</th>
                                    <th>Ngày lập</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_list_orders && $result_list_orders->num_rows > 0): ?>
                                    <?php while ($row = $result_list_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $row['IdHoaDon']; ?></td>
                                            <td><?php echo $row['HoTenKhachHang']; ?></td>
                                            <td><?php echo $row['SoDienThoai']; ?></td>
                                            <td class="text-end text-danger">
                                                <?php echo number_format($row['TongTien'], 0, ',', '.'); ?> VNĐ
                                            </td>
                                            <td class="text-center">
                                                <?php echo date('d/m/Y', strtotime($row['NgayLap'])); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">
                                                    <?php echo $row['TrangThai']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Không có đơn hàng</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
