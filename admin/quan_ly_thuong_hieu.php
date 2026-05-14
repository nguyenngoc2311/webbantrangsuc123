<?php
include('../php/config.php');

// ================= THÊM THƯƠNG HIỆU =================
if (isset($_POST['add_brand'])) {

    $ten_thuong_hieu = mysqli_real_escape_string($conn, $_POST['ten_thuong_hieu']);

    $sql = "INSERT INTO thuong_hieu(ten_thuong_hieu)
            VALUES('$ten_thuong_hieu')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Thêm thương hiệu thành công!');
                window.location.href='quan_ly_thuong_hieu.php';
              </script>";
        exit;
    }
}

// ================= SỬA THƯƠNG HIỆU =================
if (isset($_POST['edit_brand_submit'])) {

    $id = (int)$_POST['id'];
    $ten_thuong_hieu = mysqli_real_escape_string($conn, $_POST['ten_thuong_hieu']);

    $sql = "UPDATE thuong_hieu
            SET ten_thuong_hieu='$ten_thuong_hieu'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Cập nhật thương hiệu thành công!');
                window.location.href='quan_ly_thuong_hieu.php';
              </script>";
        exit;
    }
}

// ================= XÓA THƯƠNG HIỆU =================
if (isset($_GET['delete_id'])) {

    $id = (int)$_GET['delete_id'];

    $sql = "DELETE FROM thuong_hieu WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Xóa thương hiệu thành công!');
                window.location.href='quan_ly_thuong_hieu.php';
              </script>";
        exit;
    }
}

// ================= LẤY DANH SÁCH =================
$result = mysqli_query($conn, "SELECT * FROM thuong_hieu ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản Lý Thương Hiệu | Luxury Jewelry</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../css/qlth.css">
</head>

<body>

<?php include('header.php'); ?>

<div class="main-content">

    <div class="container-fluid">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 card p-4 shadow-sm border-0 flex-row bg-white">

            <div>
                <h4 class="mb-1 fw-bold text-dark">
                    Quản Lý Thương Hiệu
                </h4>

                <p class="text-muted small mb-0">
                    Danh sách các thương hiệu trang sức trong hệ thống
                </p>
            </div>

            <button class="btn btn-primary px-4 py-2 shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#addBrandModal">

                <i class="bi bi-plus-lg me-2"></i>
                Thêm Thương Hiệu
            </button>
        </div>

        <!-- TABLE -->
        <div class="card shadow-sm border-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>
                    <tr>
                        <th class="text-center" style="width:100px;">
                            Mã ID
                        </th>

                        <th>
                            Tên Thương Hiệu
                        </th>

                        <th class="text-center">
                            Thao Tác
                        </th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td class="text-center fw-bold text-muted">
                                #<?php echo $row['id']; ?>
                            </td>

                            <td class="fw-semibold">
                                <?php echo $row['ten_thuong_hieu']; ?>
                            </td>

                            <td>

                                <div class="d-flex justify-content-center gap-2">

                                    <!-- NÚT SỬA -->
                                    <button class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editBrandModal<?php echo $row['id']; ?>">

                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- NÚT XÓA -->
                                    <a href="?delete_id=<?php echo $row['id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa thương hiệu này?')">

                                        <i class="bi bi-trash"></i>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- ================= MODAL THÊM ================= -->
<div class="modal fade" id="addBrandModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Thêm Thương Hiệu
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Tên thương hiệu
                        </label>

                        <input type="text"
                               name="ten_thuong_hieu"
                               class="form-control"
                               required>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit"
                            name="add_brand"
                            class="btn btn-primary">

                        Thêm
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- ================= MODAL SỬA ================= -->
<?php
$result_modal = mysqli_query($conn, "SELECT * FROM thuong_hieu ORDER BY id ASC");

while ($edit = mysqli_fetch_assoc($result_modal)):
?>

<div class="modal fade"
     id="editBrandModal<?php echo $edit['id']; ?>"
     tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Sửa Thương Hiệu
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <input type="hidden"
                           name="id"
                           value="<?php echo $edit['id']; ?>">

                    <div class="mb-3">

                        <label class="form-label">
                            Tên thương hiệu
                        </label>

                        <input type="text"
                               name="ten_thuong_hieu"
                               class="form-control"
                               value="<?php echo $edit['ten_thuong_hieu']; ?>"
                               required>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit"
                            name="edit_brand_submit"
                            class="btn btn-warning">

                        Cập Nhật
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endwhile; ?>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
