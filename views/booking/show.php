<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Đơn hàng: <strong><?= htmlspecialchars($booking['code'] ?? 'N/A') ?></strong></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('booking.index') ?>" class="btn btn-default"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    
                    <!-- Nút Hủy chỉ hiện khi chưa Hủy -->
                    <?php if (($booking['state'] ?? '') !== 'CANCELLED'): ?>
                        <form action="<?= route('booking.cancel', ['id' => $booking['id']]) ?>" method="POST" style="display:inline" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này? Hành động không thể hoàn tác và sẽ trả lại chỗ trống.')">
                            <button type="submit" class="btn btn-danger ml-2"><i class="fas fa-times-circle"></i> Hủy Đơn</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Thông báo -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Cột trái: Thông tin chính -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <h3 class="profile-username text-center"><?= htmlspecialchars($booking['contact_name'] ?? 'Không tên') ?></h3>
                            <p class="text-muted text-center"><?= htmlspecialchars($booking['contact_phone'] ?? 'Không SĐT') ?></p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Trạng thái</b> 
                                    <span class="float-right badge <?= ($booking['state']=='CANCELLED')?'badge-danger':'badge-success' ?>">
                                        <?= htmlspecialchars($booking['state'] ?? '') ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Ngày đặt</b> <span class="float-right"><?= date('d/m/Y', strtotime($booking['created_at'])) ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b>Tổng khách</b> <span class="float-right font-weight-bold text-primary" style="font-size: 1.2rem"><?= $booking['pax_count'] ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b>Tổng tiền (Tạm tính)</b> 
                                    <span class="float-right text-success font-weight-bold">
                                        <?= number_format($booking['total_amount'] ?? 0) ?> đ
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-header"><h3 class="card-title">Thông tin Tour</h3></div>
                        <div class="card-body">
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Tour</strong>
                            <p class="text-muted"><?= htmlspecialchars($booking['tour_name'] ?? '') ?></p>
                            <hr>
                            <strong><i class="far fa-calendar-alt mr-1"></i> Lịch trình</strong>
                            <p class="text-muted">
                                Đi: <?= date('d/m/Y', strtotime($booking['start_date'])) ?><br>
                                Về: <?= date('d/m/Y', strtotime($booking['end_date'])) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Danh sách khách & Dịch vụ -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#travelers" data-toggle="tab">Danh sách Khách</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="travelers">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th style="width: 10px">#</th>
                                                <th>Họ và tên</th>
                                                <th>Giới tính</th>
                                                <th>Ngày sinh</th>
                                                <th>Ghi chú</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($travelers)): ?>
                                                <tr><td colspan="5" class="text-center">Chưa có thông tin khách hàng</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($travelers as $idx => $t): ?>
                                                <tr>
                                                    <td><?= $idx + 1 ?></td>
                                                    <td class="font-weight-bold"><?= htmlspecialchars($t['full_name'] ?? '') ?></td>
                                                    <td>
                                                        <?php 
                                                            $g = $t['gender'] ?? 'OTHER';
                                                            echo ($g == 'MALE') ? 'Nam' : (($g == 'FEMALE') ? 'Nữ' : 'Khác');
                                                        ?>
                                                    </td>
                                                    <td><?= !empty($t['dob']) ? date('d/m/Y', strtotime($t['dob'])) : '-' ?></td>
                                                    <td><?= htmlspecialchars($t['note'] ?? '') ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>