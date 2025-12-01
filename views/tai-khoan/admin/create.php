<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy flash success từ session (nếu có) và xóa
$flashSuccess = $_SESSION['flash_success'] ?? null;
if ($flashSuccess) {
    unset($_SESSION['flash_success']);
}

// Lấy errors/old từ session
$sessionErrors = $_SESSION['errors'] ?? null;
if ($sessionErrors) {
    unset($_SESSION['errors']);
}

$sessionOld = $_SESSION['old'] ?? null;
if ($sessionOld) {
    unset($_SESSION['old']);
}

// Ưu tiên dữ liệu từ controller truyền xuống, nếu không có thì lấy từ session
$errors = isset($errors) ? $errors : ($sessionErrors ?? []);
$old    = isset($old)    ? $old    : ($sessionOld    ?? []);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thêm mới tài khoản</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('admin.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <!-- Flash Success -->
                    <?php if (!empty($flashSuccess)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flashSuccess) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- General Error -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errors['general'][0]) ?>
                        </div>
                    <?php endif; ?>

                    <!-- FORM -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin người dùng</h3>
                        </div>
                        
                        <form action="<?= route('admin.store') ?>" method="POST" novalidate>
                            <div class="card-body">

                                <!-- USERNAME -->
                                <div class="form-group">
                                    <label for="username">Tên đăng nhập <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control <?= !empty($errors['username']) ? 'is-invalid' : '' ?>"
                                        id="username"
                                        name="username"
                                        placeholder="Nhập tên đăng nhập..."
                                        value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                                    <?php if (!empty($errors['username'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['username'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- PASSWORD -->
                                <div class="form-group">
                                    <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password"
                                        class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                                        id="password"
                                        name="password"
                                        placeholder="Nhập mật khẩu...">
                                    <?php if (!empty($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['password'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- PASSWORD CONFIRMATION -->
                                <div class="form-group">
                                    <label for="password_confirmation">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password"
                                        class="form-control <?= !empty($errors['password_confirmation']) ? 'is-invalid' : '' ?>"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        placeholder="Nhập lại mật khẩu...">
                                    <?php if (!empty($errors['password_confirmation'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['password_confirmation'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- FULL NAME -->
                                <div class="form-group">
                                    <label for="full_name">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>"
                                        id="full_name"
                                        name="full_name"
                                        placeholder="Nhập họ và tên..."
                                        value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                                    <?php if (!empty($errors['full_name'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['full_name'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- EMAIL -->
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email"
                                        class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                                        id="email"
                                        name="email"
                                        placeholder="Nhập địa chỉ email..."
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
                                        placeholder="Nhập số điện thoại..."
                                        value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                    <?php if (!empty($errors['phone'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['phone'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- ROLE (CHỨC VỤ) - MỚI THÊM -->
                                <div class="form-group">
                                    <label for="role">Chức vụ <span class="text-danger">*</span></label>
                                    <select class="form-control" name="role" id="role">
                                        <!-- 0: Admin, 1: HDV (Theo yêu cầu của bạn) -->
                                        <option value="0" <?= (isset($old['role']) && (string)$old['role'] === '0') ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                                        <option value="1" <?= (isset($old['role']) && (string)$old['role'] === '1') ? 'selected' : '' ?>>Hướng dẫn viên</option>
                                    </select>
                                </div>

                            </div>
                            <!-- /.card-body -->

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Thêm người dùng
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- /.card -->

                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>