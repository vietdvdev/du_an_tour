<!-- views/tour/index.php -->
<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/test.css') ?>">
<!-- Thêm CSS cần thiết cho icons -->

<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<!-- Phần Sidebar -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Đảm bảo session đã start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Giả định biến chứa dữ liệu là $ListTour
$ListTour = $ListTour ?? [];

// Hàm hỗ trợ hiển thị trạng thái
function getTourStateLabel(string $state): string
{
    return match ($state) {
        'DRAFT' => '<span class="badge bg-warning">Bản Nháp</span>',
        'PUBLISHED' => '<span class="badge bg-success">Đã Công Bố</span>',
        default => '<span class="badge bg-secondary">Không rõ</span>',
    };
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Tour</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <!-- Nút dẫn đến màn hình tạo tour cốt lõi -->
                    <a href="<?= route('tour.create') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tạo Tour Mới
                    </a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_success']);
                    unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']);
                    unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách Tour có sẵn (<?= count($ListTour) ?>)</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="tourTable" class="table table-bordered table-hover">
                        <caption class="sr-only">Bảng Tour</caption>
                        <thead>
                            <tr>
                                <th style="width:50px;">ID</th>
                                <th style="width:100px;">Mã Tour</th>
                                <th>Tên Tour</th>
                                <th style="width:150px;">Danh mục</th>
                                <th style="width:100px;" class="text-center">Trạng thái</th>
                                <th style="width:100px;" class="text-center">Hiển thị</th>
                                <th style="width:120px;" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ListTour as $index => $tour):
                                $id = (int)($tour['id'] ?? 0);
                                $isActive = (int)($tour['is_active'] ?? 1);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($id) ?></td>
                                    <td><?= htmlspecialchars($tour['code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($tour['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($tour['category_name'] ?? 'N/A') ?></td>
                                    <td class="text-center"><?= getTourStateLabel($tour['state'] ?? 'DRAFT') ?></td>

                                    <td class="text-center align-middle">
                                        <form action="<?= route('tour.toggle.status', ['id' => $id]) ?>" method="POST" style="display:inline-block">

                                            <input type="hidden" name="is_active" value="<?= $isActive ? 0 : 1 ?>">

                                            <button type="submit"
                                                class="btn btn-sm font-weight-bold <?= $isActive ? 'btn-outline-success' : 'btn-outline-secondary' ?>"
                                                title="<?= $isActive ? 'Đang bật. Nhấn để Tắt' : 'Đang tắt. Nhấn để Bật' ?>"
                                                style="min-width: 80px;">

                                                <i class="fas <?= $isActive ? 'fa-toggle-on' : 'fa-toggle-off' ?> mr-1"></i>
                                                <?= $isActive ? 'Bật' : 'Tắt' ?>

                                            </button>
                                        </form>
                                    </td>

                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center" style="gap: 5px;">

                                            <a href="<?= htmlspecialchars(route('tour.show', ['id' => $id])) ?>"
                                                class="btn btn-sm btn-info" title="Xem Chi Tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="<?= htmlspecialchars(route('tour.edit', ['id' => $id])) ?>"
                                                class="btn btn-sm btn-primary" title="Cấu hình">
                                                <i class="fas fa-cog"></i>
                                            </a>

                                            <a href="javascript:void(0)"
                                                onclick="confirmDelete(<?= $id ?>, '<?= htmlspecialchars($tour['name'] ?? '') ?>')"
                                                class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </a>

                                            <form id="delete-form-<?= $id ?>" method="POST"
                                                action="<?= route('tour.delete', ['id' => $id]) ?>"
                                                style="display:none;"></form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($ListTour)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Không tìm thấy Tour nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- Page specific script -->
<script>
    $(function() {
        // Khởi tạo DataTables
        if ($.fn.DataTable.isDataTable('#tourTable')) {
            $('#tourTable').DataTable().destroy();
        }

        const table = $('#tourTable').DataTable({
            responsive: true,
            lengthChange: true,
            pageLength: 25,
            autoWidth: false,
            order: [
                [1, 'asc']
            ],
            columnDefs: [{
                    orderable: false,
                    targets: [0, 4, 5, 6]
                }, // Cột ID, Trạng thái, Hành động
                {
                    searchable: false,
                    targets: [0, 4, 5, 6]
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
            },
            buttons: ["copy", "csv", "excel", "pdf", "print"]
        });

        table.buttons().container()
            .appendTo('#tourTable_wrapper .col-md-6:eq(0)');
    });

    /**
     * Xác nhận và gửi form xóa Tour
     */
    function confirmDelete(id, name) {
        if (!confirm(`Bạn có chắc muốn XÓA Tour "${name}"? Thao tác này sẽ xóa toàn bộ chi tiết, đợt khởi hành và booking liên quan (nếu không có ràng buộc).`)) return;
        const form = document.getElementById("delete-form-" + id);
        if (form) form.submit();
    }
</script>