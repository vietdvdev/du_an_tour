<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/form.css') ?>">

<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thêm Nhà Cung Cấp</h1>
                </div>
            </div>
        </div></section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

<form class="form-card dm-form" method="POST" action="<?= route('supplier.store') ?>" novalidate>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($errors['general'][0]) ?>
        </div>
    <?php endif; ?>

    <div class="dm-group">
        <label class="dm-label" for="name">Tên Nhà cung cấp <span class="required">*</span></label>
        <input type="text" id="name" name="name" class="dm-input <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
            required maxlength="255" placeholder="Nhập tên nhà cung cấp"
            value="<?= htmlspecialchars($old['name'] ?? '') ?>">

        <?php if (!empty($errors['name'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['name'][0]) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="dm-group">
        <label class="dm-label" for="type">Loại Nhà cung cấp <span class="required">*</span></label>
        <select id="type" name="type" class="dm-input <?= !empty($errors['type']) ? 'is-invalid' : '' ?>" required>
            <option value="">-- Chọn loại --</option>
            <option value="HOTEL" <?= (($old['type'] ?? '') === 'HOTEL') ? 'selected' : '' ?>>Khách sạn</option>
            <option value="TRANSPORT" <?= (($old['type'] ?? '') === 'TRANSPORT') ? 'selected' : '' ?>>Vận chuyển</option>
            <option value="RESTAURANT" <?= (($old['type'] ?? '') === 'RESTAURANT') ? 'selected' : '' ?>>Nhà hàng</option>
            <option value="OTHER" <?= (($old['type'] ?? '') === 'OTHER') ? 'selected' : '' ?>>Khác</option>
        </select>
        <?php if (!empty($errors['type'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['type'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-label" for="contact">Người Phụ trách</label>
        <input type="text" id="contact" name="contact" class="dm-input <?= !empty($errors['contact']) ? 'is-invalid' : '' ?>" 
            maxlength="255" placeholder="Nhập tên người liên hệ"
            value="<?= htmlspecialchars($old['contact'] ?? '') ?>">

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
            value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            
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
            value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            
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
            value="<?= htmlspecialchars($old['address'] ?? '') ?>">

        <?php if (!empty($errors['address'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['address'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-group">
        <label class="dm-label" for="note">Ghi chú</label>
        <textarea id="note" name="note" class="dm-textarea <?= !empty($errors['note']) ? 'is-invalid' : '' ?>" rows="4"
            placeholder="Ghi chú thêm về nhà cung cấp"><?= htmlspecialchars($old['note'] ?? '') ?></textarea>
            
        <?php if (!empty($errors['note'])): ?>
            <div class="invalid-feedback">
                <?= htmlspecialchars($errors['note'][0]) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dm-actions">
        <button type="submit" class="dm-btn dm-btn-primary">Lưu</button>
        <a href="<?= route('supplier.index') ?>" class="dm-btn dm-btn-secondary">Quay lại</a>
    </div>

</form>


                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>