<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1><i class="fas fa-map-marked-alt text-info"></i> Thiết lập Lịch trình Tour</h1>
                    <small class="text-muted">Tour: <?= htmlspecialchars($booking['tour_name']) ?></small>
                </div>
            </div>
        </div>
    </section>


    <section class="content">
        <div class="container-fluid">
           
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>
           
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>


            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        Thời gian: <b><?= date('d/m/Y', strtotime($booking['start_date'])) ?></b>
                        đến <b><?= date('d/m/Y', strtotime($booking['end_date'])) ?></b>
                        (Tổng: <span class="badge badge-warning"><?= $numDays ?> Ngày</span>)
                    </h3>
                </div>
               
                <form action="<?= route('booking.custom.update_itinerary', ['id' => $booking['id']]) ?>" method="POST">
                    <div class="card-body">
                        <div class="alert alert-light border">
                            <i class="fas fa-info-circle text-info"></i> Hãy nhập tiêu đề và nội dung hoạt động cho từng ngày. Thông tin này sẽ được gửi cho khách hàng.
                        </div>


                        <!-- Vòng lặp tạo ô nhập theo số ngày -->
                        <?php for ($i = 1; $i <= $numDays; $i++):
                            // Lấy dữ liệu cũ nếu đã từng nhập
                            $currentData = $itineraryMap[$i] ?? [];
                            // Tính ngày dương lịch tương ứng
                            $currentDate = date('d/m/Y', strtotime($booking['start_date'] . ' + ' . ($i - 1) . ' days'));
                        ?>
                            <div class="card mb-3 border border-light shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="card-title text-primary font-weight-bold">
                                        Ngày <?= $i ?> <small class="text-muted">(<?= $currentDate ?>)</small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Tiêu đề ngày </label>
                                        <input type="text" name="itinerary[<?= $i ?>][title]" class="form-control"
                                               placeholder="Nhập tiêu đề cho ngày <?= $i ?>..."
                                               value="<?= htmlspecialchars($currentData['title'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nội dung chi tiết</label>
                                        <textarea name="itinerary[<?= $i ?>][content]" class="form-control" rows="3"
                                                  placeholder="Mô tả các hoạt động, điểm tham quan, ăn uống..."><?= htmlspecialchars($currentData['content'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                   
                    <div class="card-footer text-center">
                        <a href="<?= route('booking.show', ['id' => $booking['id']]) ?>" class="btn btn-default mr-2">
                            Bỏ qua (Làm sau)
                        </a>
                        <button type="submit" class="btn btn-success btn-lg px-5 font-weight-bold">
                            <i class="fas fa-save"></i> HOÀN TẤT & LƯU
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>


<?php include __DIR__ . '/../layout/footer.php'; ?>

