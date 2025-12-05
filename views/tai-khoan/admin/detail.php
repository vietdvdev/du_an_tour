<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hồ sơ chi tiết</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <!-- Nút quay lại -->
                    <!-- SỬA LỖI TẠI ĐÂY: Đổi 'guide.index' thành 'admin.guide.index' -->
                    <a href="<?= $user['role'] == 1 ? route('admin.guide.index') : route('admin.index') ?>" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    
                    <!-- Nút chuyển sang trang Sửa -->
                    <a href="<?= route('admin.edit', ['id' => $user['id']]) ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                
                <!-- CỘT TRÁI: THÔNG TIN TÓM TẮT & AVATAR -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <?php 
                                    // Xử lý ảnh đại diện
                                    $avatar = $profile['avatar_url'] ?? '/assets/img/user-default.png';
                                    if (empty($avatar)) $avatar = '/assets/img/user-default.png';
                                ?>
                                <img class="profile-user-img img-fluid img-circle" 
                                     src="<?= public_url($avatar) ?>" 
                                     alt="User profile picture"
                                     style="width: 128px; height: 128px; object-fit: cover;">
                            </div>

                            <h3 class="profile-username text-center"><?= htmlspecialchars($user['full_name']) ?></h3>

                            <p class="text-muted text-center">
                                <?php if($user['role'] == 1): ?>
                                    <span class="badge badge-info">Hướng dẫn viên</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Quản trị viên</span>
                                <?php endif; ?>
                            </p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Tên đăng nhập</b> <a class="float-right"><?= htmlspecialchars($user['username']) ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Trạng thái</b> 
                                    <a class="float-right">
                                        <?= $user['is_active'] == 1 
                                            ? '<span class="badge badge-success">Hoạt động</span>' 
                                            : '<span class="badge badge-secondary">Khóa</span>' ?>
                                    </a>
                                </li>
                                <li class="list-group-item">
                                    <b>Ngày tham gia</b> <a class="float-right"><?= date('d/m/Y', strtotime($user['created_at'])) ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- THÔNG TIN LIÊN HỆ -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Liên hệ</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                            <hr>
                            <strong><i class="fas fa-phone mr-1"></i> Số điện thoại</strong>
                            <p class="text-muted"><?= htmlspecialchars($user['phone']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- CỘT PHẢI: CHI TIẾT HỒ SƠ -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Thông tin chi tiết</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="activity">
                                    
                                    <?php if ($user['role'] == 1 && !empty($profile)): ?>
                                        <!-- DÀNH CHO HƯỚNG DẪN VIÊN -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-id-card mr-1"></i> Số thẻ HDV</strong>
                                                <p class="text-muted"><?= htmlspecialchars($profile['license_number'] ?? 'Chưa cập nhật') ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="far fa-calendar-times mr-1"></i> Ngày hết hạn thẻ</strong>
                                                <p class="text-muted"><?= !empty($profile['license_expiry']) ? date('d/m/Y', strtotime($profile['license_expiry'])) : '---' ?></p>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-venus-mars mr-1"></i> Giới tính</strong>
                                                <p class="text-muted">
                                                    <?php 
                                                        $g = $profile['gender'] ?? 'OTHER';
                                                        echo $g == 'MALE' ? 'Nam' : ($g == 'FEMALE' ? 'Nữ' : 'Khác');
                                                    ?>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-birthday-cake mr-1"></i> Ngày sinh</strong>
                                                <p class="text-muted"><?= !empty($profile['dob']) ? date('d/m/Y', strtotime($profile['dob'])) : '---' ?></p>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-briefcase mr-1"></i> Kinh nghiệm</strong>
                                                <p class="text-muted"><?= htmlspecialchars($profile['experience_years'] ?? '0') ?> năm</p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-language mr-1"></i> Ngôn ngữ</strong>
                                                <p class="text-muted"><?= htmlspecialchars($profile['languages'] ?? 'Chưa cập nhật') ?></p>
                                            </div>
                                        </div>
                                        <hr>

                                        <strong><i class="fas fa-star mr-1"></i> Đánh giá trung bình</strong>
                                        <p class="text-warning">
                                            <?= htmlspecialchars($profile['rating'] ?? '5.0') ?> / 5.0 <i class="fas fa-star"></i>
                                        </p>
                                        <hr>

                                        <strong><i class="far fa-file-alt mr-1"></i> Giới thiệu (Bio)</strong>
                                        <p class="text-muted">
                                            <?= nl2br(htmlspecialchars($profile['bio'] ?? 'Chưa có thông tin giới thiệu.')) ?>
                                        </p>

                                    <?php else: ?>
                                        <!-- DÀNH CHO ADMIN HOẶC HDV CHƯA CÓ PROFILE -->
                                        <div class="alert alert-info">
                                            <h5><i class="icon fas fa-info"></i> Thông báo!</h5>
                                            <?php if ($user['role'] == 0): ?>
                                                Đây là tài khoản Quản trị viên, không có thông tin hồ sơ chi tiết (Guide Profile).
                                            <?php else: ?>
                                                Tài khoản Hướng dẫn viên này chưa cập nhật hồ sơ chi tiết.
                                                <br>Vui lòng bấm nút <b>"Chỉnh sửa"</b> để cập nhật.
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>