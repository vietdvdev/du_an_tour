<!-- Header -->
<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Flash success
$flashSuccess = $_SESSION['flash_success'] ?? null;
if ($flashSuccess) unset($_SESSION['flash_success']);

// Lấy old + errors
$errors = $errors ?? [];
$old    = $old    ?? [];

if (!empty($user) && empty($old)) {
    $old = $user; // Lần đầu vào form → hiển thị dữ liệu từ DB
}
?>

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <h1>Chỉnh sửa tài khoản quản trị</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Flash success -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($flashSuccess) ?>
                </div>
            <?php endif; ?>

            <!-- Lỗi chung -->
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errors['general'][0]) ?>
                </div>
            <?php endif; ?>

            <form action="<?= route('admin.update', ['id' => $old['id']]) ?>" method="POST" novalidate>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text"
                           name="username"
                           id="username"
                           class="form-control <?= !empty($errors['username']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                    <?php if (!empty($errors['username'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['username'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Mật khẩu mới (để trống nếu không đổi)</label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>">
                    <?php if (!empty($errors['password'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['password'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Confirm -->
                <div class="form-group">
                    <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="form-control <?= !empty($errors['password_confirmation']) ? 'is-invalid' : '' ?>">
                    <?php if (!empty($errors['password_confirmation'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['password_confirmation'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Full name -->
                <div class="form-group">
                    <label for="full_name">Họ tên</label>
                    <input type="text"
                           name="full_name"
                           id="full_name"
                           class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                    <?php if (!empty($errors['full_name'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['full_name'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['email'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           class="form-control <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['phone'][0]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="<?= route('admin.index') ?>" class="btn btn-secondary">Quay lại</a>

            </form>

        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
