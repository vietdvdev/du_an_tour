<!-- Phần header -->
<?php include __DIR__ . '/../../layout/header.php'; ?>

<!-- Phần Navbar -->
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<!-- Phần Navbar -->
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<!-- Phần nỗi dung -->

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý tài khoản quản trị viên</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['flash_success'];
                            unset($_SESSION['flash_success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['flash_error'];
                            unset($_SESSION['flash_error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $flashError = $_SESSION['flash_error'] ?? null;
                    if ($flashError) {
                        echo '<div class="alert alert-danger">' . htmlspecialchars($flashError) . '</div>';
                        unset($_SESSION['flash_error']);
                    }
                    ?>

                    <div class="card">
                        <div class="card-header">
                            <a href="<?= route('admin.create') ?>">
                                <button class="btn btn-success"> Thêm tài khoản</button>
                            </a>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên người dùng</th>
                                        <th>Email </th>
                                        <th>Số điện thoại</th>
                                        <th>Chức vụ</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ListUsers as $key => $Users) { ?>
                                        <tr>
                                            <td><?= $key + 1 ?></td>
                                            <td><?= $Users['full_name'] ?></td>
                                            <td><?= $Users['email'] ?></td>
                                            <td><?= $Users['phone'] ?></td>
                                            <td><?= $Users['role'] == 1 ? 'Admin' : 'HDV' ?></td>
                                            <td>
                                                <a href="<?= htmlspecialchars(route('admin.edit', ['id' => $Users['id']])) ?>">
                                                    <button class="btn btn-warning"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                                            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0" />
                                                            <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z" />
                                                        </svg></button> </a>
                                                <a href="<?= htmlspecialchars(route('admin.delete', ['id' => $Users['id']])) ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                    <button class="btn btn-danger"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                                                        </svg></button> </a>
                                            </td>
                                        </tr>

                                    <?php } ?>


                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên người dùng</th>
                                        <th>Email </th>
                                        <th>Số điện thoại</th>
                                        <th>Chức vụ</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- <footer> -->
<?php include __DIR__ . '/../../layout/footer.php'; ?>
<!-- endforeach -->
<!-- Page specific script -->
<script>
    $(document).ready(function() {
        // Nếu DataTable đã tồn tại => destroy để tránh reinit lỗi
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }

        let table = $('#example1').DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
        });

        table.buttons().container()
            .appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
<!-- Code injected by live-server -->



<!-- Phần Footer -->