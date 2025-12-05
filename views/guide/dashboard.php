<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Bàn làm việc của HDV</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- 1. CÁC THẺ THỐNG KÊ (INFO BOXES) -->
            <div class="row">
                <!-- Tour sắp tới -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-map-marked-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tour sắp tới</span>
                            <span class="info-box-number">
                                <?= number_format($upcomingCount ?? 0) ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Tour đã dẫn -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tour đã dẫn</span>
                            <span class="info-box-number">
                                <?= number_format($completedCount ?? 0) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Đánh giá -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-star text-white"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Đánh giá</span>
                            <span class="info-box-number">
                                <?= number_format($profile['rating'] ?? 5.0, 1) ?> / 5.0
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Hồ sơ cá nhân -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Hồ sơ</span>
                            <a href="<?= route('profile.index') ?>" class="small-box-footer">
                                Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. DANH SÁCH LỊCH TRÌNH HÔM NAY -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="far fa-calendar-check mr-1"></i> 
                                Lịch trình hôm nay (<?= date('d/m/Y') ?>)
                            </h3>
                        </div>
                        
                        <div class="card-body p-0">
                            <?php if (!empty($todayTours)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover m-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Mã Tour</th>
                                                <th>Tên Tour</th>
                                                <th>Thời gian</th>
                                                <th>Vai trò</th>
                                                <th>Điểm đón</th>
                                                <th class="text-right" style="width: 150px;">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todayTours as $tour): ?>
                                                <tr>
                                                    <td class="align-middle">
                                                        <span class="badge badge-info"><?= htmlspecialchars($tour['tour_code']) ?></span>
                                                    </td>
                                                    <td class="align-middle font-weight-bold">
                                                        <?= htmlspecialchars($tour['tour_name']) ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <small class="d-block text-muted">Đi: <?= date('d/m/Y', strtotime($tour['start_date'])) ?></small>
                                                        <small class="d-block text-muted">Về: <?= date('d/m/Y', strtotime($tour['dep_end_date'])) ?></small>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?php if ($tour['role'] == 'MAIN'): ?>
                                                            <span class="badge badge-warning">Trưởng đoàn</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Phụ tá</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?= htmlspecialchars($tour['pickup_point'] ?? '---') ?>
                                                    </td>
                                                    <td class="align-middle text-right">
                                                        <!-- Nút Điểm danh nhanh -->
                                                        <a href="<?= route('guide.attendance') ?>?departure_id=<?= $tour['departure_id'] ?>" 
                                                           class="btn btn-success btn-sm font-weight-bold">
                                                            <i class="fas fa-clipboard-check"></i> Điểm danh
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-mug-hot fa-3x text-gray mb-3" style="opacity: 0.3;"></i>
                                    <p class="text-muted">
                                        Hôm nay bạn không có lịch trình dẫn tour nào.<br>
                                        Hãy tận hưởng ngày nghỉ hoặc kiểm tra lại lịch phân công.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>