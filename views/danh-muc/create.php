<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/form.css') ?>">

<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Đảm bảo $errors và $old tồn tại khi render view
$errors = $errors ?? [];
$old = $old ?? [];
?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thêm Danh Mục Tour</h1>
                </div>
            </div>
        </div></section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

<form class="form-card dm-form" method="POST" action="<?= route('danhMuc.store') ?>" novalidate>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($errors['general'][0]) ?>
        </div>
    <?php endif; ?>

    <div class="dm-group">
        <label class="dm-label" for="name">Tên danh mục <span class="required">*</span></label>
        <input type="text" id="name" name="name" class="dm-input <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
            required maxlength="255" placeholder="Nhập tên danh mục"
            value="<?= htmlspecialchars($old['name'] ?? '') ?>">

        <?php if (!empty($errors['name'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['name'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-label" for="description">Mô tả</label>
        <textarea id="description" name="description" class="dm-textarea <?= !empty($errors['description']) ? 'is-invalid' : '' ?>" rows="4"
            placeholder="Nhập nội dung mô tả (không bắt buộc)"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>

        <?php if (!empty($errors['description'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['description'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-checkbox-wrap">
            <input type="checkbox" name="is_active" <?= (($old['is_active'] ?? 1) == 1) ? 'checked' : '' ?>>
            <span>Hiển thị danh mục</span>
        </label>
    </div>

    <div class="dm-actions">
        <button type="submit" class="dm-btn dm-btn-primary">Lưu</button>
        <a href="<?= route('danhMuc.index') ?>" class="dm-btn dm-btn-secondary">Quay lại</a>
    </div>

</form>


                </div>
            </div>
        </div>
    </section>
</div>



<?php include __DIR__ . '/../layout/footer.php'; ?>
<script>
    $(document).ready(function() {
        // Logic DataTable của bạn (cần đảm bảo ID #example1 tồn tại trên trang index)
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