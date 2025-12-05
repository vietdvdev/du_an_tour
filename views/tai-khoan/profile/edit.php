<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<?php
$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old = $old ?? ($_SESSION['old'] ?? []);
unset($_SESSION['errors'], $_SESSION['old']);

// Nếu lần đầu vào, chưa có input cũ thì dùng dữ liệu từ DB
if (empty($old) && !empty($user)) {
    // Gộp user và profile (nếu có) đã được controller xử lý
    $old = $user; 
    // Lưu ý: Controller editProfile đã gộp sẵn rồi nên $old ở đây chứa đủ thông tin
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cập nhật hồ sơ</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('profile.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= $errors['general'][0] ?></div>
            <?php endif; ?>

            <form action="<?= route('profile.update') ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header"><h3 class="card-title">Thông tin tài khoản</h3></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Tên đăng nhập</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                    <small class="text-muted">Không thể thay đổi tên đăng nhập.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                                    <?php if(isset($errors['full_name'])): ?><div class="invalid-feedback"><?= $errors['full_name'][0] ?></div><?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                    <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'][0] ?></div><?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                </div>

                                <hr>
                                <div class="form-group">
                                    <label>Mật khẩu mới (Để trống nếu không đổi)</label>
                                    <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
                                    <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'][0] ?></div><?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Xác nhận mật khẩu</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NẾU LÀ HDV THÌ HIỆN THÊM CỘT BÊN PHẢI -->
                    <?php if ((int)$user['role'] === 1): ?>
                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header"><h3 class="card-title">Thông tin Hướng dẫn viên</h3></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Số thẻ HDV</label>
                                            <input type="text" name="license_number" class="form-control" value="<?= htmlspecialchars($old['license_number'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Ngày hết hạn thẻ</label>
                                            <input type="date" name="license_expiry" class="form-control" value="<?= htmlspecialchars($old['license_expiry'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Ngày sinh</label>
                                            <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($old['dob'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Giới tính</label>
                                            <select name="gender" class="form-control">
                                                <option value="MALE" <?= (isset($old['gender']) && $old['gender'] == 'MALE') ? 'selected' : '' ?>>Nam</option>
                                                <option value="FEMALE" <?= (isset($old['gender']) && $old['gender'] == 'FEMALE') ? 'selected' : '' ?>>Nữ</option>
                                                <option value="OTHER" <?= (isset($old['gender']) && $old['gender'] == 'OTHER') ? 'selected' : '' ?>>Khác</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Ảnh đại diện</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="avatar" name="avatar" accept="image/*">
                                        <label class="custom-file-label" for="avatar">Chọn ảnh mới...</label>
                                    </div>
                                    <?php if(!empty($old['avatar_url'])): ?>
                                        <div class="mt-2">
                                            <img src="<?= htmlspecialchars(public_url($old['avatar_url'])) ?>" style="height: 80px; border-radius: 5px;">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Kinh nghiệm (năm)</label>
                                    <input type="number" name="experience_years" class="form-control" value="<?= htmlspecialchars($old['experience_years'] ?? '0') ?>">
                                </div>

                                <div class="form-group">
                                    <label>Ngôn ngữ thành thạo</label>
                                    <input type="text" name="languages" class="form-control" value="<?= htmlspecialchars($old['languages'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label>Giới thiệu bản thân</label>
                                    <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($old['bio'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-12 text-center mb-4">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-save"></i> Cập nhật hồ sơ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<script>
    // Hiển thị tên file khi chọn
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>