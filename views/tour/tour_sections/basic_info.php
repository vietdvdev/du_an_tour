<form action="<?= route('tour.update', ['id' => $tour['id']]) ?>" method="POST">
    <div class="row">
        <!-- Tên Tour -->
        <div class="col-md-6">
            <div class="form-group">
                <label>Tên Tour <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($tour['name']) ?>" required>
            </div>
        </div>
        
        <!-- Mã Tour (Read-only) -->
        <div class="col-md-3">
            <div class="form-group">
                <label>Mã Tour <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($tour['code']) ?>" readonly style="background-color: #f4f6f9;">
                <small class="text-muted">Mã tour cố định.</small>
            </div>
        </div>
        
        <!-- Danh mục -->
        <div class="col-md-3">
            <div class="form-group">
                <label>Danh mục</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $tour['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Mô tả -->
    <div class="form-group">
        <label>Mô tả ngắn</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($tour['description'] ?? '') ?></textarea>
    </div>

    <div class="row">
        <!-- TRẠNG THÁI (ĐÃ SỬA: Chỉ xem, không sửa) -->
        <div class="col-md-6">
            <div class="form-group">
                <label>Trạng thái hiện tại</label>
                <?php 
                    // Xác định nhãn hiển thị
                    $currentState = $tour['state'] ?? 'DRAFT';
                    $stateLabel = 'Bản nháp (Draft)';
                    $inputClass = 'text-secondary'; // Màu chữ xám

                    if ($currentState === 'PUBLISHED') {
                        $stateLabel = 'Đã công bố (Published)';
                        $inputClass = 'text-success font-weight-bold'; // Màu chữ xanh đậm
                    }
                ?>
                
                <!-- Input hiển thị (Readonly) -->
                <input type="text" class="form-control <?= $inputClass ?>" value="<?= $stateLabel ?>" readonly style="background-color: #f4f6f9;">
                
                <!-- Input ẩn (Hidden) để gửi giá trị state về Controller khi bấm Lưu -->
                <input type="hidden" name="state" value="<?= $currentState ?>">
                
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Để thay đổi trạng thái, hãy sử dụng nút <b>"Công bố"</b> ở góc trên màn hình.
                </small>
            </div>
        </div>

        <!-- Nút Lưu -->
        <div class="col-md-6 text-right d-flex align-items-end justify-content-end pb-3">
            <button type="submit" class="btn btn-primary font-weight-bold px-4">
                <i class="fas fa-save"></i> Lưu thông tin chung
            </button>
        </div>
    </div>
</form>