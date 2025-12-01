<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<?php
// Xử lý dữ liệu Session (Flash inputs/errors)
$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old = $old ?? ($_SESSION['old'] ?? []);
unset($_SESSION['errors'], $_SESSION['old']);

// Nếu chưa có dữ liệu cũ (lần đầu vào trang), dùng dữ liệu từ DB ($user)
if (empty($old) && !empty($user)) {
    $old = $user;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chỉnh sửa tài khoản</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= isset($old['role']) && $old['role'] == 1 ? route('guide.index') : route('admin.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= $errors['general'][0] ?>
                </div>
            <?php endif; ?>

            <form action="<?= route('admin.update', ['id' => $old['id']]) ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin đăng nhập & Cá nhân</h3>
                            </div>
                            <div class="card-body">
                                
                                <div class="form-group">
                                    <label>Chức vụ <span class="text-danger">*</span></label>
                                    <select class="form-control" name="role" id="role-select">
                                        <option value="0" <?= (isset($old['role']) && $old['role'] == 0) ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                                        <option value="1" <?= (isset($old['role']) && $old['role'] == 1) ? 'selected' : '' ?>>Hướng dẫn viên</option>
                                    </select>
                                    <small class="text-muted">Lưu ý: Thay đổi chức vụ sẽ ảnh hưởng đến quyền truy cập.</small>
                                </div>

                                <div class="form-group">
                                    <label>Tên đăng nhập <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                                    <?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?= $errors['username'][0] ?></div><?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Mật khẩu mới (Để trống nếu không đổi)</label>
                                    <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="******">
                                    <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'][0] ?></div><?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Xác nhận mật khẩu mới</label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="******">
                                </div>

                                <div class="form-group">
                                    <label>Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                                    <?php if(isset($errors['full_name'])): ?><div class="invalid-feedback"><?= $errors['full_name'][0] ?></div><?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                    <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'][0] ?></div><?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                     <?php if(isset($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone'][0] ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" id="guide-info-area" style="display: none;">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Hồ sơ Hướng dẫn viên</h3>
                            </div>
                            <div class="card-body">
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Số thẻ HDV</label>
                                            <input type="text" name="license_number" class="form-control" 
                                                   value="<?= htmlspecialchars($old['license_number'] ?? '') ?>" placeholder="VD: 12345678">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Ngày hết hạn thẻ</label>
                                            <input type="date" name="license_expiry" class="form-control" 
                                                   value="<?= htmlspecialchars($old['license_expiry'] ?? '') ?>">
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
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="avatar" class="custom-file-input" id="avatarInput" accept="image/*">
                                            <label class="custom-file-label" for="avatarInput">Chọn file ảnh...</label>
                                        </div>
                                    </div>
                                    <?php if(isset($errors['avatar'])): ?>
                                        <div class="text-danger small mt-1"><?= $errors['avatar'][0] ?></div>
                                    <?php endif; ?>

                                    <div class="mt-3 text-center">
                                        <?php 
                                            // Logic hiển thị ảnh cũ hoặc placeholder
                                            $currentAvatar = !empty($old['avatar_url']) ? $old['avatar_url'] : '';
                                            $displayStyle = !empty($currentAvatar) ? '' : 'display: none;';
                                        ?>
                                        <img id="avatar-preview" src="<?= htmlspecialchars($currentAvatar) ?>" 
                                             class="img-circle elevation-2"
                                             alt="User Avatar" 
                                             style="width: 120px; height: 120px; object-fit: cover; <?= $displayStyle ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Kinh nghiệm (năm)</label>
                                    <input type="number" name="experience_years" class="form-control" 
                                           value="<?= htmlspecialchars($old['experience_years'] ?? '0') ?>">
                                </div>

                                <div class="form-group">
                                    <label>Ngôn ngữ thành thạo</label>
                                    <input type="text" name="languages" class="form-control" 
                                           value="<?= htmlspecialchars($old['languages'] ?? '') ?>" placeholder="VD: Tiếng Anh, Tiếng Trung...">
                                </div>

                                <div class="form-group">
                                    <label>Giới thiệu bản thân (Bio)</label>
                                    <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($old['bio'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-center mb-4">
                        <button type="submit" class="btn btn-warning btn-lg px-5">
                            <i class="fas fa-save"></i> Cập nhật thông tin
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<script>
    $(document).ready(function() {
        // 1. Hàm kiểm tra để hiện/ẩn form HDV
        function toggleGuideInfo() {
            var role = $('#role-select').val();
            if (role == '1') { // 1 là HDV
                $('#guide-info-area').slideDown();
            } else {
                $('#guide-info-area').slideUp();
            }
        }

        toggleGuideInfo();
        $('#role-select').change(function() {
            toggleGuideInfo();
        });

        // 2. Hàm xử lý Preview ảnh khi chọn file
        $('#avatarInput').change(function(e) {
            // Cập nhật tên file vào label (nếu dùng Bootstrap custom file input)
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                $('.custom-file-label').html(fileName);
                
                // Đọc file và hiển thị preview
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#avatar-preview').attr('src', e.target.result);
                    $('#avatar-preview').show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>