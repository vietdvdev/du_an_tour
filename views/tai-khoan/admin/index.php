<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php include __DIR__ . '/../../layout/navbar.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Danh sách Hướng dẫn viên</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    
                    <!-- Hiển thị thông báo Flash Session từ Server -->
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <a href="<?= route('admin.create') ?>">
                                <button class="btn btn-success"> + Thêm Hướng dẫn viên</button>
                            </a>
                        </div>
                        
                        <div class="card-body">
                            <table id="tableGuide" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>SĐT</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ListUsers)): ?>
                                        <?php foreach ($ListUsers as $key => $item): ?>
                                            <?php 
                                                // Lấy trạng thái an toàn
                                                $isActive = isset($item['is_active']) ? (int)$item['is_active'] : 1;
                                                $uId = $item['id'] ?? 0;
                                            ?>
                                            <tr>
                                                <td><?= $key + 1 ?></td>
                                                <td>
                                                    <b><?= htmlspecialchars($item['full_name'] ?? '') ?></b><br>
                                                    <small class="text-muted"><?= htmlspecialchars($item['username'] ?? '') ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($item['email'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($item['phone'] ?? '') ?></td>
                                                
                                                <!-- CỘT TRẠNG THÁI (Xử lý PHP thuần + Confirm) -->
                                                <td class="text-center">
                                                    <!-- Sử dụng thẻ <a> để gửi request GET lên server -->
                                                    <!-- Đã thêm onclick confirm -->
                                                    <a href="<?= route('admin.toggle_status', ['id' => $uId]) ?>" 
                                                       class="btn btn-sm <?= $isActive == 1 ? 'btn-outline-success' : 'btn-outline-secondary' ?>"
                                                       style="min-width: 100px;"
                                                       onclick="return confirm('Bạn có chắc chắn muốn <?= $isActive == 1 ? 'KHÓA' : 'MỞ KHÓA' ?> tài khoản <?= htmlspecialchars($item['username'] ?? '') ?> không?');">
                                                        
                                                        <i class="fas fa-toggle-<?= $isActive == 1 ? 'on' : 'off' ?>"></i> 
                                                        <span class="font-weight-bold ml-1">
                                                            <?= $isActive == 1 ? 'Mở' : 'Khóa' ?>
                                                        </span>
                                                    </a>
                                                </td>

                                                <td>
                                                                                                        <a href="<?= route('admin.detail', ['id' => $uId]) ?>" class="btn btn-info btn-sm" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= route('admin.edit', ['id' => $uId]) ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Sửa
                                                    </a>
                                                    <a href="<?= route('admin.delete', ['id' => $uId]) ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Xóa HDV <?= htmlspecialchars($item['full_name'] ?? '') ?>?')"
                                                       title="Xóa">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Chưa có dữ liệu</td>
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

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<!-- Script DataTable -->
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#tableGuide')) {
            $('#tableGuide').DataTable().destroy();
        }
        $('#tableGuide').DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#tableGuide_wrapper .col-md-6:eq(0)');
    });
</script>