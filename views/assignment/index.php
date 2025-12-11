<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-map-signs text-warning"></i> Điều hành & Phân công HDV</h1>
            <small class="text-muted">Chỉ hiển thị các tour sắp tới đã có khách đặt.</small>
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


            <div class="card card-warning card-outline">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr class="bg-light">
                                <th>Mã Tour / Tên Tour</th>
                                <th class="text-center">Số khách</th>
                                <th class="text-center">Lịch trình</th>
                                <th>HDV Đã Phân Công</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($departures)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Không có tour nào sắp khởi hành có khách đặt.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($departures as $dep): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-primary">
                                            <?= htmlspecialchars($dep['tour_code']) ?>
                                        </div>
                                        <div><?= htmlspecialchars($dep['tour_name']) ?></div>
                                    </td>
                                   
                                    <!-- Cột Số khách (MỚI) -->
                                    <td class="text-center align-middle">
                                        <span class="badge badge-success" style="font-size: 0.9rem;">
                                            <i class="fas fa-users"></i> <?= $dep['total_pax'] ?>
                                        </span>
                                    </td>


                                    <td class="text-center align-middle">
                                        <div class="font-weight-bold text-dark">
                                            <?= date('d/m', strtotime($dep['start_date'])) ?>
                                            <i class="fas fa-arrow-right text-muted fa-xs"></i>
                                            <?= date('d/m/Y', strtotime($dep['end_date'])) ?>
                                        </div>
                                        <?php
                                            $diff = strtotime($dep['start_date']) - time();
                                            $days = round($diff / (60 * 60 * 24));
                                            if ($days >= 0 && $days <= 3) {
                                                echo "<small class='text-danger font-weight-bold'>Còn $days ngày</small>";
                                            }
                                        ?>
                                    </td>
                                    <td class="align-middle">
                                        <!-- Hiển thị danh sách HDV đã gán -->
                                        <?php if (isset($assignedMap[$dep['id']])): ?>
                                            <?php foreach ($assignedMap[$dep['id']] as $asg): ?>
                                                <div class="mb-1 d-inline-block mr-2">
                                                    <span class="badge <?= $asg['role']=='MAIN'?'badge-primary':'badge-secondary' ?> p-2">
                                                        <?= $asg['role'] == 'MAIN' ? 'Trưởng' : 'Phụ' ?>:
                                                        <?= htmlspecialchars($asg['full_name']) ?>
                                                       
                                                        <form action="<?= route('assignment.delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Gỡ HDV này?')">
                                                            <input type="hidden" name="assignment_id" value="<?= $asg['id'] ?>">
                                                            <button class="btn btn-xs text-white ml-1 border-0 bg-transparent p-0"><i class="fas fa-times"></i></button>
                                                        </form>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted font-italic text-sm"><i class="fas fa-exclamation-circle text-warning"></i> Chưa gán</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-sm btn-outline-warning font-weight-bold"
                                                onclick="openAssignModal(<?= $dep['id'] ?>, '<?= htmlspecialchars($dep['tour_name']) ?>')">
                                            <i class="fas fa-user-plus"></i> Phân công
                                        </button>
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


<!-- MODAL PHÂN CÔNG -->
<div class="modal fade" id="modal-assign">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= route('assignment.store') ?>" method="POST">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">Phân công HDV</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="departure_id" id="modal_dep_id">
                    <div class="alert alert-secondary">
                        Đang gán cho tour: <strong id="modal_tour_name"></strong>
                    </div>
                   
                    <div class="form-group">
                        <label>Chọn Hướng dẫn viên <span class="text-danger">*</span></label>
                        <select name="guide_id" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Chọn HDV --</option>
                            <?php foreach ($guides as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['full_name']) ?> (<?= $g['username'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                   
                    <div class="form-group">
                        <label>Vai trò</label>
                        <select name="role" class="form-control">
                            <option value="MAIN">Trưởng đoàn (Main)</option>
                            <option value="ASSIST">Phụ tá (Assist)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning font-weight-bold">Lưu phân công</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include __DIR__ . '/../layout/footer.php'; ?>
<script>
    function openAssignModal(id, name) {
        document.getElementById('modal_dep_id').value = id;
        document.getElementById('modal_tour_name').innerText = name;
        $('#modal-assign').modal('show');
    }
</script>

