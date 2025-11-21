<?php
// File này chứa form để chỉnh sửa thông tin cơ bản của Tour.
// Biến $tour (dữ liệu tour hiện tại) và $categories (danh sách danh mục) được giả định đã có sẵn.

$tourData = $tour ?? [];
$categoriesList = $categories ?? [];
?>

<form action="<?= route('tour.update', ['id' => $tourData['id']]) ?>" method="POST">
    <!-- Trường ẩn để gửi ID của Tour -->
    <input type="hidden" name="tour_id" value="<?= htmlspecialchars($tourData['id'] ?? '') ?>">
    
    <div class="row g-3">
        <!-- 1. MÃ TOUR (READ-ONLY) -->
        <div class="col-md-6">
            <label for="tour_code" class="form-label font-weight-bold">Mã Tour</label>
            <!-- Thêm thuộc tính readonly để ngăn chỉnh sửa -->
            <input 
                type="text" 
                class="form-control" 
                id="tour_code" 
                name="code" 
                value="<?= htmlspecialchars($tourData['code'] ?? 'T0000') ?>" 
                readonly 
                style="background-color: #f0f0f0;"
            >
            <div class="form-text">Mã tour không thể thay đổi sau khi được tạo.</div>
        </div>
        
        <!-- 2. TRẠNG THÁI TOUR (CÓ THỂ CHỈNH SỬA) -->
        <div class="col-md-6">
            <label for="tour_state" class="form-label font-weight-bold">Trạng Thái</label>
            <select class="form-select" id="tour_state" name="state" required>
                <!-- Giả định các trạng thái có thể là 'Draft', 'Published', 'Archived' -->
                <?php $currentState = $tourData['state'] ?? 'Draft'; ?>
                <option value="Draft" <?= $currentState === 'Draft' ? 'selected' : '' ?>>Nháp</option>
                <option value="Published" <?= $currentState === 'Published' ? 'selected' : '' ?>>Đã Xuất Bản</option>
                <option value="Archived" <?= $currentState === 'Archived' ? 'selected' : '' ?>>Lưu Trữ</option>
            </select>
        </div>

        <!-- 3. TÊN TOUR (CÓ THỂ CHỈNH SỬA) -->
        <div class="col-12">
            <label for="tour_name" class="form-label font-weight-bold">Tên Tour</label>
            <input 
                type="text" 
                class="form-control" 
                id="tour_name" 
                name="name" 
                value="<?= htmlspecialchars($tourData['name'] ?? '') ?>" 
                placeholder="Nhập tên tour du lịch..."
                required
            >
        </div>

        <!-- 4. DANH MỤC TOUR (CÓ THỂ CHỈNH SỬA) -->
        <div class="col-12">
            <label for="tour_category" class="form-label font-weight-bold">Danh Mục Tour</label>
            <select class="form-select" id="tour_category" name="category_id" required>
                <option value="">-- Chọn Danh Mục --</option>
                <?php $currentCategoryId = (int)($tourData['category_id'] ?? 0); ?>
                <?php foreach ($categoriesList as $category): ?>
                    <option 
                        value="<?= htmlspecialchars($category['id']) ?>" 
                        <?= $currentCategoryId === (int)$category['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 5. MÔ TẢ TOUR (CÓ THỂ CHỈNH SỬA) -->
        <div class="col-12">
            <label for="tour_description" class="form-label font-weight-bold">Mô tả Tour</label>
            <textarea 
                class="form-control" 
                id="tour_description" 
                name="description" 
                rows="5" 
                placeholder="Mô tả chi tiết về tour du lịch..."
            ><?= htmlspecialchars($tourData['description'] ?? '') ?></textarea>
        </div>
        
        <!-- NÚT LƯU -->
        <div class="col-12 mt-4 text-center">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save"></i> Lưu Thay Đổi
            </button>
        </div>
    </div>
</form>

<style>
/* Tùy chỉnh nhỏ để mô phỏng styling của form */
.form-label {
    margin-bottom: 0.5rem;
    display: block;
}
.form-control, .form-select {
    padding: 0.75rem 1rem;
    border: 1px solid #ced4da;
    border-radius: 0.5rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    border-radius: 0.5rem;
}
.font-weight-bold {
    font-weight: 600;
}
</style>