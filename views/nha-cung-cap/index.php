<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/test.css') ?>">
<!-- Thêm Bootstrap Icons nếu cần -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> -->

<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<!-- Phần Sidebar -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Đảm bảo session đã start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Giả định biến chứa dữ liệu là $ListSupplier
$ListSupplier = $ListSupplier ?? []; 

// Hàm hỗ trợ hiển thị loại nhà cung cấp bằng Tiếng Việt
function getSupplierTypeLabel(string $type): string
{
    return match ($type) {
        'HOTEL' => 'Khách sạn',
        'TRANSPORT' => 'Vận chuyển',
        'RESTAURANT' => 'Nhà hàng',
        'OTHER' => 'Khác',
        default => $type,
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
                    <h1>Quản lý nhà cung cấp</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= route('supplier.create') ?>" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Thêm nhà cung cấp
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
                    <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách nhà cung cấp</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="supplierTable" class="table table-bordered table-hover">
                        <caption class="sr-only">Bảng nhà cung cấp</caption>
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Tên nhà cung cấp</th>
                                <th>Loại</th>
                                <th>Contact</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th style="width:120px;" class="text-center">Hành động</th> <!-- Tăng width để chứa 2 nút -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ListSupplier as $index => $supplier): 
                                $id = (int)($supplier['id'] ?? 0);
                                $is_active = (int)($supplier['is_active'] ?? 1);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($supplier['id'] ?? $index+1) ?></td>
                                    <td><?= htmlspecialchars($supplier['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(getSupplierTypeLabel($supplier['type'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars(mb_strimwidth($supplier['contact'] ?? '', 0, 30, '...')) ?></td>
                                    <td><?= htmlspecialchars($supplier['phone'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($supplier['email'] ?? '') ?></td>
                                
                                    <!-- CỘT HÀNH ĐỘNG (SỬA VÀ XÓA) - Dùng d-flex để căn chỉnh các nút -->
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center" style="gap: 5px;">
                                            <!-- Nút Sửa -->
                                            <a href="<?= htmlspecialchars(route('supplier.edit', ['id' => $id])) ?>"
                                                class="btn btn-sm btn-primary" title="Sửa">
                                                <i class="bi bi-pencil-square"></i> Sửa
                                            </a>

                                            <!-- Nút Xóa (Kích hoạt Form POST ẩn) -->
                                            <a href="javascript:void(0)"
                                                onclick="confirmDelete(<?= $id ?>, '<?= htmlspecialchars($supplier['name'] ?? '') ?>')"
                                                class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="bi bi-trash"></i> Xóa
                                            </a>
                                            
                                            <!-- FORM POST ẨN ĐỂ GỌI HÀM DELETE -->
                                            <form id="delete-form-<?= $id ?>" method="POST"
                                                  action="<?= route('supplier.delete', ['id' => $id]) ?>"
                                                  style="display:none;"></form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                             <?php if (empty($ListSupplier)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Không tìm thấy nhà cung cấp nào.</td>
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
    $(function () {
        // Khởi tạo DataTables
        if ($.fn.DataTable.isDataTable('#supplierTable')) {
            $('#supplierTable').DataTable().destroy();
        }

        const table = $('#supplierTable').DataTable({
            responsive: true,
            lengthChange: true,
            pageLength: 25,
            autoWidth: false,
            // Sắp xếp theo cột Tên Nhà cung cấp (cột index 1)
            order: [[1, 'asc']], 
            columnDefs: [
                // Cột ID, Trạng thái, Hành động không sắp xếp/tìm kiếm
                { orderable: false, targets: [0, 6, 7] }, 
                { searchable: false, targets: [0, 6, 7] }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
            },
            buttons: ["copy", "csv", "excel", "pdf", "print"]
        });

        table.buttons().container()
            .appendTo('#supplierTable_wrapper .col-md-6:eq(0)');
    });

    /**
     * Hàm xử lý toggle active (giả định route update.active tồn tại)
     */
    function toggleActive(id, currentStatus) {
        const message = currentStatus
            ? "Bạn có chắc muốn ẨN nhà cung cấp này không?"
            : "Bạn có chắc muốn HIỂN THỊ lại nhà cung cấp này không?";

        if (confirm(message)) {
            const form = document.getElementById("toggle-form-" + id);
            if (form) form.submit();
        }
    }

    /**
     * Xác nhận và gửi form xóa Nhà cung cấp
     */
    function confirmDelete(id, name) {
        if (!confirm(`Bạn có chắc muốn XÓA nhà cung cấp "${name}"? (Hành động này không thể hoàn tác)`)) return;
        const form = document.getElementById("delete-form-" + id);
        if (form) form.submit();
    }
</script>