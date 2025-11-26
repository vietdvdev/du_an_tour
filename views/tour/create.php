<?php include __DIR__ . '/../layout/header.php'; ?>
<link rel="stylesheet" href="<?= asset('css/form.css') ?>">

<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php
// Đảm bảo các biến tồn tại
$errors = $errors ?? [];
$old = $old ?? [];
$categories = $categories ?? [];
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-suitcase-rolling mr-2"></i> Thêm Tour Mới</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="<?= route('tour.index') ?>">Quản lý Tour</a></li>
                        <li class="breadcrumb-item active">Tạo mới</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title text-bold">Thông tin chung</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <form method="POST" action="<?= route('tour.store') ?>" novalidate>
                            <div class="card-body">

                                <?php if (!empty($errors['general'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="icon fas fa-ban"></i> <?= htmlspecialchars($errors['general'][0]) ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="code">Mã Tour <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                                </div>
                                                <input type="text" id="code" name="code" 
                                                    class="form-control <?= !empty($errors['code']) ? 'is-invalid' : '' ?>" 
                                                    required maxlength="50" placeholder="VD: VN012025"
                                                    value="<?= htmlspecialchars($old['code'] ?? '') ?>">
                                                
                                                <?php if (!empty($errors['code'])): ?>
                                                    <div class="invalid-feedback">
                                                        <?= htmlspecialchars($errors['code'][0]) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <small class="form-text text-muted">Mã duy nhất để định danh tour.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="category_id">Danh Mục <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                                </div>
                                                <select id="category_id" name="category_id" 
                                                    class="form-control custom-select <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>" required>
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
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="name">Tên Tour <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                 <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-map-marked-alt"></i></span>
                                                </div>
                                                <input type="text" id="name" name="name" 
                                                    class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
                                                    required maxlength="255" placeholder="Nhập tên tour hiển thị khách hàng"
                                                    value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                                
                                                <?php if (!empty($errors['name'])): ?>
                                                    <div class="invalid-feedback">
                                                        <?= htmlspecialchars($errors['name'][0]) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Mô tả ngắn</label>
                                            <textarea id="description" name="description" 
                                                class="form-control <?= !empty($errors['description']) ? 'is-invalid' : '' ?>" 
                                                rows="5" placeholder="Mô tả sơ lược về điểm đến, trải nghiệm..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                                            
                                            <?php if (!empty($errors['description'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($errors['description'][0]) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div> </div>
                            <div class="card-footer bg-white d-flex justify-content-between">
                                <a href="<?= route('tour.index') ?>" class="btn btn-default">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save mr-1"></i> Lưu & Tiếp tục
                                </button>
                            </div>
                        </form>
                    </div>
                    </div>
            </div>
        </div>
    </section>
    </div>

<?php include __DIR__ . '/../layout/footer.php'; ?>