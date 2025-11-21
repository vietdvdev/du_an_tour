<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Chính sách Hoàn/Hủy</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= route('tour.update.policy', ['id' => $tourId]) ?>">
                    <div class="form-group">
                        <label>Quy định Hủy Tour (Cancel Rules)</label>
                        <textarea name="cancel_rules" class="form-control" rows="6" placeholder="- Hủy trước 30 ngày: Phạt 10%..."><?= htmlspecialchars($policy['cancel_rules'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Quy định Hoàn tiền (Refund Rules)</label>
                        <textarea name="refund_rules" class="form-control" rows="6" placeholder="- Thời gian hoàn tiền: 7 ngày làm việc..."><?= htmlspecialchars($policy['refund_rules'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu Chính Sách</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Nhà cung cấp dịch vụ</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= route('tour.update.suppliers', ['id' => $tourId]) ?>">
                    <div class="alert alert-light">
                        Chọn các nhà cung cấp sẽ phục vụ cho Tour này (Khách sạn, Xe, Nhà hàng...).
                    </div>
                    
                    <div class="form-group">
                        <label>Tìm kiếm NCC:</label>
                        <input type="text" class="form-control" placeholder="Nhập tên NCC...">
                    </div>

                    <div class="list-group mb-3" style="max-height: 300px; overflow-y:auto;">
                        <p class="text-muted text-center">Chức năng đang phát triển...</p>
                    </div>

                    <button type="submit" class="btn btn-primary disabled">Lưu Nhà Cung Cấp</button>
                </form>
            </div>
        </div>
    </div>
</div>