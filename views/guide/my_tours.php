<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-alt text-primary"></i> Lịch dẫn tour của tôi</h1>
                </div>
            </div>
        </div>
    </section>


    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                   
                    <!-- Thông báo lỗi nếu có -->
                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                        </div>
                    <?php endif; ?>


                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Danh sách công việc</h3>
                        </div>
                       
                        <div class="card-body">
                            <table id="tableMyTours" class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center align-middle" style="width: 50px;">STT</th>
                                        <th class="align-middle">Mã Tour / Tên Tour</th>
                                        <th class="align-middle">Thời gian</th>
                                        <th class="text-center align-middle">Vai trò</th>
                                        <th class="align-middle">Điểm đón</th>
                                        <th class="text-center align-middle">Trạng thái</th>
                                        <th class="text-center align-middle" style="width: 180px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($assignments)): ?>
                                        <?php foreach ($assignments as $key => $item):
                                            // Xử lý trạng thái tour
                                            $today = date('Y-m-d');
                                            $start = $item['start_date'];
                                            $end   = $item['dep_end_date'];
                                           
                                            $statusBadge = '';
                                            $statusText = '';


                                            if ($today < $start) {
                                                $statusBadge = 'badge-info';
                                                $statusText = 'Sắp khởi hành';
                                            } elseif ($today >= $start && $today <= $end) {
                                                $statusBadge = 'badge-success';
                                                $statusText = 'Đang diễn ra';
                                            } else {
                                                $statusBadge = 'badge-secondary';
                                                $statusText = 'Đã hoàn thành';
                                            }
                                        ?>
                                            <tr>
                                                <td class="text-center align-middle"><?= $key + 1 ?></td>
                                               
                                                <td class="align-middle">
                                                    <b class="text-primary"><?= htmlspecialchars($item['tour_code']) ?></b><br>
                                                    <small class="text-muted"><?= htmlspecialchars($item['tour_name']) ?></small>
                                                </td>
                                               
                                                <td class="align-middle">
                                                    <small class="d-block text-muted">Từ <i class="fas fa-plane-departure text-xs mr-1"></i> <?= date('d/m/Y', strtotime($start)) ?></small>
                                                    <small class="d-block text-muted">Đến <i class="fas fa-plane-arrival text-xs mr-1"></i> <?= date('d/m/Y', strtotime($end)) ?></small>
                                                </td>
                                               
                                                <td class="align-middle text-center">
                                                    <?php if ($item['role'] == 'MAIN'): ?>
                                                        <span class="badge badge-warning">Trưởng đoàn</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light border">HDV Phụ</span>
                                                    <?php endif; ?>
                                                </td>
                                               
                                                <td class="align-middle"><?= htmlspecialchars($item['pickup_point'] ?? '---') ?></td>
                                               
                                                <td class="align-middle text-center">
                                                    <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                                                </td>
                                               
                                                <!-- PHẦN THAO TÁC: Đã sửa lại đồng bộ -->
                                                <td class="align-middle text-center">
                                                    <a href="<?= route('guide.log.index') ?>?departure_id=<?= $item['departure_id'] ?>"
                                                       class="btn btn-sm btn-warning" title="Viết nhật ký">
                                                        <i class="fas fa-book-open"></i>
                                                    </a>
                                                   
                                                    <a href="<?= route('guide.attendance') ?>?departure_id=<?= $item['departure_id'] ?>"
                                                       class="btn btn-sm btn-success" title="Điểm danh khách">
                                                        <i class="fas fa-user-check"></i> Điểm danh
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-search fa-2x mb-2"></i><br>
                                                Bạn chưa được phân công tour nào.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<?php include __DIR__ . '/../layout/footer.php'; ?>


<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#tableMyTours')) {
            $('#tableMyTours').DataTable().destroy();
        }
        $('#tableMyTours').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "order": [[ 2, "desc" ]], // Sắp xếp theo ngày gần nhất
            "language": {
                "search": "Tìm kiếm:",
                "paginate": { "next": "Sau", "previous": "Trước" },
                "info": "Hiển thị _START_ đến _END_ của _TOTAL_ chuyến",
                "emptyTable": "Không có dữ liệu"
            }
        });
    });
</script>




