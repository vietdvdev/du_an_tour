<div class="row">
    
    <div class="col-md-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning">
                <h3 class="card-title">
                    <i class="fas fa-gavel mr-1"></i> Chính sách Tour
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= route('tour.update.policy', ['id' => $tourId]) ?>">
                    
                    <div class="form-group">
                        <label class="text-dark">Quy định Hủy Tour (Cancel Rules)</label>
                        <textarea name="cancel_rules" class="form-control" rows="6" 
                                  placeholder="- Hủy trước 30 ngày: Phạt 10% giá trị tour..."><?= htmlspecialchars($policy['cancel_rules'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="text-dark">Quy định Hoàn tiền (Refund Rules)</label>
                        <textarea name="refund_rules" class="form-control" rows="6" 
                                  placeholder="- Thời gian hoàn tiền: Trong vòng 3-5 ngày làm việc..."><?= htmlspecialchars($policy['refund_rules'] ?? '') ?></textarea>
                    </div>

                    <div class="text-right">
                         <button type="submit" class="btn btn-primary font-weight-bold">
                             <i class="fas fa-save"></i> Lưu Chính Sách
                         </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        
<?php 
    // Đảm bảo biến tồn tại để không báo lỗi Undefined variable
    $allSuppliers = $allSuppliers ?? []; 
?>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-success text-white">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-plus-circle mr-1"></i> Thêm Nhà Cung Cấp
        </h3>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= route('tour.supplier.add', ['id' => $tourId]) ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label for="tour_category" class="form-label font-weight-bold">Chọn Nhà cung cấp <span class="text-danger">*</span></label>
                        <select  name="supplier_id" class="form-select" required>
                            <option value="">-- Chọn NCC --</option>
                            
                            <?php if (!empty($allSuppliers)): ?>
                                <?php foreach ($allSuppliers as $sup): ?>
                                    <option value="<?= $sup['id'] ?>">
                                        <?= htmlspecialchars($sup['name']) ?> 
                                        (<?= htmlspecialchars($sup['type'] ?? 'Khác') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Chưa có dữ liệu NCC</option>
                            <?php endif; ?>
                            
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label>Vai trò trong Tour</label>
                        <div class="input-group">
                            <input type="text" name="role" class="form-control" 
                                   placeholder="VD: Xe 29 chỗ, KS Đêm 1..." required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success font-weight-bold">
                                    <i class="fas fa-plus"></i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle"></i> Chọn NCC từ danh sách và nhập dịch vụ họ cung cấp cho tour này.
            </small>
        </form>
    </div>
</div>

        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i> Danh sách NCC đã gán
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Tên Nhà Cung Cấp</th>
                                <th>Loại</th>
                                <th>Vai trò</th>
                                <th width="100" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suppliers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="far fa-folder-open fa-2x mb-2"></i><br>
                                        Chưa có nhà cung cấp nào được gán cho Tour này.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($suppliers as $item): ?>
                                <tr>
                                    <td class="align-middle font-weight-bold">
                                        <?= htmlspecialchars($item['supplier_name']) ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info px-2 py-1">
                                            <?= htmlspecialchars($item['supplier_type']) ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <?= htmlspecialchars($item['role']) ?>
                                    </td>
                                    
                                    <td class="text-center align-middle">
                                        <form method="POST" action="<?= route('tour.supplier.delete', ['id' => $tourId]) ?>" style="display:inline;">
                                            <input type="hidden" name="supplier_id_to_delete" value="<?= $item['supplier_id'] ?>">
                                            
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn chắc chắn muốn gỡ bỏ NCC này khỏi Tour?')"
                                                    title="Xóa NCC này">
                                                <i class="fas fa-trash-alt"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

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