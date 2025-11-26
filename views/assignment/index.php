<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1><i class="fas fa-map-signs text-warning"></i> Điều hành & Phân công HDV</h1>
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

            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Mã Tour / Tên Tour</th>
                                <th class="text-center">Lịch trình</th>
                                <th>HDV Đã Phân Công</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departures as $dep): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($dep['tour_code']) ?></span><br>
                                    <?= htmlspecialchars($dep['tour_name']) ?>
                                </td>
                                <td class="text-center">
                                    <?= date('d/m', strtotime($dep['start_date'])) ?> - 
                                    <?= date('d/m/Y', strtotime($dep['end_date'])) ?>
                                </td>
                                <td>
                                    <!-- Hiển thị danh sách HDV đã gán -->
                                    <?php if (isset($assignedMap[$dep['id']])): ?>
                                        <?php foreach ($assignedMap[$dep['id']] as $asg): ?>
                                            <div class="mb-1">
                                                <span class="badge <?= $asg['role']=='MAIN'?'badge-primary':'badge-secondary' ?>">
                                                    <?= $asg['role'] ?>
                                                </span>
                                                <?= htmlspecialchars($asg['full_name']) ?>
                                                
                                                <form action="<?= route('assignment.delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Gỡ HDV này?')">
                                                    <input type="hidden" name="assignment_id" value="<?= $asg['id'] ?>">
                                                    <button class="btn btn-xs text-danger border-0 bg-transparent"><i class="fas fa-times"></i></button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted font-italic">Chưa có HDV</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-success" 
                                            onclick="openAssignModal(<?= $dep['id'] ?>, '<?= $dep['tour_code'] ?>')">
                                        <i class="fas fa-user-plus"></i> Gán HDV
                                    </button>
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
                    <p>Đang gán cho tour: <strong id="modal_tour_name"></strong></p>
                    
                    <div class="form-group">
                        <label>Chọn Hướng dẫn viên</label>
                        <select name="guide_id" class="form-control" required>
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
                    <button type="submit" class="btn btn-primary">Lưu phân công</button>
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