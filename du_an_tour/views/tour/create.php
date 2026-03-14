<!-- Giả sử file này nằm trong thư mục views/tour/ -->
<!-- Phần header -->
<?php include __DIR__ . '/../layout/header.php'; ?>
<!-- Giả sử css/form.css có định nghĩa style cho form/invalid-feedback -->
<link rel="stylesheet" href="<?= asset('css/form.css') ?>"> 

<!-- Phần Navbar -->
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<!-- Phần Sidebar -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Đảm bảo các biến được truyền từ Controller tồn tại
$errors = $errors ?? [];
$old = $old ?? [];
$categories = $categories ?? []; // Danh sách danh mục được tải từ TourController::create()
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tạo Tour Mới (Bước Cốt Lõi)</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin cơ bản về Tour</h3>
                        </div>
                        
                        <!-- Form gửi đến TourController::store() -->
                        <form method="POST" action="<?= route('tour.store') ?>" novalidate>
                            <div class="card-body">

                                <!-- HIỂN THỊ LỖI CHUNG (ví dụ lỗi DB) -->
                                <?php if (!empty($errors['general'])): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($errors['general'][0]) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- CODE (Mã Tour - UNIQUE, NOT NULL) -->
                                <div class="form-group">
                                    <label for="code">Mã Tour <span class="text-danger">*</span></label>
                                    <input type="text" id="code" name="code" 
                                        class="form-control <?= !empty($errors['code']) ? 'is-invalid' : '' ?>" 
                                        required maxlength="50" placeholder="Ví dụ: VN012025"
                                        value="<?= htmlspecialchars($old['code'] ?? '') ?>">
                                    <?php if (!empty($errors['code'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['code'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Mã tour phải là duy nhất và là khóa tra cứu chính.</small>
                                </div>

                                <!-- NAME (Tên Tour - NOT NULL) -->
                                <div class="form-group">
                                    <label for="name">Tên Tour <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" 
                                        class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
                                        required maxlength="255" placeholder="Nhập tên tour hiển thị"
                                        value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                    <?php if (!empty($errors['name'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['name'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- CATEGORY_ID (Danh Mục - FK, NOT NULL) -->
                                <div class="form-group">
                                    <label for="category_id">Danh Mục Tour <span class="text-danger">*</span></label>
                                    <select id="category_id" name="category_id" 
                                        class="form-control <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                                        <option value="">-- Chọn Danh mục --</option>
                                        <?php $currentCatId = $old['category_id'] ?? 0; ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>" 
                                                <?= ((int)$cat['id'] === (int)$currentCatId) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($errors['category_id'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['category_id'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- DESCRIPTION (Mô tả) -->
                                <div class="form-group">
                                    <label for="description">Mô tả Tour</label>
                                    <textarea id="description" name="description" 
                                        class="form-control <?= !empty($errors['description']) ? 'is-invalid' : '' ?>" 
                                        rows="4" placeholder="Mô tả sơ lược về tour (không bắt buộc)"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                                    <?php if (!empty($errors['description'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['description'][0]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                            </div>
                            <!-- /.card-body -->

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Lưu & Tiếp tục cấu hình
                                </button>
                                <a href="<?= route('tour.index') ?>" class="btn btn-default float-right">
                                    Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include __DIR__ . '/../layout/footer.php'; ?>