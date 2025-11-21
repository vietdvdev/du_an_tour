<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/form.css') ?>">

<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Đảm bảo các biến tồn tại và ưu tiên hiển thị dữ liệu $old sau khi validation thất bại
$errors = $errors ?? [];

// Dữ liệu hiển thị trong form sẽ là $old (nếu có lỗi validation) hoặc $supplier
$displayData = $old ?? $supplier ?? []; 

// Lấy ID nhà cung cấp
$id = $displayData['id'] ?? $id ?? 0;
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chỉnh Sửa Nhà Cung Cấp: <?= htmlspecialchars($displayData['name'] ?? 'ID ' . $id) ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

<form class="form-card dm-form" method="POST" action="<?= route('supplier.update', ['id' => $id]) ?>" novalidate>
    
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($errors['general'][0]) ?>
        </div>
    <?php endif; ?>

    <div class="dm-group">
        <label class="dm-label" for="name">Tên Nhà cung cấp <span class="required">*</span></label>
        <input type="text" id="name" name="name" class="dm-input <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
            required maxlength="255" placeholder="Nhập tên nhà cung cấp"
            value="<?= htmlspecialchars($displayData['name'] ?? '') ?>">

        <?php if (!empty($errors['name'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['name'][0]) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="dm-group">
        <label class="dm-label" for="type">Loại Nhà cung cấp <span class="required">*</span></label>
        <select id="type" name="type" class="dm-input <?= !empty($errors['type']) ? 'is-invalid' : '' ?>" required>
            <?php $currentType = $displayData['type'] ?? ''; ?>
            <option value="">-- Chọn loại --</option>
            <option value="HOTEL" <?= ($currentType === 'HOTEL') ? 'selected' : '' ?>>Khách sạn</option>
            <option value="TRANSPORT" <?= ($currentType === 'TRANSPORT') ? 'selected' : '' ?>>Vận chuyển</option>
            <option value="RESTAURANT" <?= ($currentType === 'RESTAURANT') ? 'selected' : '' ?>>Nhà hàng</option>
            <option value="OTHER" <?= ($currentType === 'OTHER') ? 'selected' : '' ?>>Khác</option>
        </select>
        <?php if (!empty($errors['type'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['type'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-label" for="contact">Người liên hệ</label>
        <input type="text" id="contact" name="contact" class="dm-input <?= !empty($errors['contact']) ? 'is-invalid' : '' ?>" 
            maxlength="255" placeholder="Nhập tên người liên hệ"
            value="<?= htmlspecialchars($displayData['contact'] ?? '') ?>">

        <?php if (!empty($errors['contact'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['contact'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-label" for="phone">Điện thoại <span class="required">*</span></label>
        <input type="text" id="phone" name="phone" class="dm-input <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>" 
            required maxlength="21" placeholder="Nhập số điện thoại"
            value="<?= htmlspecialchars($displayData['phone'] ?? '') ?>">
            
        <?php if (!empty($errors['phone'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['phone'][0]) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="dm-group">
        <label class="dm-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="dm-input <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
            maxlength="255" placeholder="Nhập email"
            value="<?= htmlspecialchars($displayData['email'] ?? '') ?>">
            
        <?php if (!empty($errors['email'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['email'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-label" for="address">Địa chỉ</label>
        <input type="text" id="address" name="address" class="dm-input <?= !empty($errors['address']) ? 'is-invalid' : '' ?>" 
            maxlength="255" placeholder="Nhập địa chỉ"
            value="<?= htmlspecialchars($displayData['address'] ?? '') ?>">

        <?php if (!empty($errors['address'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['address'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-label" for="note">Ghi chú</label>
        <textarea id="note" name="note" class="dm-textarea <?= !empty($errors['note']) ? 'is-invalid' : '' ?>" rows="4"
            placeholder="Ghi chú thêm về nhà cung cấp"><?= htmlspecialchars($displayData['note'] ?? '') ?></textarea>
            
        <?php if (!empty($errors['note'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['note'][0]) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="dm-actions">
        <button type="submit" class="dm-btn dm-btn-primary">Lưu Chỉnh Sửa</button>
        <a href="<?= route('supplier.index') ?>" class="dm-btn dm-btn-secondary">Quay lại</a>
    </div>

</form>


                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>