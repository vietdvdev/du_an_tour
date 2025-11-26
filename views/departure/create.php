<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-plus-circle text-primary"></i> Tạo Đợt Khởi Hành Mới</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông tin chi tiết</h3>
                </div>
                
                <form action="<?= route('departure.store') ?>" method="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Chọn Tour <span class="text-danger">*</span></label>
                            <select name="tour_id" class="form-control select2" required>
                                <option value="">-- Chọn Tour --</option>
                                <?php foreach ($tours as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= (isset($old['tour_id']) && $old['tour_id'] == $t['id']) ? 'selected' : '' ?>>
                                        [<?= $t['code'] ?>] <?= $t['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if(isset($errors['tour_id'])): ?><div class="text-danger small"><?= $errors['tour_id'] ?></div><?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ngày đi (Start Date) <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" required value="<?= $old['start_date'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ngày về (End Date) <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" required value="<?= $old['end_date'] ?? '' ?>">
                                    <?php if(isset($errors['end_date'])): ?><div class="text-danger small"><?= $errors['end_date'][0] ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tổng số chỗ (Capacity) <span class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control" min="1" required value="<?= $old['capacity'] ?? 20 ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Điểm đón khách</label>
                                    <input type="text" name="pickup_point" class="form-control" placeholder="VD: Sân bay Nội Bài..." value="<?= $old['pickup_point'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Ghi chú nội bộ</label>
                            <textarea name="note" class="form-control" rows="3"><?= $old['note'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="<?= route('departure.index') ?>" class="btn btn-default mr-2">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-save"></i> Lưu Đợt Khởi Hành
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>