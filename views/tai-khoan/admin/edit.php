<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<?php
// Xử lý dữ liệu Session (Flash inputs/errors)
$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old = $old ?? ($_SESSION['old'] ?? []);
unset($_SESSION['errors'], $_SESSION['old']);

// Nếu chưa có dữ liệu cũ (lần đầu vào trang), dùng dữ liệu từ DB ($user đã gộp profile ở Controller)
if (empty($old) && !empty($user)) {
    // $user ở đây đã được Controller gộp (merge) thông tin từ bảng guide_profile nếu có
    $old = $user;
}
?>

<div class="content-wrapper">
    <!-- Header -->
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

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Flash Success -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <!-- General Error -->
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= $errors['general'][0] ?>
                </div>
            <?php endif; ?>

            <!-- Form Edit (Có upload file) -->
            <form action="<?= route('admin.update', ['id' => $old['id']]) ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    
                    <!-- CỘT TRÁI: THÔNG TIN CƠ BẢN (Bảng users) -->
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin đăng nhập & Cá nhân</h3>
                            </div>
                            <div class="card-body">
                                
                                <!-- ROLE -->
                                <div class="form-group">
                                    <label>Chức vụ <span class="text-danger">*</span></label>
                                    <select class="form-control" name="role" id="role-select">
                                        <option value="0" <?= (isset($old['role']) && (string)$old['role'] === '0') ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                                        <option value="1" <?= (isset($old['role']) && (string)$old['role'] === '1') ? 'selected' : '' ?>>Hướng dẫn viên</option>
                                    </select>
                                    <small class="text-muted"><i class="fas fa-info-circle"></i> Chọn "Hướng dẫn viên" để nhập thêm hồ sơ chi tiết.</small>
                                </div>

                                <!-- USERNAME -->
                                <div class="form-group">
                                    <label>Tên đăng nhập <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['username'] ?? '') ?>">
                                    <?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?= $errors['username'][0] ?></div><?php endif; ?>
                                </div>

                                <!-- PASSWORD -->
                                <div class="form-group">
                                    <label>Mật khẩu mới (Để trống nếu không đổi)</label>
                                    <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="******">
                                    <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'][0] ?></div><?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Xác nhận mật khẩu mới</label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="******">
                                </div>

                                <!-- FULL NAME -->
                                <div class="form-group">
                                    <label>Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                                    <?php if(isset($errors['full_name'])): ?><div class="invalid-feedback"><?= $errors['full_name'][0] ?></div><?php endif; ?>
                                </div>

                                <!-- EMAIL -->
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                    <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'][0] ?></div><?php endif; ?>
                                </div>

                                <!-- PHONE -->
                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                     <?php if(isset($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone'][0] ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CỘT PHẢI: THÔNG TIN HỒ SƠ GUIDE (Bảng guide_profile) -->
                    <!-- Mặc định ẩn, JS sẽ hiện nếu Role = 1 -->
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

                                <!-- UPLOAD ẢNH ĐẠI DIỆN -->
                                <div class="form-group">
                                    <label>Ảnh đại diện</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="avatar" name="avatar" accept="image/*">
                                        <label class="custom-file-label" for="avatar">Chọn file ảnh mới...</label>
                                    </div>
                                    
                                    <!-- Hiển thị ảnh hiện tại nếu có -->
                                    <?php if(!empty($old['avatar_url'])): ?>
                                        <div class="mt-2 p-2 text-center" style="background: #f8f9fa; border: 1px dashed #ced4da; border-radius: 5px;">
                                            <small class="d-block mb-1 text-muted">Ảnh hiện tại:</small>
                                            <img src="<?= htmlspecialchars($old['avatar_url']) ?>" alt="Current Avatar" class="img-circle elevation-2" style="height: 80px; width: 80px; object-fit: cover;">
                                        </div>
                                    <?php endif; ?>
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

<!-- Script xử lý giao diện -->
<script>
    $(document).ready(function() {
        // 1. Hàm kiểm tra chức vụ để hiện/ẩn form HDV
        function toggleGuideInfo() {
            var role = $('#role-select').val();
            if (role == '1') { // 1 là HDV
                $('#guide-info-area').slideDown();
            } else {
                $('#guide-info-area').slideUp();
            }
        }

        // Chạy ngay khi load trang để hiển thị đúng trạng thái ban đầu
        toggleGuideInfo();

        // Chạy khi người dùng thay đổi select box
        $('#role-select').change(function() {
            toggleGuideInfo();
        });

        // 2. Hiển thị tên file khi chọn (Custom File Input Bootstrap)
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>