<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/form.css') ?>">

<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>




<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit danh mục tour</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
<?php
$id = isset($old['id']) ? $old['id'] : ($item['id'] ?? 0);
?>

<form class="form-card dm-form" method="POST" action="<?= route('danhMuc.update', ['id' => $id]) ?>" novalidate>
    <input type="hidden" name="_method" value="POST">

    <!-- NAME -->
    <div class="dm-group">
        <label class="dm-label" for="name">Tên danh mục</label>

        <input
            type="text"
            id="name"
            name="name"
            class="dm-input <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
            placeholder="Nhập tên danh mục"
            maxlength="255"
            required
            value="<?=
                isset($old['name']) ? htmlspecialchars($old['name']) :
                (isset($item['name']) ? htmlspecialchars($item['name']) : '')
            ?>"
        >

        <?php if (!empty($errors['name'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['name'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- DESCRIPTION -->
    <div class="dm-group">
        <label class="dm-label" for="description">Mô tả</label>

        <textarea
            id="description"
            name="description"
            class="dm-textarea <?= !empty($errors['description']) ? 'is-invalid' : '' ?>"
            rows="4"
            placeholder="Nhập mô tả (không bắt buộc)"
        ><?=
            isset($old['description']) ? htmlspecialchars($old['description']) :
            (isset($item['description']) ? htmlspecialchars($item['description']) : '')
        ?></textarea>

        <?php if (!empty($errors['description'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['description'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- BUTTONS -->
    <div class="dm-actions">
        <button type="submit" class="dm-btn dm-btn-primary">Lưu</button>
        <a href="<?= route('danhMuc.index') ?>" class="dm-btn dm-btn-secondary">Quay lại</a>
    </div>

</form>


                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
</div>
<!-- /.row -->
</div>
<!-- /.container-fluid -->
</section>
<!-- /.content -->
</div>



<!-- Viết các hiển thị danh sách danh mục --><?php include __DIR__ . '/../layout/footer.php'; ?>
<!-- endforeach -->
<!-- Page specific script -->
<script>
    $(document).ready(function() {
        // Nếu DataTable đã tồn tại => destroy để tránh reinit lỗi
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }

        let table = $('#example1').DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
        });

        table.buttons().container()
            .appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>