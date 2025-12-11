<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<!-- Hàm hỗ trợ tạo URL lọc giữ nguyên các tham số khác -->
<?php
function filterUrl($key, $value) {
    $params = $_GET;
    $params[$key] = $value;
    return '?' . http_build_query($params);
}
?>


<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Đặt chỗ</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('booking.custom.create') ?>" class="btn btn-success font-weight-bold ml-2">
                        <i class="fas fa-magic mr-1"></i> Booking Tour yêu cầu
                    </a>
                    <a href="<?= route('booking.create') ?>" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-plus mr-1"></i> Thêm Booking Mới
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
           
            <!-- BLOCK LỌC DỮ LIỆU -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-body p-2">
                    <div class="row align-items-center">
                        <!-- MENU TABS: LOẠI BOOKING -->
                        <div class="col-md-8">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link <?= ($filterType == 'all') ? 'active' : '' ?>"
                                       href="<?= filterUrl('type', 'all') ?>">
                                       <i class="fas fa-list"></i> Tất cả
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= ($filterType == 'system') ? 'active' : '' ?>"
                                       href="<?= filterUrl('type', 'system') ?>">
                                       <i class="fas fa-box"></i> Tour Hệ Thống
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= ($filterType == 'custom') ? 'active' : '' ?>"
                                       href="<?= filterUrl('type', 'custom') ?>">
                                       <i class="fas fa-swatchbook"></i> Tour Yêu Cầu
                                    </a>
                                </li>
                            </ul>
                        </div>


                        <!-- FILTER: THỜI GIAN -->
                        <div class="col-md-4">
                            <form method="GET" action="" class="form-inline justify-content-end">
                                <!-- Giữ lại type khi submit form select -->
                                <input type="hidden" name="type" value="<?= htmlspecialchars($filterType) ?>">
                               
                                <label class="mr-2 font-weight-normal text-muted">Thời gian:</label>
                                <select name="time" class="form-control custom-select" onchange="this.form.submit()">
                                    <option value="all" <?= ($filterTime == 'all') ? 'selected' : '' ?>>Tất cả</option>
                                    <option value="today" <?= ($filterTime == 'today') ? 'selected' : '' ?>>Hôm nay</option>
                                    <option value="week" <?= ($filterTime == 'week') ? 'selected' : '' ?>>Tuần này</option>
                                    <option value="month" <?= ($filterTime == 'month') ? 'selected' : '' ?>>Tháng này</option>
                                    <option value="year" <?= ($filterTime == 'year') ? 'selected' : '' ?>>Năm nay</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END BLOCK LỌC -->


            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>


            <div class="card">
                <div class="card-body">
                    <table id="bookingTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr class="bg-light">
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
                            <?php if(empty($bookings)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Không tìm thấy dữ liệu phù hợp.</td></tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $bk):
                                    $badgeClass = match($bk['state']) {
                                        'PLACED' => 'badge-info',
                                        'DEPOSITED' => 'badge-primary',
                                        'COMPLETED' => 'badge-success',
                                        'CANCELLED' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                ?>
                                <tr>
                                    <td class="align-middle font-weight-bold text-primary">
                                        <?= htmlspecialchars($bk['code']) ?>
                                        <?php if($filterType == 'all'): ?>
                                            <?php if(isset($bk['tour_type']) && $bk['tour_type'] == 'custom'): ?>
                                                <br><span class="badge badge-warning badge-pill" style="font-size: 0.7em">Custom</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($bk['tour_name']) ?></div>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt text-info"></i> KH: <?= date('d/m/Y', strtotime($bk['start_date'])) ?>
                                        </small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold"><?= htmlspecialchars($bk['contact_name']) ?></div>
                                        <small><i class="fas fa-phone fa-xs text-secondary"></i> <?= htmlspecialchars($bk['contact_phone']) ?></small>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">
                                        <?= $bk['pax_count'] ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge <?= $badgeClass ?> p-2 shadow-sm"><?= $bk['state'] ?></span>
                                    </td>
                                    <!-- [QUAN TRỌNG] Thêm data-order để DataTables sắp xếp theo timestamp gốc -->
                                    <td class="text-center align-middle text-muted" data-order="<?= strtotime($bk['created_at']) ?>">
                                        <?= date('d/m/Y H:i', strtotime($bk['created_at'])) ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="<?= route('booking.show', ['id' => $bk['id']]) ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
        // Kiểm tra xem bảng có dữ liệu thô không trước khi bật DataTable
        if ($('#bookingTable tbody tr').length > 0) {
            $('#bookingTable').DataTable({
                "order": [[ 5, "desc" ]], // Sắp xếp cột ngày đặt (index 5)
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json",
                    "emptyTable": "Không có dữ liệu booking nào"
                },
                "responsive": true,
                "autoWidth": false,
                "retrieve": true,
                "paging": true
            });
        }
    });
</script>

