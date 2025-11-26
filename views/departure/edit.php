<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-edit text-info"></i> Cập nhật Đợt Khởi Hành #<?= $departure['id'] ?></h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Thông tin chi tiết</h3>
                </div>
                
                <form action="<?= route('departure.update', ['id' => $departure['id']]) ?>" method="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tour</label>
                            <input type="text" class="form-control" disabled 
                                   value="[<?= htmlspecialchars($tour['code']) ?>] <?= htmlspecialchars($tour['name']) ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ngày đi</label>
                                    <input type="date" name="start_date" class="form-control" required value="<?= $departure['start_date'] ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ngày về</label>
                                    <input type="date" name="end_date" class="form-control" required value="<?= $departure['end_date'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tổng số chỗ (Capacity) <span class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control" required 
                                           value="<?= $departure['capacity'] ?>" min="<?= $sold_seats ?>">
                                    <small class="text-danger font-weight-bold mt-1 d-block">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Đã bán: <?= $sold_seats ?> chỗ. Không thể giảm thấp hơn số này.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <select name="status" class="form-control">
                                        <option value="OPEN" <?= $departure['status'] == 'OPEN' ? 'selected' : '' ?>>OPEN (Đang nhận khách)</option>
                                        <option value="CLOSED" <?= $departure['status'] == 'CLOSED' ? 'selected' : '' ?>>CLOSED (Đã đóng/Đủ khách)</option>
                                        <option value="COMPLETED" <?= $departure['status'] == 'COMPLETED' ? 'selected' : '' ?>>COMPLETED (Đã hoàn thành)</option>
                                        <option value="CANCELLED" <?= $departure['status'] == 'CANCELLED' ? 'selected' : '' ?>>CANCELLED (Hủy chuyến)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Điểm đón khách</label>
                            <input type="text" name="pickup_point" class="form-control" value="<?= htmlspecialchars($departure['pickup_point']) ?>">
                        </div>

                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($departure['note']) ?></textarea>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="<?= route('departure.index') ?>" class="btn btn-default mr-2">Quay lại</a>
                        <button type="submit" class="btn btn-info font-weight-bold">
                            <i class="fas fa-check"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>