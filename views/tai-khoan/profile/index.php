<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hồ sơ cá nhân</h1>
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

            <div class="row">
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <?php 
                                    $avatar = $profile['avatar_url'] ?? '/assets/img/user-default.png';
                                    if(empty($avatar)) $avatar = '/assets/img/user-default.png';
                                ?>
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?= htmlspecialchars(public_url($avatar)) ?>"
                                     alt="User profile picture" style="width:128px; height:128px; object-fit:cover;">
                            </div>

                            <h3 class="profile-username text-center"><?= htmlspecialchars($user['full_name']) ?></h3>
                            <p class="text-muted text-center">
                                <?= $user['role'] == 1 ? 'Hướng dẫn viên' : 'Quản trị viên' ?>
                            </p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Tên đăng nhập</b> <a class="float-right"><?= htmlspecialchars($user['username']) ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Email</b> <a class="float-right"><?= htmlspecialchars($user['email']) ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Điện thoại</b> <a class="float-right"><?= htmlspecialchars($user['phone']) ?></a>
                                </li>
                            </ul>

                            <a href="<?= route('profile.edit') ?>" class="btn btn-primary btn-block"><b>Chỉnh sửa thông tin</b></a>
                        </div>
                    </div>
                </div>
                
                <!-- Thông tin chi tiết (Chỉ hiện nếu là HDV) -->
                <?php if ($user['role'] == 1): ?>
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin nghề nghiệp</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-id-card mr-1"></i> Số thẻ HDV</strong>
                            <p class="text-muted"><?= htmlspecialchars($profile['license_number'] ?? 'Chưa cập nhật') ?></p>
                            <hr>
                            
                            <strong><i class="fas fa-briefcase mr-1"></i> Kinh nghiệm</strong>
                            <p class="text-muted"><?= htmlspecialchars($profile['experience_years'] ?? '0') ?> năm</p>
                            <hr>

                            <strong><i class="fas fa-language mr-1"></i> Ngôn ngữ</strong>
                            <p class="text-muted"><?= htmlspecialchars($profile['languages'] ?? 'Chưa cập nhật') ?></p>
                            <hr>

                            <strong><i class="far fa-file-alt mr-1"></i> Giới thiệu</strong>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($profile['bio'] ?? '')) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>