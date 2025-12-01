<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Bàn làm việc của HDV</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-map-marked-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tour sắp tới</span>
                            <span class="info-box-number">0</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tour đã dẫn</span>
                            <span class="info-box-number">0</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Đánh giá</span>
                            <span class="info-box-number">5.0</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Hồ sơ</span>
                            <span class="info-box-number">Cập nhật</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Lịch trình hôm nay</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center">
                                <i class="fas fa-mug-hot fa-3x mb-3"></i><br>
                                Hiện tại bạn chưa có lịch trình nào hôm nay.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>