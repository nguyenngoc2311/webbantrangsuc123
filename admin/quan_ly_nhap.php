<?php
include '../php/config.php';

// ===== LẤY DANH SÁCH PHIẾU NHẬP =====
$sql = "SELECT pn.id, pn.idnhanvien, nv.hoten AS hotennhanvien,
               pn.tongtien, pn.ngaynhap
        FROM phieunhap pn
        LEFT JOIN nhanvien nv ON pn.idnhanvien = nv.id
        ORDER BY pn.id DESC";

$result = $conn->query($sql);

// ===== XÓA PHIẾU NHẬP =====
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);

    // Xóa chi tiết trước
    $stmt1 = $conn->prepare("DELETE FROM chitietphieunhap WHERE phieu_nhap_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // Xóa phiếu nhập
    $stmt2 = $conn->prepare("DELETE FROM phieunhap WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    header("Location: quan_ly_nhap.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhập hàng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/qln.css">
</head>

<body class="bg-light">

<div class="main-container">

    <!-- Sidebar -->
    <div class="sidebar">
        <?php include 'header.php'; ?>
    </div>

    <!-- Content -->
    <div class="content">
        <main class="container-fluid">

            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h2 class="fw-bold">QUẢN LÝ PHIẾU NHẬP</h2>
            </div>

            <div class="card shadow-sm border-0">
                
                <div class="card-header bg-white d-flex justify-content-between">
                    <span class="badge bg-info text-dark">
                        Tổng: <?php echo $result->num_rows; ?> phiếu
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <!-- HEADER -->
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="100">Mã PN</th>
                                    <th width="300">Nhân viên thực hiện</th>
                                    <th class="text-end">Tổng tiền nhập</th>
                                    <th class="text-center">Ngày nhập</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>

                            <!-- BODY -->
                            <tbody>

                            <?php
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {

                                    $ngay = date("d/m/Y H:i", strtotime($row['ngaynhap']));

                                    echo "<tr>";

                                    // ID
                                    echo "<td class='text-center fw-bold text-primary'>#{$row['id']}</td>";

                                    // Nhân viên
                                    echo "<td>
                                            <div class='d-flex align-items-center'>
                                                <div class='bg-secondary bg-opacity-10 p-2 rounded-circle me-2'>
                                                    <i class='bi bi-person'></i>
                                                </div>
                                                <div>
                                                    <div class='fw-bold'>{$row['hotennhanvien']}</div>
                                                    <small class='text-muted'>ID: {$row['idnhanvien']}</small>
                                                </div>
                                            </div>
                                          </td>";

                                    // Tổng tiền
                                    echo "<td class='text-end fw-bold text-danger'>
                                            " . number_format($row['tongtien'], 0, ',', '.') . " ₫
                                          </td>";

                                    // Ngày nhập
                                    echo "<td class='text-center text-muted'>
                                            <small>{$ngay}</small>
                                          </td>";

                                    // Thao tác
                                    echo "<td class='text-center'>
                                            <div class='btn-group'>

                                                <a href='chi_tiet.php?id={$row['id']}'
                                                   class='btn btn-sm btn-outline-info'>
                                                    <i class='bi bi-eye'></i>
                                                </a>

                                                <form method='POST'
                                                      onsubmit=\"return confirm('Xóa phiếu nhập này?');\">
                                                    <input type='hidden' name='delete_id' value='{$row['id']}'>
                                                    <button type='submit'
                                                            class='btn btn-sm btn-outline-danger'>
                                                        <i class='bi bi-trash'></i>
                                                    </button>
                                                </form>

                                            </div>
                                          </td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='5' class='text-center py-5 text-muted'>
                                            Không có dữ liệu
                                        </td>
                                      </tr>";
                            }
                            ?>

                            </tbody>
                        </table>

                    </div>
                </div>

            </div>

        </main>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>