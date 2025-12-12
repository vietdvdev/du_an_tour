<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>
<!-- Navbar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<!-- Sidebar -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-tags text-warning"></i> Quản lý Danh mục Tour</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('danhMuc.create') ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i> Thêm danh mục mới
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle mr-1"></i> 
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Danh sách các loại hình du lịch</h3>
                </div>
                
                <div class="card-body">
                    <!-- QUAN TRỌNG: Thêm id="example1" để DataTable hoạt động -->
                    <table id="example1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr class="bg-light">
                                <th style="width: 50px" class="text-center">STT</th>
                                <th>Tên danh mục</th>
                                <th>Mô tả</th>
                                <th style="width: 130px" class="text-center">Trạng thái</th>
                                <th style="width: 120px" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Danh_Muc_Tour as $key => $c): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $key + 1 ?></td>
                                    
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-primary" style="font-size: 1.05em;">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="align-middle text-muted">
                                        <?= htmlspecialchars(substr($c['description'], 0, 80)) ?>
                                        <?= strlen($c['description']) > 80 ? '...' : '' ?>
                                    </td>
                                    
                                    <!-- Trạng thái (Click để đổi) -->
                                    <td class="text-center align-middle">
                                        <a href="javascript:void(0)" 
                                           onclick="toggleActive(<?= $c['id'] ?>, <?= $c['is_active'] ?>)"
                                           class="badge <?= $c['is_active'] ? 'badge-success' : 'badge-secondary' ?> p-2"
                                           style="font-size: 0.85rem; cursor: pointer; min-width: 80px;">
                                            <?php if ($c['is_active']): ?>
                                                <i class="fas fa-check-circle"></i> Hiển thị
                                            <?php else: ?>
                                                <i class="fas fa-eye-slash"></i> Đang ẩn
                                            <?php endif; ?>
                                        </a>
                                        
                                        <!-- Form ẩn để submit logic toggle -->
                                        <form id="toggle-form-<?= $c['id'] ?>" method="POST" 
                                              action="<?= route('danhMuc.update.active', ['id' => $c['id']]) ?>" 
                                              style="display:none;">
                                        </form>
                                    </td>

                                    <!-- Hành động -->
                                    <td class="text-center align-middle">
                                        <div class="btn-group">
                                            <a href="<?= route('danhMuc.edit', ['id' => $c['id']]) ?>" 
                                               class="btn btn-sm btn-info" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="<?= route('danhMuc.delete', ['id' => $c['id']]) ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')" 
                                               title="Xóa">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
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

<!-- Script xử lý DataTable và Toggle -->
<script>
    $(document).ready(function() {
        // Kiểm tra xem DataTable đã được khởi tạo chưa
        if (!$.fn.DataTable.isDataTable('#example1')) {
            $("#example1").DataTable({
                "responsive": true, 
                "lengthChange": true, 
                "autoWidth": false,
                "pageLength": 10,
                // Thêm retrieve: true để tránh lỗi re-init nếu lỡ có code khác gọi lại
                "retrieve": true, 
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
                },
                "buttons": ["copy", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        }
    });

    function toggleActive(id, currentStatus) {
        let message = currentStatus ? 
            "Bạn có chắc muốn ẨN danh mục này không?" : 
            "Bạn có chắc muốn HIỂN THỊ lại danh mục này không?";
        
        if (confirm(message)) {
            document.getElementById("toggle-form-" + id).submit();
        }
    }
</script>