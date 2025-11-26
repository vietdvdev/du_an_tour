<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Đợt Khởi Hành</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('departure.create') ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-plus mr-1"></i> Tạo Đợt Mới
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
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Lịch trình & Tình trạng chỗ</h3>
                </div>
                <div class="card-body">
                    <table id="departureTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th>Tour</th>
                                <th class="text-center">Ngày đi / Về</th>
                                <th class="text-center">Điểm đón</th>
                                <th class="text-center" style="width: 120px">Tình trạng chỗ</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center" style="width: 100px">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departures as $item): 
                                $booked = (int)$item['booked_seats'];
                                $capacity = (int)$item['capacity'];
                                $available = $capacity - $booked;
                                
                                // Logic màu sắc cho thanh tiến trình
                                $percent = ($capacity > 0) ? round(($booked / $capacity) * 100) : 0;
                                $progressClass = 'bg-success';
                                if ($percent >= 80) $progressClass = 'bg-warning';
                                if ($percent >= 100) $progressClass = 'bg-danger';
                            ?>
                            <tr>
                                <td class="text-center align-middle"><?= $item['id'] ?></td>
                                <td class="align-middle">
                                    <span class="badge badge-secondary"><?= htmlspecialchars($item['tour_code']) ?></span><br>
                                    <strong><?= htmlspecialchars($item['tour_name']) ?></strong>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="text-primary font-weight-bold"><?= date('d/m/Y', strtotime($item['start_date'])) ?></div>
                                    <small class="text-muted"><i class="fas fa-arrow-down fa-xs"></i></small><br>
                                    <div class="text-secondary"><?= date('d/m/Y', strtotime($item['end_date'])) ?></div>
                                </td>
                                <td class="text-center align-middle"><?= htmlspecialchars($item['pickup_point']) ?></td>
                                
                                <td class="align-middle">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Đã bán: <b><?= $booked ?></b></small>
                                        <small>Tổng: <b><?= $capacity ?></b></small>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                             style="width: <?= $percent ?>%"></div>
                                    </div>
                                    <small class="d-block text-center mt-1">
                                        <?php if($available <= 0): ?>
                                            <span class="text-danger font-weight-bold">HẾT CHỖ</span>
                                        <?php else: ?>
                                            Còn <span class="text-success font-weight-bold"><?= $available ?></span> chỗ
                                        <?php endif; ?>
                                    </small>
                                </td>

                                <td class="text-center align-middle">
                                    <?php if($item['status'] == 'OPEN'): ?>
                                        <span class="badge badge-success">Đang mở</span>
                                    <?php elseif($item['status'] == 'CLOSED'): ?>
                                        <span class="badge badge-secondary">Đã đóng</span>
                                    <?php else: ?>
                                        <span class="badge badge-dark"><?= $item['status'] ?></span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center align-middle">
                                    <a href="<?= route('departure.edit', ['id' => $item['id']]) ?>" class="btn btn-sm btn-info" title="Sửa">
                                        <i class="fas fa-edit"></i>
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
        $('#departureTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": false, // Tắt sort mặc định vì ta đã order trong SQL
            "info": true,
            "autoWidth": false,
            "responsive": true,
             language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json" }
        });
    });
</script>