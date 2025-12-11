<?php include __DIR__ . '/../layout/header.php'; ?>
<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<!-- Phần Sidebar -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$ListTour = $ListTour ?? [];
$currentType = $currentType ?? 'all'; // Biến từ controller


function getTourStateLabel(string $state): string
{
    return match ($state) {
        'DRAFT' => '<span class="badge badge-warning">Bản Nháp</span>',
        'PUBLISHED' => '<span class="badge badge-success">Đã Công Bố</span>',
        'HIDDEN' => '<span class="badge badge-secondary">Đang Ẩn</span>',
        default => '<span class="badge badge-light">N/A</span>',
    };
}
?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Tour</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('tour.create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Tạo Tour Mới
                    </a>
                </div>
            </div>
        </div>
    </section>


    <section class="content">
        <div class="container-fluid">


            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>


            <div class="card">
                <div class="card-header p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- BỘ LỌC MENU (TABS) -->
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link <?= $currentType == 'all' ? 'active' : '' ?>" href="<?= route('tour.index') ?>?type=all">
                                    Tất cả
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentType == 'system' ? 'active' : '' ?>" href="<?= route('tour.index') ?>?type=system">
                                    Tour Hệ thống
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentType == 'custom' ? 'active' : '' ?>" href="<?= route('tour.index') ?>?type=custom">
                                    Tour Theo yêu cầu
                                </a>
                            </li>
                        </ul>
                       
                        <span class="badge badge-info">Tổng: <?= count($ListTour) ?> tour</span>
                    </div>
                </div>
               
                <div class="card-body">
                    <table id="tourTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width:50px;">ID</th>
                                <th style="width:100px;">Mã Tour</th>
                                <th>Tên Tour</th>
                                <!-- CỘT MỚI: LOẠI TOUR -->
                                <th style="width:120px;" class="text-center">Loại</th>
                                <th style="width:150px;">Danh mục</th>
                                <th style="width:100px;" class="text-center">Trạng thái</th>
                                <th style="width:100px;" class="text-center">Hiển thị</th>
                                <th style="width:120px;" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ListTour as $tour):
                                $id = (int)($tour['id'] ?? 0);
                                $isActive = (int)($tour['is_active'] ?? 1);
                                $isCustom = (int)($tour['is_custom'] ?? 0);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($id) ?></td>
                                    <td>
                                        <span class="font-weight-bold text-primary"><?= htmlspecialchars($tour['code'] ?? '') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($tour['name'] ?? '') ?></td>
                                   
                                    <!-- HIỂN THỊ LOẠI TOUR -->
                                    <td class="text-center">
                                        <?php if ($isCustom == 1): ?>
                                            <span class="badge badge-warning"><i class="fas fa-user-tag"></i> Yêu cầu</span>
                                        <?php else: ?>
                                            <span class="badge badge-info"><i class="fas fa-globe"></i> Hệ thống</span>
                                        <?php endif; ?>
                                    </td>


                                    <td><?= htmlspecialchars($tour['category_name'] ?? '---') ?></td>
                                    <td class="text-center"><?= getTourStateLabel($tour['state'] ?? 'DRAFT') ?></td>


                                    <!-- Nút Bật/Tắt -->
                                    <td class="text-center align-middle">
                                        <form action="<?= route('tour.toggle.status', ['id' => $id]) ?>" method="POST">
                                            <input type="hidden" name="is_active" value="<?= $isActive ? 0 : 1 ?>">
                                            <button type="submit"
                                                class="btn btn-xs font-weight-bold <?= $isActive ? 'btn-outline-success' : 'btn-outline-secondary' ?>"
                                                title="<?= $isActive ? 'Đang bật. Nhấn để Tắt' : 'Đang tắt. Nhấn để Bật' ?>"
                                                style="width: 60px;">
                                                <?= $isActive ? 'ON' : 'OFF' ?>
                                            </button>
                                        </form>
                                    </td>


                                    <!-- Hành động -->
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= route('tour.show', ['id' => $id]) ?>" class="btn btn-info" title="Xem"><i class="fas fa-eye"></i></a>
                                            <a href="<?= route('tour.edit', ['id' => $id]) ?>" class="btn btn-primary" title="Sửa"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $id ?>, '<?= htmlspecialchars($tour['name']) ?>')" title="Xóa"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <form id="delete-form-<?= $id ?>" method="POST" action="<?= route('tour.delete', ['id' => $id]) ?>" style="display:none;"></form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>


                            <?php if (empty($ListTour)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">Không tìm thấy Tour nào theo bộ lọc này.</td>
                                </tr>
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
    $(function() {
        if ($.fn.DataTable.isDataTable('#tourTable')) {
            $('#tourTable').DataTable().destroy();
        }


        $('#tourTable').DataTable({
            "responsive": true,
            "lengthChange": true,
            "pageLength": 25,
            "autoWidth": false,
            "ordering": false, // Tắt sắp xếp JS để dùng sắp xếp PHP (mới nhất lên đầu)
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
            },
            "buttons": ["copy", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#tourTable_wrapper .col-md-6:eq(0)');
    });


    function confirmDelete(id, name) {
        if (confirm(`Bạn có chắc muốn XÓA Tour "${name}"? Hành động này không thể hoàn tác.`)) {
            document.getElementById("delete-form-" + id).submit();
        }
    }
</script>

