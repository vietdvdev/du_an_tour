<!-- Card Hồ sơ khách & Tài chính -->
<div class="card card-primary card-outline">
    <div class="card-body box-profile">
        <h3 class="profile-username text-center"><?= htmlspecialchars($booking['contact_name'] ?? '---') ?></h3>
        <p class="text-muted text-center"><i class="fas fa-phone"></i> <?= htmlspecialchars($booking['contact_phone'] ?? '---') ?></p>
        <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
                <b>Trạng thái</b> 
                <span class="float-right badge <?= ($booking['state']=='COMPLETED')?'badge-success':($booking['state']=='CANCELLED'?'badge-danger':'badge-warning') ?>">
                    <?= $booking['state'] ?>
                </span>
            </li>
            <li class="list-group-item">
                <b>Tổng tiền</b> <span class="float-right text-success font-weight-bold"><?= number_format($booking['total_amount'] ?? 0) ?> đ</span>
            </li>
            <li class="list-group-item">
                <b>Đã thanh toán</b> <span class="float-right text-primary font-weight-bold"><?= number_format($booking['paid_amount'] ?? 0) ?> đ</span>
            </li>
            <li class="list-group-item">
                <b>Còn thiếu</b> <span class="float-right text-danger font-weight-bold"><?= number_format(($booking['total_amount']??0) - ($booking['paid_amount']??0)) ?> đ</span>
            </li>
        </ul>
    </div>
</div>

<!-- Cập nhật trạng thái -->
<div class="card card-warning card-outline">
    <div class="card-header"><h3 class="card-title">Cập nhật trạng thái</h3></div>
    <div class="card-body">
        <form action="<?= route('booking.update.status', ['id' => $booking['id']]) ?>" method="POST">
            <div class="form-group">
                <select name="status" class="form-control">
                    <option value="PLACED" <?= $booking['state']=='PLACED'?'selected':'' ?>>PLACED (Chờ xác nhận)</option>
                    <option value="DEPOSITED" <?= $booking['state']=='DEPOSITED'?'selected':'' ?>>DEPOSITED (Đã cọc)</option>
                    <option value="COMPLETED" <?= $booking['state']=='COMPLETED'?'selected':'' ?>>COMPLETED (Hoàn tất)</option>
                    <option value="CANCELLED" <?= $booking['state']=='CANCELLED'?'selected':'' ?>>CANCELLED (Hủy)</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="note" class="form-control form-control-sm" placeholder="Ghi chú...">
            </div>
            <button type="submit" class="btn btn-block btn-warning btn-sm font-weight-bold">Cập nhật</button>
        </form>
    </div>
</div>

<!-- Card Tour -->
<div class="card card-info">
    <div class="card-header"><h3 class="card-title">Thông tin Tour</h3></div>
    <div class="card-body">
        <strong><i class="fas fa-map-marker-alt mr-1"></i> Tour</strong>
        <p class="text-muted">
            [<?= htmlspecialchars($booking['tour_code'] ?? '') ?>] 
            <?= htmlspecialchars($booking['tour_name'] ?? '') ?>
        </p>
        <hr>
        <strong><i class="far fa-calendar-alt mr-1"></i> Lịch trình</strong>
        <p class="text-muted">
            Đi: <?= date('d/m/Y', strtotime($booking['start_date'])) ?><br>
            Về: <?= date('d/m/Y', strtotime($booking['end_date'])) ?>
        </p>
    </div>
</div>