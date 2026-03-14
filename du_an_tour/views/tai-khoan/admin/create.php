<!-- Phần header -->
<?php include __DIR__ . '/../../layout/header.php'; ?>

<!-- Phần Navbar -->
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<!-- Phần Navbar -->
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<!-- Phần nỗi dung -->

<!-- Phần nỗi dung -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy flash success từ session (nếu có) và xóa
$flashSuccess = $_SESSION['flash_success'] ?? null;
if ($flashSuccess) {
    unset($_SESSION['flash_success']);
}

// Lấy errors/old từ session (nếu bạn từng lưu chúng vào session)
$sessionErrors = $_SESSION['errors'] ?? null;
if ($sessionErrors) {
    unset($_SESSION['errors']);
}

$sessionOld = $_SESSION['old'] ?? null;
if ($sessionOld) {
    unset($_SESSION['old']);
}

// Nếu controller truyền $errors/$old bằng render(), dùng nó; nếu không, fallback vào session.
$errors = isset($errors) ? $errors : ($sessionErrors ?? []);
$old    = isset($old)    ? $old    : ($sessionOld    ?? []);
?>
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

                    <!-- Flash success -->
                    <?php if (!empty($flashSuccess)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flashSuccess) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- General error (nếu controller trả render với 'general') -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errors['general'][0]) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= route('admin.store') ?>" method="POST" novalidate>

                        <!-- USERNAME -->
                        <div class="form-group">
                            <label for="username">Tên đăng nhập</label>
                            <input type="text"
                                class="form-control <?= !empty($errors['username']) ? 'is-invalid' : '' ?>"
                                id="username"
                                name="username"
                                value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                            <?php if (!empty($errors['username'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['username'][0]) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- PASSWORD -->
                        <div class="form-group">
                            <label for="password">Mật khẩu</label>
                            <input type="password"
                                class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                                id="password"
                                name="password">
                            <?php if (!empty($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password'][0]) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- PASSWORD CONFIRMATION -->
                        <div class="form-group">
                            <label for="password_confirmation">Xác nhận mật khẩu</label>
                            <input type="password"
                                class="form-control <?= !empty($errors['password_confirmation']) ? 'is-invalid' : '' ?>"
                                id="password_confirmation"
                                name="password_confirmation">
                            <?php if (!empty($errors['password_confirmation'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password_confirmation'][0]) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- FULL NAME -->
                        <div class="form-group">
                            <label for="full_name">Họ tên</label>
                            <input type="text"
                                class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>"
                                id="full_name"
                                name="full_name"
                                value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                            <?php if (!empty($errors['full_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['full_name'][0]) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- EMAIL -->
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email"
                                class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                            <?php if (!empty($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['email'][0]) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- PHONE -->
                        <div class="form-group">
                            <label for="phone">Số điện thoại</label>
                            <input type="text"
                                class="form-control <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>"
                                id="phone"
                                name="phone"
                                value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                            <?php if (!empty($errors['phone'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['phone'][0]) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- GENERAL ERROR (ví dụ lỗi DB, catch exception) -->
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($errors['general'][0]) ?>
                            </div>
                        <?php endif; ?>

                        <!-- SUBMIT -->
                        <button type="submit" class="btn btn-primary">Thêm người dùng</button>
                    </form>


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
<!-- Code injected by live-server -->


<!-- Phần Footer -->