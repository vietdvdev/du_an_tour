<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Đặt chỗ</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('booking.create') ?>" class="btn btn-success font-weight-bold">
                        <i class="fas fa-plus mr-1"></i> Tạo Booking Mới
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>

            <div class="card card-outline card-primary">
                <div class="card-body">
                    <table id="bookingTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Mã Booking</th>
                                <th>Tour / Ngày đi</th>
                                <th>Người liên hệ</th>
                                <th class="text-center">Số khách</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Ngày đặt</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $bk): 
                                // Logic màu trạng thái
                                $badgeClass = match($bk['state']) {
                                    'PLACED' => 'badge-info', // Mới đặt
                                    'DEPOSITED' => 'badge-primary', // Đã cọc
                                    'COMPLETED' => 'badge-success', // Hoàn thành
                                    'CANCELLED' => 'badge-danger', // Đã hủy
                                    default => 'badge-secondary'
                                };
                            ?>
                            <tr>
                                <td class="align-middle font-weight-bold text-primary">
                                    <?= htmlspecialchars($bk['code']) ?>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold"><?= htmlspecialchars($bk['tour_name']) ?></div>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($bk['start_date'])) ?>
                                    </small>
                                </td>
                                <td class="align-middle">
                                    <div><?= htmlspecialchars($bk['contact_name']) ?></div>
                                    <small><i class="fas fa-phone fa-xs"></i> <?= htmlspecialchars($bk['contact_phone']) ?></small>
                                </td>
                                <td class="text-center align-middle font-weight-bold">
                                    <?= $bk['pax_count'] ?>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge <?= $badgeClass ?> p-2"><?= $bk['state'] ?></span>
                                </td>
                                <td class="text-center align-middle text-muted">
                                    <?= date('d/m/Y H:i', strtotime($bk['created_at'])) ?>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="<?= route('booking.show', ['id' => $bk['id']]) ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
<script>
    $(function () {
        $('#bookingTable').DataTable({
            "order": [[ 5, "desc" ]], // Sắp xếp theo ngày đặt mới nhất
            "language": { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json" }
        });
    });
</script>